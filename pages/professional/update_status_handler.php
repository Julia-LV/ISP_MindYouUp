<?php
session_start();
include('../../config.php');

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $link_id = $_POST['link_id'];
    $new_status = $_POST['status'];

    // Update the status in the Link Table
    $stmt = $conn->prepare("UPDATE patient_professional_link SET Status = ? WHERE Link_ID = ?");
    $stmt->bind_param("si", $new_status, $link_id);
    
    if ($stmt->execute()) {
        
        header("Location: my_patients.php");
    } else {
        echo "Error updating status: " . $conn->error;
    }
}
?>