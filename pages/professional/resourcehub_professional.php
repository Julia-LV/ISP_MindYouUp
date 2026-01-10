<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Auth Guard
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || strtolower($_SESSION['role'] ?? '') !== 'professional') {
    header('Location: ../auth/login.php');
    exit;
}

$currentProfessionalId = (int)($_SESSION['user_id'] ?? 0);
$uploadBase = __DIR__ . '/../../uploads/';
if (!is_dir($uploadBase)) mkdir($uploadBase, 0777, true);

// Setup
$errors = [];
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$skills = [
    'competing_behaviours' => 'Competing Behaviours',
    'habit_reversal'       => 'Habit Reversal Training',
    'anxiety_management'   => 'Anxiety Management',
    'pmr_training'         => 'Progressive Muscle Relaxation Training',
];

function handle_upload($fieldName, $uploadBase) {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) return null;
    $ext = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
    $filename = preg_replace('/[^a-zA-Z0-9]/', '_', pathinfo($_FILES[$fieldName]['name'], PATHINFO_FILENAME)) . '_' . time() . '.' . $ext;
    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $uploadBase . $filename)) {
        return '../../uploads/' . $filename;
    }
    return null;
}

// Fetch linked patients
$linkedPatients = [];
$sql = "SELECT up.User_ID, up.First_Name, up.Last_Name FROM patient_professional_link ppl 
        JOIN user_profile up ON up.User_ID = ppl.Patient_ID 
        WHERE ppl.Professional_ID = ? ORDER BY up.Last_Name";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $currentProfessionalId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $linkedPatients[] = $row;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_type = $_POST['item_type'] ?? 'article';
    $category_type = $_POST['category_type'] ?? ''; 
    $title = trim($_POST['title'] ?? '');
    $external_link = trim($_POST['external_link'] ?? '');
    $selectedPatients = $_POST['share_patients'] ?? [];

    if (empty($selectedPatients)) $errors[] = "Please select at least one patient.";
    if (empty($title)) $errors[] = "Title is required.";

    // Logic: Use uploaded file first, if none, use the external link
    $media_url = handle_upload('media_file', $uploadBase);
    if (!$media_url && !empty($external_link)) {
        $media_url = $external_link;
    }

    if (empty($media_url)) $errors[] = "Please provide a file or a link.";

    if (empty($errors)) {
        // 1. SAVE TO LIBRARY (Fixing the NULL issue by adding category_type)
        $stmt = $conn->prepare("INSERT INTO resource_hub (item_type, category_type, title, media_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $item_type, $category_type, $title, $media_url);
        
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            // 2. SHARE WITH PATIENTS
            $ins = $conn->prepare("INSERT INTO patient_resource_assignments (patient_id, resource_id, assigned_at) VALUES (?, ?, NOW())");
            foreach ($selectedPatients as $pid) {
                $ins->bind_param('ii', $pid, $newId);
                $ins->execute();
            }
            $_SESSION['flash_success'] = "Resource shared successfully!";
            header("Location: resourcehub_professional.php");
            exit;
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}

require_once __DIR__ . '/../../components/header_component.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="min-h-screen bg-[#E9F0E9] pt-8 pb-12 px-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-[#005949] mb-8">Resource Hub Admin</h1>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-[24px] shadow-sm p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-[#005949] mb-2">Display Mode</label>
                    <select name="item_type" class="w-full p-3 rounded-xl border border-[#c7e4d7]">
                        <option value="category">Inside a Category Box</option>
                        <option value="article">List as Article/Guide</option>
                        <option value="banner">Promoted Banner</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#005949] mb-2">Select Category (Optional)</label>
                    <select name="category_type" class="w-full p-3 rounded-xl border border-[#c7e4d7]">
                        <option value="">-- No Category --</option>
                        <?php foreach ($skills as $k => $v): ?>
                            <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#005949] mb-2">Resource Title</label>
                <input type="text" name="title" required class="w-full p-3 rounded-xl border border-[#c7e4d7]" placeholder="e.g., Breathing Exercises">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-[#005949] mb-2">Upload File (PDF/Image)</label>
                    <input type="file" name="media_file" class="w-full p-2 text-sm border border-[#c7e4d7] rounded-xl">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#005949] mb-2">OR Paste Link (YouTube/Web)</label>
                    <input type="url" name="external_link" class="w-full p-3 rounded-xl border border-[#c7e4d7]" placeholder="https://youtube.com/...">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#005949] mb-2">Assign to Patients</label>
                <div class="border border-[#c7e4d7] rounded-xl p-4 max-h-40 overflow-y-auto">
                    <?php foreach ($linkedPatients as $p): ?>
                        <label class="flex items-center gap-3 p-1">
                            <input type="checkbox" name="share_patients[]" value="<?php echo $p['User_ID']; ?>">
                            <span><?php echo $p['First_Name'] . ' ' . $p['Last_Name']; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="w-full bg-[#005949] text-white py-3 rounded-full font-bold">Upload & Share</button>
        </form>
    </div>
</main>