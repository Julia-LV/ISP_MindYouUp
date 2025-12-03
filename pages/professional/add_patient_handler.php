<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$doctor_id = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'];

// Double check if already connected
$check = $conn->prepare("SELECT Link_ID FROM patient_professional_link WHERE Patient_ID = ? AND Professional_ID = ?");
$check->bind_param("ii", $patient_id, $doctor_id);
$check->execute();

if ($check->get_result()->num_rows == 0) {
    // INSERT THE LINK 
    // Status defaults to 'Currently Followed' since the DOCTOR is adding them personally
    $sql = "INSERT INTO patient_professional_link (Patient_ID, Professional_ID, Assigned_Date, Status, Treatment_Type) 
            VALUES (?, ?, NOW(), 'Currently Followed', 'Medical')";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $patient_id, $doctor_id);
    $stmt->execute();
}

// Go back to the list
header("Location: my_patients.php");
exit;
?>