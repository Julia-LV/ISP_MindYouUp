<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['User_ID']) || $_SESSION['Role'] != 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['User_ID'];

// Fetch current user data
$sql = "SELECT u.First_Name, u.Last_Name, u.`E-mail`, u.User_Image, p.* 
        FROM user_profile u
        JOIN patient_profile p ON u.User_ID = p.User_ID
        WHERE u.User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST['status'];
    $age = $_POST['age'];
    $treatment = $_POST['treatment'];

    // === Update Profile Picture if uploaded ===
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_dir = "../../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target = $target_dir . $file_name;
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target);

        // Update user image
        $sql_user = "UPDATE user_profile SET User_Image=? WHERE User_ID=?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("si", $target, $user_id);
        $stmt_user->execute();
    }

    // === Always update patient info ===
    $sql_patient = "UPDATE patient_profile 
                    SET Patient_Status=?, Age=?, Treatment_Type=?
                    WHERE User_ID=?";
    $stmt_patient = $conn->prepare($sql_patient);
    $stmt_patient->bind_param("sisi", $status, $age, $treatment, $user_id);
    $stmt_patient->execute();

    header("Location: patient_profile.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - MindYouUp</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Status:</label>
            <select name="status" required>
                <option value="Followed" <?= $profile['Patient_Status'] == 'Followed' ? 'selected' : '' ?>>Followed</option>
                <option value="Drop_Out" <?= $profile['Patient_Status'] == 'Drop_Out' ? 'selected' : '' ?>>Drop Out</option>
                <option value="Discharged" <?= $profile['Patient_Status'] == 'Discharged' ? 'selected' : '' ?>>Discharged</option>
            </select><br>

            <label>Age:</label>
            <input type="number" name="age" value="<?= htmlspecialchars($profile['Age']) ?>" required><br>

            <label>Treatment Type:</label>
            <input type="text" name="treatment" value="<?= htmlspecialchars($profile['Treatment_Type']) ?>" required><br>

            <label>Profile Picture:</label>
            <input type="file" name="profile_pic" accept="image/*"><br>

            <button type="submit">Save Changes</button>
            <a href="patient_profile.php" class="button">Cancel</a>
        </form>
    </div>
</body>
</html>
