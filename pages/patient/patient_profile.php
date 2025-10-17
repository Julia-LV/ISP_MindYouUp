<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['User_ID']) || $_SESSION['Role'] != 'Patient') { 
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['User_ID'];

// CORREÇÃO 1: Adicionar u.User_Image à consulta SQL
$sql = "SELECT u.First_Name, u.Last_Name, u.`E-mail`, u.User_Image, p.* FROM user_profile u
        JOIN patient_profile p ON u.User_ID = p.User_ID
        WHERE u.User_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// Definir o caminho da imagem aqui
$default_pic = "../../img/MYU_logos/MYU_Monogram.png";
// CORREÇÃO 2: Usar o User_Image da DB ou o default.
$profile_pic_path = !empty($profile['User_Image']) ? $profile['User_Image'] : $default_pic;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Profile - MindYouUp</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <div class="container">
        <h2>Patient Profile</h2>

        <div class="profile-card">
            
            <img src="<?= htmlspecialchars($profile_pic_path) ?>" width="150" height="150" class="profile-pic">

            <p><strong>Name:</strong> <?= htmlspecialchars($profile['First_Name'] . " " . $profile['Last_Name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($profile['E-mail']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($profile['Patient_Status']) ?></p>
            <p><strong>Age:</strong> <?= htmlspecialchars($profile['Age']) ?></p>
            <p><strong>Start Date:</strong> <?= htmlspecialchars($profile['Start_Date']) ?></p>
            <p><strong>Treatment:</strong> <?= htmlspecialchars($profile['Treatment_Type']) ?></p>

            <a href="edit_profile.php" class="button">Edit Profile</a>
        </div>
    </div>
</body>
</html>