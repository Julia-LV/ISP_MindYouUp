<?php
session_start();
include('../../config.php');


if ($_SERVER["REQUEST_METHOD"] !== "POST" || 
    !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    $_SESSION['role'] !== 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

// Get IDs from the form and session
$professional_id = $_POST['professional_id'] ?? $_SESSION['user_id'];
$patient_id = $_POST['patient_id'] ?? '';

// Sanitize and Validate Input
$patient_id = filter_var($patient_id, FILTER_VALIDATE_INT);
$professional_id = filter_var($professional_id, FILTER_VALIDATE_INT);

$message = "";
$message_type = "";

if (!$patient_id || !$professional_id) {
    $message = "Error: Invalid Professional ID or Patient ID provided.";
    $message_type = "error";
} else {
    // 1. Check if Patient ID exists 
    $sql_check_patient = "SELECT User_ID FROM user_profile WHERE User_ID = ? AND Role = 'Patient'";
    $stmt_check = $conn->prepare($sql_check_patient);
    $stmt_check->bind_param("i", $patient_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        $message = "Error: Patient with ID {$patient_id} does not exist or is not registered as a Patient.";
        $message_type = "error";
    } else {
        // 2. Insert the link into the database
        $sql_insert = "INSERT INTO patient_professional_link (Patient_ID, Professional_ID)
                       VALUES (?, ?)";
        
        $stmt_insert = $conn->prepare($sql_insert);
        
        
        $stmt_insert->bind_param("ii", $patient_id, $professional_id);

        if ($stmt_insert->execute()) {
            $message = "Success: Patient ID {$patient_id} linked successfully to your profile!";
            $message_type = "success";
        } elseif ($conn->errno == 1062) { 
            $message = "Warning: This patient is already linked to your profile.";
            $message_type = "warning";
        } else {
            $message = "Database Error: Could not create link. " . $stmt_insert->error;
            $message_type = "error";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

// Store the message in the session and redirect back to the profile page
$_SESSION['link_message'] = $message;
$_SESSION['link_message_type'] = $message_type;

header("Location: professional_profile.php");
exit;
?>