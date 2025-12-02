<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $link_id = $_POST['link_id'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Permission Check
    if ($role == 'Patient') {
        $check = "SELECT Link_ID FROM patient_professional_link WHERE Link_ID = ? AND Patient_ID = ?";
    } else {
        $check = "SELECT Link_ID FROM patient_professional_link WHERE Link_ID = ? AND Professional_ID = ?";
    }

    $stmt_check = $conn->prepare($check);
    $stmt_check->bind_param("ii", $link_id, $user_id);
    $stmt_check->execute();

    if ($stmt_check->get_result()->num_rows > 0) {
        // Delete
        $del = "DELETE FROM patient_professional_link WHERE Link_ID = ?";
        $stmt_del = $conn->prepare($del);
        $stmt_del->bind_param("i", $link_id);
        $stmt_del->execute();
    }
}

// REDIRECT LOGIC UPDATED FOR COMMON FOLDER
if ($_SESSION['role'] == 'Patient') {
    // Go up from 'common' (..), then into 'patient'
    header("Location: ../patient/my_professionals.php");
} else {
    // Go up from 'common' (..), then into 'professional'
    header("Location: ../professional/my_patients.php");
}
exit;
?>