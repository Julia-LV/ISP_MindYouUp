<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professional') { header("Location: ../auth/login.php"); exit; }

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $dob   = $_POST['dob'];
    $spec  = $_POST['specialization']; 

    // Update General
    
    $stmt = $conn->prepare("UPDATE user_profile SET First_Name=?, Last_Name=?, Email=?, Birthday=? WHERE User_ID=?");
    $stmt->bind_param("ssssi", $fname, $lname, $email, $dob, $user_id);
    $stmt->execute();

    // Update Specialization
    $sql_prof = "INSERT INTO professional_profile (User_ID, Specialization) VALUES (?, ?) ON DUPLICATE KEY UPDATE Specialization = VALUES(Specialization)";
    $stmt2 = $conn->prepare($sql_prof);
    $stmt2->bind_param("is", $user_id, $spec);
    $stmt2->execute();

    // IMAGE HANDLING
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
    
    header("Location: professional_profile.php");
    exit;
}

// Fetch Data

$sql = "SELECT u.First_Name, u.Last_Name, u.Email, u.Birthday, u.User_Image, p.Specialization 
        FROM user_profile u LEFT JOIN professional_profile p ON u.User_ID = p.User_ID WHERE u.User_ID = ?";
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
                            <input type="date" name="dob" value="<?= htmlspecialchars($profile['Birthday'] ?? '') ?>" class="w-full rounded-lg border-gray-300 border p-2.5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Specialization</label>
                        <input type="text" name="specialization" value="<?= htmlspecialchars($profile['Specialization'] ?? '') ?>" placeholder="e.g. Clinical Psychologist" class="w-full rounded-lg border-gray-300 border p-2.5">
                    </div>

                    <div class="pt-4 flex justify-between items-center border-t border-gray-100 mt-6">
                         <a href="../auth/reset_password.php" class="text-[#F26647] hover:underline text-sm font-medium">Change Password</a>
                        <div class="flex gap-3">
                            <a href="professional_profile.php" class="px-5 py-2.5 rounded-lg text-gray-700 hover:bg-gray-100">Cancel</a>
                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#F0856C] text-white font-medium hover:bg-[#F26647]">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>