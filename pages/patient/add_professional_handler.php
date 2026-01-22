<?php
session_start();
include('../../config.php');
require_once __DIR__ . '/../common/notifications.php';

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
        // Fetch patient name for notification
        $patientName = '';
        $pstmt = $conn->prepare("SELECT First_Name, Last_Name FROM user_profile WHERE User_ID = ? LIMIT 1");
        if ($pstmt) {
            $pstmt->bind_param("i", $patient_id);
            $pstmt->execute();
            $pstmt->bind_result($fname, $lname);
            if ($pstmt->fetch()) {
                $patientName = trim($fname . ' ' . $lname);
            }
            $pstmt->close();
        }

        // Send notification to professional
        $title = 'Connection Request';
        $message = $patientName ? ($patientName . ' wants to connect with you.') : 'You have a new connection request.';
        saveNotificationToDatabase($conn, $doctor_id, $title, $message, 'connection');

        header("Location: search_professionals.php?status=success&msg=requested");
    } else {
        header("Location: search_professionals.php?error=failed");
    }
} else {
    header("Location: search_professionals.php?error=exists");
}
exit;
?>