<?php
/*
 * reset_password.php
 *
 * Page for a user to enter a NEW password.
 * It validates the token from the URL.
 */

// --- 1. PHP Logic ---
session_start();
$message = "";
$message_type = "error";
$token = $_GET['token'] ?? '';
$token_is_valid = false;
$user_email = ""; // We'll get this from the token

require_once '../../config.php';

// We now check for the token in the POST data OR the GET data.
$token = $_POST['token'] ?? $_GET['token'] ?? '';

if (empty($token)) {
    $message = "Invalid or missing reset token.";
} else {
    // Token exists, let's validate it
    $sql_check = "SELECT email, expires FROM password_resets WHERE token = ? AND expires > ?";
    $current_time = time();
    
    if ($stmt = $conn->prepare($sql_check)) {
        $stmt->bind_param("si", $token, $current_time);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            // Token is valid and not expired!
            $token_is_valid = true;
            $stmt->bind_result($user_email, $expires_from_db);
            $stmt->fetch();
        } else {
            $message = "This link is invalid or has expired. Please request a new one.";
        }
        $stmt->close();
    }
}

// Check if the form was submitted (for the *new* password)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_is_valid) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm)) {
        $message = "Please enter and confirm your new password.";
    } elseif ($password !== $password_confirm) {
        $message = "The two passwords do not match.";
    } else {
        // All good! Update the user's password in the MAIN user_profile table
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql_update = "UPDATE user_profile SET `Password` = ? WHERE `E-mail` = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ss", $hashed_password, $user_email);
            $stmt_update->execute();
            
            // Password updated! Now delete the token so it can't be reused.
            $sql_delete = "DELETE FROM password_resets WHERE token = ?";
            if ($stmt_delete = $conn->prepare($sql_delete)) {
                $stmt_delete->bind_param("s", $token);
                $stmt_delete->execute();
                $stmt_delete->close();
            }

            // Redirect to login page with a success message
            header("Location: login.php?reset=success");
            exit;

        } else {
            $message = "An error occurred. Please try again.";
        }
        $stmt_update->close();
    }
}
$conn->close();

// --- 2. Page Display ---
$page_title = 'Reset Password - Mind You Up';
include '../../components/header_component.php'; 

?>

<!-- 
  
  We add the <body> tag that this page needs.
-->
<body class="bg-[#FFFDF5] flex items-center justify-center min-h-screen p-4">

<?php

$form_title = 'Reset Your Password';
$form_subtitle = 'Enter your new password below';
include '../../components/auth_card_start.php'; 

// --- Message Handling ---
if (!empty($message)) {
    if ($message_type == "success") {
        echo '<div class="mb-4 p-3 rounded-md bg-green-100 text-green-700" role="alert"><p>'. htmlspecialchars($message) .'</p></div>';
    } else {
        echo '<div class="mb-4 p-3 rounded-md bg-red-100 text-red-700" role="alert"><p>'. htmlspecialchars($message) .'</p></div>';
    }
} 

// --- Form Fields ---
// We only show the form if the token was valid
if ($token_is_valid) {

    // We add a hidden input to "tape" the token to the form.
    echo '<input type="hidden" name="token" value="' . htmlspecialchars($token) . '">';
    
    // Include Password input
    $id = 'password'; $name = 'password'; $label = 'New Password'; $type = 'password'; $value = ''; $autocomplete = 'new-password';
    include '../../components/input.php';

    // Include Password Confirm input
    $id = 'password_confirm'; $name = 'password_confirm'; $label = 'Confirm New Password'; $type = 'password'; $value = ''; $autocomplete = 'new-password';
    include '../../components/input.php';

    // Include Button component
    $button_text = 'Save New Password'; $button_type = 'submit'; $extra_classes = 'w-full'; 
    include '../../components/button.php';

} else {
    // Token was invalid, so we don't show the form.
    // The error message is already being displayed above.
    // We can add a link to request a new one.
    echo '<div class="text-center">';
    echo '  <a href="forgot_password.php" class="font-medium text-green-700 hover:text-[#004539]">Request a new reset link</a>';
    echo '</div>';
}

$link_text = "Remembered your password?"; $link_url = 'login.php'; $link_label = 'Log in';
include '../../components/auth_card_end.php'; 
?>