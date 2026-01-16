<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$patient_id = $_SESSION['user_id'];
$doctor_id = $_POST['doctor_id'];

// 1. Check if link exists 
$check = $conn->prepare("SELECT Link_ID FROM patient_professional_link WHERE Patient_ID = ? AND Professional_ID = ?");
$check->bind_param("ii", $patient_id, $doctor_id);
$check->execute();

if ($check->get_result()->num_rows == 0) {
    // 2. Insert with Connection_Status = 'Pending'
    $sql = "INSERT INTO patient_professional_link 
            (Patient_ID, Professional_ID, Assigned_Date, Status, Connection_Status, Treatment_Type) 
            VALUES (?, ?, NOW(), 'Pending', 'Pending', 'Medical')";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $patient_id, $doctor_id);
    
    if($stmt->execute()) {
        header("Location: search_professionals.php?status=success&msg=requested");
    } else {
        header("Location: search_professionals.php?error=failed");
    }
} else {
    header("Location: search_professionals.php?error=exists");
}
exit;
?>