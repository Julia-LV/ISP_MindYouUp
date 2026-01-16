<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Auth Guard
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || strtolower($_SESSION['role'] ?? '') !== 'professional') {
    header('Location: ../auth/login.php');
    exit;
}

$currentProfessionalId = (int)($_SESSION['user_id'] ?? 0);
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// Map for Category Labels
$skills = [
    'competing_behaviours' => 'Competing Behaviours',
    'habit_reversal'       => 'Habit Reversal Training',
    'anxiety_management'   => 'Anxiety Management',
    'pmr_training'         => 'PMR Training',
];

// Handle Delete Assignment
if (isset($_GET['unassign_patient']) && isset($_GET['resource_id'])) {
    $pid = (int)$_GET['unassign_patient'];
    $rid = (int)$_GET['resource_id'];
    $stmt = $conn->prepare("DELETE FROM patient_resource_assignments WHERE patient_id = ? AND resource_id = ?");
    $stmt->bind_param('ii', $pid, $rid);
    if($stmt->execute()) $_SESSION['flash_success'] = "Assignment removed successfully.";
    header("Location: resourcehub_existing.php");
    exit;
}

// 1. Calculate Banner Numbers
$bannerMap = [];
$bSql = "SELECT id FROM resource_hub WHERE professional_id = ? AND item_type = 'banner' ORDER BY id ASC";
$bStmt = $conn->prepare($bSql);
$bStmt->bind_param('i', $currentProfessionalId);
$bStmt->execute();
$bRes = $bStmt->get_result();
$count = 1;
while($bRow = $bRes->fetch_assoc()) {
    $bannerMap[$bRow['id']] = $count++;
}

// 2. Fetch Assignments
$items = [];
$sql = "SELECT rh.*, up.First_Name, up.Last_Name, up.User_ID as Patient_ID, pra.assigned_at 
        FROM resource_hub rh 
        JOIN patient_resource_assignments pra ON rh.id = pra.resource_id 
        JOIN user_profile up ON up.User_ID = pra.patient_id 
        WHERE rh.professional_id = ?
        ORDER BY pra.assigned_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $currentProfessionalId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $items[] = $row;

require_once __DIR__ . '/../../components/header_component.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="min-h-screen bg-[#E9F0E9] pt-8 pb-12 px-4">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-[#005949]">History of Uploaded Items</h1>
            <a href="resourcehub_professional.php" class="bg-white text-[#005949] px-6 py-2 rounded-full font-bold shadow-sm border border-[#c7e4d7] hover:bg-gray-50 transition-all">
                ← Back to Admin
            </a>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg shadow-sm"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-[24px] shadow-sm overflow-hidden border border-[#c7e4d7]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f8faf8] border-b border-[#c7e4d7]">
                        <th class="p-4 text-xs font-bold text-[#005949] uppercase">Resource</th>
                        <th class="p-4 text-xs font-bold text-[#005949] uppercase">Category Type</th>
                        <th class="p-4 text-xs font-bold text-[#005949] uppercase">Assigned To</th>
                        <th class="p-4 text-xs font-bold text-[#005949] uppercase">Date Sent</th>
                        <th class="p-4 text-xs font-bold text-[#005949] uppercase text-right">Remove</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f4f2]">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="5" class="p-12 text-center text-gray-500 italic">No resources shared yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-[#fcfdfc] transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-[#005949]">
                                        <?php 
                                            if($item['item_type'] === 'banner') {
                                                echo "<span class='text-orange-600 font-black'>Banner " . ($bannerMap[$item['id']] ?? '') . ":</span> ";
                                            }
                                            echo htmlspecialchars($item['title']); 
                                        ?>
                                    </div>
                                    <div class="text-[10px] text-gray-400 uppercase tracking-widest"><?php echo str_replace('_', ' ', $item['item_type']); ?></div>
                                </td>
                                
                                <td class="p-4">
                                    <span class="text-xs font-medium text-gray-600">
                                        <?php echo $skills[$item['category_type']] ?? '<span class="text-gray-300">N/A</span>'; ?>
                                    </span>
                                </td>

                                <td class="p-4">
                                    <span class="bg-[#E9F0E9] text-[#005949] px-3 py-1 rounded-full text-xs font-bold">
                                        <?php echo htmlspecialchars($item['First_Name'] . ' ' . $item['Last_Name']); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($item['assigned_at'])); ?>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="?unassign_patient=<?php echo $item['Patient_ID']; ?>&resource_id=<?php echo $item['id']; ?>" 
                                       onclick="return confirm('Remove this resource from this patient?')"
                                       class="text-red-400 hover:text-red-600 text-[10px] font-bold uppercase tracking-widest transition-colors">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>