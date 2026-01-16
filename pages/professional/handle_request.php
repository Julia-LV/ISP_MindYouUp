<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$link_id = $_POST['link_id'];
$action = $_POST['action'];

if ($action === 'accept') {
    // Only update Connection_Status. Keep medical 'Status' as whatever it was (Pending/New).
    $stmt = $conn->prepare("UPDATE patient_professional_link SET Connection_Status = 'Accepted' WHERE Link_ID = ?");
    $stmt->bind_param("i", $link_id);
    $stmt->execute();
    $msg = "accepted";
} elseif ($action === 'decline') {
    // Delete the row entirely
    $stmt = $conn->prepare("DELETE FROM patient_professional_link WHERE Link_ID = ?");
    $stmt->bind_param("i", $link_id);
    $stmt->execute();
    $msg = "declined";
}

header("Location: my_patients.php?status=success&msg=" . $msg);
exit;
?>