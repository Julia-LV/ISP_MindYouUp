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
    $subtitle = trim($_POST['subtitle'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $banner_content_type = $_POST['banner_content_type'] ?? 'article';
    $external_link = trim($_POST['external_link'] ?? '');
    $selectedPatients = $_POST['share_patients'] ?? [];

    if (empty($selectedPatients)) $errors[] = "Please select at least one patient.";
    if (empty($title)) $errors[] = "Title is required.";

    // Banner Limit Check
    if ($item_type === 'banner') {
        $countSql = "SELECT COUNT(*) FROM resource_hub WHERE item_type = 'banner'";
        $resCount = $conn->query($countSql);
        $totalBanners = $resCount->fetch_row()[0];
        if ($totalBanners >= 6) {
            $errors[] = "Banner limit reached (Max 6). Please delete an existing banner before adding a new one.";
        }
    }

    $media_url = handle_upload('media_file', $uploadBase);
    if (!$media_url && !empty($external_link)) $media_url = $external_link;
    if (empty($media_url)) $errors[] = "Please provide a file or a link.";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO resource_hub (item_type, category_type, banner_content_type, title, subtitle, media_url, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssss', $item_type, $category_type, $banner_content_type, $title, $subtitle, $media_url, $image_url);
        
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
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
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-[#005949]">Resource Hub Admin</h1>
            <div class="flex gap-4">
                <a href="resourcehub_library.php" class="bg-[#005949] text-white px-6 py-2 rounded-full font-bold shadow-lg hover:bg-[#004236] transition-all">Library</a>
                <a href="resourcehub_existing.php" class="bg-[#005949] text-white px-6 py-2 rounded-full font-bold shadow-lg hover:bg-[#004236] transition-all">Uploaded Items</a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
                <?php foreach($errors as $err) echo "<p>• $err</p>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-[24px] shadow-sm p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-[#005949] mb-2">Select Item Type</label>
                    <select name="item_type" id="item_type" class="w-full p-3 rounded-xl border border-[#c7e4d7]">
                        <option value="article">Article/Guide</option>
                        <option value="category">Categories</option>
                        <option value="banner">Banner (Max 6)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#005949] mb-2">Select Category Type</label>
                    <select name="category_type" id="category_type" class="w-full p-3 rounded-xl border border-[#c7e4d7] disabled:bg-gray-100 disabled:cursor-not-allowed transition-all">
                        <option value="">-- No Category --</option>
                        <?php foreach ($skills as $k => $v): ?>
                            <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-[#005949] mb-2">Resource Title</label>
                    <input type="text" name="title" required class="w-full p-3 rounded-xl border border-[#c7e4d7]" placeholder="e.g., Breathing Exercises">
                </div>
                <div id="subtitle_box">
                    <label class="block text-sm font-bold text-[#005949] mb-2">Subtitle (for banners)</label>
                    <input type="text" name="subtitle" class="w-full p-3 rounded-xl border border-[#c7e4d7]" placeholder="Short description or context...">
                </div>
            </div>

            <div id="banner_fields" class="hidden space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-[#005949] mb-2">Banner Background Image URL</label>
                        <input type="url" name="image_url" class="w-full p-3 rounded-xl border border-[#c7e4d7]" placeholder="https://images.unsplash.com/...">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#005949] mb-2">Content Type</label>
                        <select name="banner_content_type" class="w-full p-3 rounded-xl border border-[#c7e4d7]">
                            <option value="article">Article/Link</option>
                            <option value="video">Video (YouTube)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-[#005949] mb-2">Upload File (PDF/Image)</label>
                    <input type="file" name="media_file" class="w-full p-2 text-sm border border-[#c7e4d7] rounded-xl">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#005949] mb-2">Paste Link (YouTube/Web)</label>
                    <input type="url" name="external_link" class="w-full p-3 rounded-xl border border-[#c7e4d7]" placeholder="https://youtube.com/...">
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-bold text-[#005949]">Assign to Patients</label>
                    <button type="button" id="toggle_all_patients" class="text-sm font-bold text-[#005949] hover:underline bg-[#c7e4d7]/30 px-3 py-1 rounded-lg">
                        Select All
                    </button>
                </div>
                <div class="border border-[#c7e4d7] rounded-xl p-4 max-h-40 overflow-y-auto">
                    <?php if (empty($linkedPatients)): ?>
                        <p class="text-gray-500 text-sm italic">No patients linked to your account.</p>
                    <?php else: ?>
                        <?php foreach ($linkedPatients as $p): ?>
                            <label class="flex items-center gap-3 p-1 cursor-pointer hover:bg-gray-50 rounded-md transition-colors">
                                <input type="checkbox" name="share_patients[]" value="<?php echo $p['User_ID']; ?>" class="patient-checkbox">
                                <span><?php echo htmlspecialchars($p['First_Name'] . ' ' . $p['Last_Name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="w-full bg-[#005949] text-white py-4 rounded-full font-bold shadow-lg hover:bg-[#004236] transition-all">Upload & Share Resource</button>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemType = document.getElementById('item_type');
    const catType = document.getElementById('category_type');
    const bannerFields = document.getElementById('banner_fields');
    const toggleBtn = document.getElementById('toggle_all_patients');
    const checkboxes = document.querySelectorAll('.patient-checkbox');

    function updateForm() {
        const val = itemType.value;
        
        // 1. Category logic: Enable only if "Inside a Category Box"
        if (val === 'category') {
            catType.disabled = false;
        } else {
            catType.disabled = true;
            catType.value = "";
        }

        // 2. Banner logic: Show extra banner fields (Subtitle is now always visible)
        if (val === 'banner') {
            bannerFields.classList.remove('hidden');
        } else {
            bannerFields.classList.add('hidden');
        }
    }

    // Select All / Deselect All Logic
    if(toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = anyUnchecked;
            });

            this.textContent = anyUnchecked ? 'Deselect All' : 'Select All';
        });
    }

    itemType.addEventListener('change', updateForm);
    updateForm(); // Run on load
});
</script>