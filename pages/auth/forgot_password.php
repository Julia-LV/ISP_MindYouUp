<?php
/*
 * forgot_password.php
 *
 * Page for a user to request a password reset link.
 * It uses our existing components.
 */

// --- 1. PHP Logic ---
session_start();
$message = ""; 
$message_type = "error"; // "error" or "success"

// If user is already logged in, redirect them
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if (isset($_SESSION["role"]) && $_SESSION["role"] == "Patient") {
        header("Location: ../patient/home_patient.php");
    } else {
        header("Location: ../professional/home_professional.php");
    }
    exit;
}

require_once '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));

    if (empty($email)) {
        $message = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Check if this email exists in our user_profile table
        $sql_check = "SELECT User_ID FROM user_profile WHERE `E-mail` = ?";
        if ($stmt = $conn->prepare($sql_check)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // --- User exists! Now generate the token ---
                
                // 1. Create a secure, random token
                $token = bin2hex(random_bytes(32)); 
                
                // 2. Set an expiration time (e.g., 1 hour from now)
                $expires = time() + 3600; // time() is in seconds. 3600 = 1 hour.

                // 3. Store this token in our new 'password_resets' table
                $sql_insert = "INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)";
                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $stmt_insert->bind_param("ssi", $email, $token, $expires);
                    $stmt_insert->execute();

                    // 4. Create the reset link
                    // This is the link we WOULD email. 
                    // Note: This assumes your site is at 'http://localhost/ISP_PROJECT/ISP_MindYouUp/'
                    // Adjust the path if needed!
                    $reset_link = "http://localhost/ISP_PROJECT/ISP_MindYouUp/pages/auth/reset_password.php?token=" . $token;

                    // --- !!! DEVELOPER HACK FOR TESTING !!! ---
                    // Since we can't send email from XAMPP easily, we will
                    // display the link on the page in a success message.
                    
                    $message_type = "success";
                    $message = 'User found! Click this link to reset: <br><a href="' . htmlspecialchars($reset_link) . '" class="font-bold underline">RESET MY PASSWORD</a>';

                    // --- !!! END HACK !!! ---

                    /* // --- REAL WORLD CODE (Replaced by the hack above) ---
                    $subject = "Your Password Reset Link for Mind You Up";
                    $body = "Click this link to reset your password: " . $reset_link;
                    $headers = "From: no-reply@mindyouup.com";
                    
                    if (mail($email, $subject, $body, $headers)) {
                         $message_type = "success";
                         $message = "A reset link has been sent to your email address.";
                    } else {
                         $message = "Could not send email. Please contact support.";
                    }
                    // --- END REAL WORLD CODE ---
                    */

                    $stmt_insert->close();
                }

            } else {
                // No user found with this email.
                // We give a generic success message for security,
                // so attackers can't guess which emails are registered.
                $message_type = "success";
                $message = "If an account with that email exists, a reset link has been sent.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}

// --- 2. Page Display ---
$page_title = 'Forgot Password - Mind You Up';
include '../../components/header_component.php'; 
$form_title = 'Forgot Password';
$form_subtitle = 'Enter your email to get a reset link';
include '../../components/auth_card_start.php'; 

// --- Message Handling ---
if (!empty($message)) {
    // Check if it's an error or success message
    if ($message_type == "success") {
        echo '<div class="mb-4 p-3 rounded-md bg-green-100 text-green-700" role="alert">'. $message .'</p></div>'; // Note: No htmlspecialchars, so the link is clickable
    } else {
        echo '<div class="mb-4 p-3 rounded-md bg-red-100 text-red-700" role="alert"><p>'. htmlspecialchars($message) .'</p></div>';
    }
} 

// --- Form Fields ---
$id = 'email'; $name = 'email'; $label = 'Email'; $type = 'email'; $value = ''; $autocomplete = 'email';
include '../../components/input.php';

$button_text = 'Send Reset Link'; $button_type = 'submit'; $extra_classes = 'w-full'; 
include '../../components/button.php';

$link_text = "Remembered your password?"; $link_url = 'login.php'; $link_label = 'Log in';
include '../../components/auth_card_end.php'; 
?>