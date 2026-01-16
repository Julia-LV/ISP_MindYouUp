<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$patient_id = $_POST['patient_id']; 

// Check if link already exists
$check = $conn->prepare("SELECT Link_ID FROM patient_professional_link WHERE Patient_ID = ? AND Professional_ID = ?");
$check->bind_param("ii", $patient_id, $doctor_id);
$check->execute();

if ($check->get_result()->num_rows == 0) {
    
    $sql = "INSERT INTO patient_professional_link 
            (Patient_ID, Professional_ID, Assigned_Date, Status, Connection_Status, Treatment_Type) 
            VALUES (?, ?, NOW(), 'Pending', 'Accepted', 'Medical')";
    
    
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $patient_id, $doctor_id);
    
    if($stmt->execute()) {
        header("Location: my_patients.php?msg=added");
    } else {
        header("Location: search_patients.php?error=failed");
    }
} else {
    header("Location: search_patients.php?error=exists");
}
exit;
?>