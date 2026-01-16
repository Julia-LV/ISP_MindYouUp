<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Auth Guard
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || strtolower($_SESSION['role'] ?? '') !== 'professional') {
    header('Location: ../auth/login.php');
    exit;
}

$currentProfessionalId = (int)($_SESSION['user_id'] ?? 0);
$errors = [];
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$skills = [
    'competing_behaviours' => 'Competing Behaviours',
    'habit_reversal'       => 'Habit Reversal Training',
    'anxiety_management'   => 'Anxiety Management',
    'pmr_training'         => 'Progressive Muscle Relaxation Training',
];

// --- HANDLE DELETE RESOURCE ---
if (isset($_POST['action']) && $_POST['action'] === 'delete_resource') {
    $resourceId = (int)$_POST['resource_id'];
    
    
    $find = $conn->prepare("SELECT media_url FROM resource_hub WHERE id = ? AND professional_id = ?");
    $find->bind_param('ii', $resourceId, $currentProfessionalId);
    $find->execute();
    $fResult = $find->get_result()->fetch_assoc();
    
    if ($fResult) {
        $filePath = __DIR__ . '/../../' . str_replace('../../', '', $fResult['media_url']);
        if (file_exists($filePath) && is_file($filePath)) unlink($filePath);

        // Delete assignments and the resource
        $conn->query("DELETE FROM patient_resource_assignments WHERE resource_id = $resourceId");
        $conn->query("DELETE FROM resource_hub WHERE id = $resourceId AND professional_id = $currentProfessionalId");
        
        $_SESSION['flash_success'] = "Resource permanently deleted.";
        header("Location: resourcehub_library.php");
        exit;
    }
}

// --- HANDLE SYNC SHARING  ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'share_resource') {
    $resourceId = (int)$_POST['resource_id'];
    $selectedPatients = $_POST['patient_ids'] ?? [];

    // 1. Remove access for anyone not in the selected list
    $placeholders = count($selectedPatients) > 0 ? implode(',', array_fill(0, count($selectedPatients), '?')) : '0';
    $sqlRemove = "DELETE FROM patient_resource_assignments WHERE resource_id = ? AND patient_id NOT IN ($placeholders)";
    $stmtRem = $conn->prepare($sqlRemove);
    
    $types = 'i' . str_repeat('i', count($selectedPatients));
    $params = array_merge([$resourceId], $selectedPatients);
    $stmtRem->bind_param($types, ...$params);
    $stmtRem->execute();

    // 2. Add access for the selected ones 
    if (!empty($selectedPatients)) {
        $ins = $conn->prepare("INSERT IGNORE INTO patient_resource_assignments (patient_id, resource_id, assigned_at) VALUES (?, ?, NOW())");
        foreach ($selectedPatients as $pid) {
            $ins->bind_param('ii', $pid, $resourceId);
            $ins->execute();
        }
    }

    $_SESSION['flash_success'] = "Patient access updated successfully!";
    header("Location: resourcehub_library.php");
    exit;
}

// Fetch Linked Patients
$linkedPatients = [];
$sql = "SELECT up.User_ID, up.First_Name, up.Last_Name FROM patient_professional_link ppl 
        JOIN user_profile up ON up.User_ID = ppl.Patient_ID 
        WHERE ppl.Professional_ID = ? ORDER BY up.Last_Name";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $currentProfessionalId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $linkedPatients[] = $row;


$resources = [];
$sql = "SELECT rh.*, GROUP_CONCAT(pra.patient_id) as assigned_patient_ids 
        FROM resource_hub rh 
        LEFT JOIN patient_resource_assignments pra ON rh.id = pra.resource_id 
        WHERE rh.professional_id = ?
        GROUP BY rh.id ORDER BY rh.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $currentProfessionalId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $row['assigned_list'] = $row['assigned_patient_ids'] ? explode(',', $row['assigned_patient_ids']) : [];
    $resources[] = $row;
}

require_once __DIR__ . '/../../components/header_component.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="min-h-screen bg-[#E9F0E9] pt-8 pb-12 px-4">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-[#005949]">My Resource Library</h1>
            <a href="resourcehub_professional.php" class="bg-white text-[#005949] px-6 py-2 rounded-full font-bold shadow-sm border border-[#c7e4d7] hover:bg-gray-100 transition-all">
                ← Back to Admin
            </a>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg shadow-sm"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($resources)): ?>
                <div class="col-span-full bg-white/50 rounded-3xl p-12 text-center border-2 border-dashed border-[#c7e4d7]">
                    <p class="text-gray-500 italic">You haven't uploaded any resources yet.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($resources as $res): ?>
                <div class="bg-white rounded-[24px] shadow-sm p-6 flex flex-col border border-white hover:border-[#c7e4d7] transition-all relative group">
                    
                    <form method="POST" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity" onsubmit="return confirm('Permanently delete this resource and remove it from all patients?')">
                        <input type="hidden" name="action" value="delete_resource">
                        <input type="hidden" name="resource_id" value="<?php echo $res['id']; ?>">
                        <button type="submit" class="p-2 text-red-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Delete Resource">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>

                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="text-[10px] uppercase tracking-wider font-bold px-3 py-1 bg-[#E9F0E9] text-[#005949] rounded-full">
                            <?php 
                                echo str_replace('_', ' ', $res['item_type']);
                            ?>
                        </span>
                        <?php if($res['category_type']): ?>
                            <span class="text-[10px] font-medium text-gray-400 italic">
                                <?php echo $skills[$res['category_type']] ?? $res['category_type']; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-lg font-bold text-[#005949] mb-1 pr-8"><?php echo htmlspecialchars($res['title']); ?></h3>
                    <p class="text-sm text-gray-600 mb-6 line-clamp-2"><?php echo htmlspecialchars($res['subtitle'] ?: 'No description.'); ?></p>

                    <div class="mt-auto pt-4 border-t border-gray-100">
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="share_resource">
                            <input type="hidden" name="resource_id" value="<?php echo $res['id']; ?>">
                            
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold text-[#005949] uppercase">Manage Access</label>
                            </div>

                            <div class="max-h-32 overflow-y-auto space-y-2 mb-4 pr-2 custom-scrollbar">
                                <?php foreach ($linkedPatients as $p): 
                                    $isAssigned = in_array($p['User_ID'], $res['assigned_list']);
                                ?>
                                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 p-1 rounded transition-colors">
                                        <input type="checkbox" name="patient_ids[]" value="<?php echo $p['User_ID']; ?>" <?php echo $isAssigned ? 'checked' : ''; ?> class="rounded border-[#c7e4d7] text-[#005949] focus:ring-[#005949]">
                                        <span class="<?php echo $isAssigned ? 'font-semibold text-[#005949]' : 'text-gray-500'; ?>">
                                            <?php echo htmlspecialchars($p['First_Name'] . ' ' . $p['Last_Name']); ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <button type="submit" class="w-full bg-[#005949] text-white py-2 rounded-xl text-sm font-bold hover:bg-[#004236] shadow-sm transition-all">
                                Update Access
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #c7e4d7; border-radius: 10px; }
</style>