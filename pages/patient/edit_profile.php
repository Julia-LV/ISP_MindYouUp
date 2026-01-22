<?php
session_start();
include('../../config.php');

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../auth/login.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'Patient';

// Fetch existing data first to use as a fallback if needed
$sql_init = "SELECT Birthday FROM user_profile WHERE User_ID = ?";
$stmt_init = $conn->prepare($sql_init);
$stmt_init->bind_param("i", $user_id);
$stmt_init->execute();
$existing_data = $stmt_init->get_result()->fetch_assoc();
$old_birthday = $existing_data['Birthday'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $dob   = $_POST['dob'];

    // --- SAFETY CHECK FOR DATE ---
    // If the date is empty or incomplete (less than 10 chars like '2026'), 
    // we revert to the old birthday stored in the database instead of overwriting it.
    if (empty($dob) || strlen($dob) < 10) {
        $dob = $old_birthday;
    }

    // 1. General Update
    $stmt = $conn->prepare("UPDATE user_profile SET First_Name=?, Last_Name=?, Email=?, Birthday=? WHERE User_ID=?");
    $stmt->bind_param("ssssi", $fname, $lname, $email, $dob, $user_id);
    $stmt->execute();

    // 2. Treatment Type (Only for Patients)
    if ($role == 'Patient' && !empty($_POST['treatment'])) {
        $treatment = $_POST['treatment'];
        $sql_p = "INSERT INTO patient_profile (User_ID, Treatment_Type) VALUES (?, ?) 
                  ON DUPLICATE KEY UPDATE Treatment_Type = VALUES(Treatment_Type)";
        $stmt_p = $conn->prepare($sql_p);
        $stmt_p->bind_param("is", $user_id, $treatment);
        $stmt_p->execute();
    }

    // 3. IMAGE HANDLING
    if (isset($_POST['remove_photo'])) {
        $stmt = $conn->prepare("UPDATE user_profile SET User_Image = NULL WHERE User_ID=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target = "../../uploads/" . $file_name;
        if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target)){
            $stmt = $conn->prepare("UPDATE user_profile SET User_Image=? WHERE User_ID=?");
            $stmt->bind_param("si", $target, $user_id);
            $stmt->execute();
        }
    }
    
    header("Location: patient_profile.php");
    exit;
}

// 4. FETCH DATA FOR FORM DISPLAY
$sql = "SELECT u.First_Name, u.Last_Name, u.Email, u.Birthday, u.User_Image, p.Treatment_Type 
        FROM user_profile u LEFT JOIN patient_profile p ON u.User_ID = p.User_ID WHERE u.User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

include('../../components/header_component.php'); 
?>

<div class="flex h-screen bg-[#E9F0E9] font-sans">
    <?php include('../../includes/navbar.php'); ?>
    <main class="flex-1 overflow-y-auto p-8">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-8">Edit Profile</h1>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                        <input type="file" name="profile_pic" accept="image/*" class="block w-full text-sm text-gray-500 mb-2"/>
                        
                        <?php if(!empty($profile['User_Image'])): ?>
                            <div class="flex items-center gap-2 mt-2">
                                <input type="checkbox" name="remove_photo" id="rm_photo" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <label for="rm_photo" class="text-sm text-red-500 font-medium cursor-pointer">Remove current photo</label>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($profile['First_Name'] ?? '') ?>" required class="w-full rounded-lg border-gray-300 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($profile['Last_Name'] ?? '') ?>" required class="w-full rounded-lg border-gray-300 border p-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($profile['Email'] ?? '') ?>" required class="w-full rounded-lg border-gray-300 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                            <input type="date" name="dob" 
                                   value="<?= !empty($profile['Birthday']) ? date('Y-m-d', strtotime($profile['Birthday'])) : '' ?>" 
                                   class="w-full rounded-lg border-gray-300 border p-2.5">
                        </div>
                    </div>

                    <?php if($role == 'Patient'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Treatment Type</label>
                        <select name="treatment" class="w-full rounded-lg border-gray-300 border p-2.5 bg-white">
                            <option value="" disabled <?= empty($profile['Treatment_Type']) ? 'selected' : '' ?>>Select Type</option>
                            <option value="Psychological" <?= ($profile['Treatment_Type'] ?? '') == 'Psychological' ? 'selected' : '' ?>>Psychological</option>
                            <option value="Medical" <?= ($profile['Treatment_Type'] ?? '') == 'Medical' ? 'selected' : '' ?>>Medical</option>
                            <option value="Both" <?= ($profile['Treatment_Type'] ?? '') == 'Both' ? 'selected' : '' ?>>Both</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="pt-4 flex justify-between items-center border-t border-gray-100 mt-6">
                         <a href="../auth/reset_password.php" class="text-[#F26647] hover:underline text-sm font-medium">Change Password</a>
                        <div class="flex gap-3">
                            <a href="patient_profile.php" class="px-5 py-2.5 rounded-lg text-gray-700 hover:bg-gray-100">Cancel</a>
                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#F26647] text-white font-medium">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>