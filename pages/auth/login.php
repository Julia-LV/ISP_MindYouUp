<?php
/*
 * login.php - FINAL, FIXED VERSION
 *
 * This file now explicitly sets the $name variable for
 * each input, which fixes the "password in email" bug.
 */

// --- 1. PHP Logic ---
session_start();
$message = ""; 
$sticky_email = ""; 

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
    $password = $_POST['password'] ?? '';
    $sticky_email = $email;
    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
    } else {
        $sql = "SELECT User_ID, First_Name, `Role`, `Password` FROM user_profile WHERE `Email` = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email); $stmt->execute(); $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($user_id, $first_name, $role, $hashed_password);
                if ($stmt->fetch()) {
                    if (password_verify($password, $hashed_password)) {
                        session_start();
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $user_id;
                        $_SESSION["first_name"] = $first_name;
                        $_SESSION["role"] = $role;
                        if ($role == "Patient") {
                            header("Location: ../patient/home_patient.php");
                        } else {
                            header("Location: ../professional/home_professional.php");
                        }
                        exit;
                    } else { $message = "Invalid email or password."; }
                }
            } else { $message = "Invalid email or password."; }
            $stmt->close();
        }
    }
    $conn->close();
}
// --- 2. Page Display ---
$page_title = 'Log In - Mind You Up';
$no_layout = true; // disable topbar + wrapper for this page
// Define Custom Body Classes for Centering & Background Color
$body_class = "bg-[#E9F0E9] min-h-screen flex items-center justify-center p-4 ";

include '../../components/header_component.php'; 


$form_title = 'Log In';
$form_subtitle = 'Welcome back! Please enter your details';
include '../../components/auth_card_start.php'; 

// --- Display Messages ---
if (!empty($message)) {
    echo '<div class="mb-4 p-3 rounded-md bg-red-100 text-red-700" role="alert"><p>'.htmlspecialchars($message).'</p></div>';
} 
elseif (isset($_GET['registration']) && $_GET['registration'] == 'success') {
    echo '<div class="mb-4 p-3 rounded-md bg-green-100 text-green-700" role="alert"><p>Registration successful! Please log in.</p></div>';
}
elseif (isset($_GET['reset']) && $_GET['reset'] == 'success') {
    echo '<div class="mb-4 p-3 rounded-md bg-green-100 text-green-700" role="alert"><p>Password has been reset successfully! You can now log in.</p></div>';
}

// --- Form Fields (THE FIX) ---
// We now set $name for every input.
$id = 'email'; $name = 'email'; $label = 'Email'; $type = 'email'; $value = $sticky_email; $autocomplete = 'email';
include '../../components/input.php';

$id = 'password'; $name = 'password'; $label = 'Password'; $type = 'password'; $value = ''; $autocomplete='off';
include '../../components/input.php';
?>
<div class="flex items-center justify-end">
    <div class="text-sm">
        <a href="forgot_password.php" class="font-medium text-green-700 hover:text-[#004539]">
            Forgot your password?
        </a>
    </div>
</div>
<?php
// CHANGE $button_text TO $label
// CHANGE $button_type TO $type
$label = 'Log In'; $type = 'submit'; 
// Note: Your button.php uses $width, not $extra_classes, 
// but it defaults to w-full anyway, so you can leave width out.

include '../../components/button.php';

$link_text = "Don't have an account?"; $link_url = 'sign_up.php'; $link_label = 'Sign up';
include '../../components/auth_card_end.php'; 
?>