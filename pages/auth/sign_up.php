<?php
/*
 * sign_up.php - FINAL, FIXED VERSION
 *
 * This file now explicitly sets the $name variable for
 * EACH input, which fixes the "$name is not set" error.
 */

// --- 1. PHP Logic ---
session_start();
$message = ""; 
$sticky_first_name = ""; $sticky_last_name = ""; $sticky_age = ""; $sticky_email = ""; $sticky_role = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../../config.php';
    
    // Use the ?? '' trick to prevent "undefined array key" warnings
    $first_name = $conn->real_escape_string(trim($_POST['first_name'] ?? ''));
    $last_name  = $conn->real_escape_string(trim($_POST['last_name'] ?? ''));
    $age        = $conn->real_escape_string(trim($_POST['age'] ?? ''));
    $email      = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $password   = $_POST['password'] ?? '';
    $role       = $conn->real_escape_string(trim($_POST['role'] ?? ''));
    $agree      = $_POST['agree_terms'] ?? '';

    // Set sticky values for the form
    $sticky_first_name = $first_name; $sticky_last_name = $last_name; 
    $sticky_age = $age; $sticky_email = $email; $sticky_role = $role;

    // --- Validation ---
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role) || empty($age)) {
        $message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (empty($agree)) {
        $message = "You must agree to the terms and conditions.";
    } else {
        // All good, check if email already exists
        $sql_check = "SELECT User_ID FROM user_profile WHERE `E-mail` = ?";
        if ($stmt = $conn->prepare($sql_check)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $message = "This email is already registered.";
            } else {
                // Email is new, proceed to insert
                $sql_insert = "INSERT INTO user_profile (First_Name, Last_Name, Age, `E-mail`, `Password`, `Role`) VALUES (?, ?, ?, ?, ?, ?)";
                
                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_insert->bind_param("ssisss", $first_name, $last_name, $age, $email, $hashed_password, $role);
                    
                    if ($stmt_insert->execute()) {
                        // Success! Redirect to login with a success message
                        header("Location: login.php?registration=success");
                        exit;
                    } else {
                        $message = "Something went wrong. Please try again later.";
                    }
                    $stmt_insert->close();
                }
            }
            $stmt->close();
        }
    }
    $conn->close();
}

// --- 2. Page Display ---
$page_title = 'Sign Up - Mind You Up';
include '../../components/header_component.php'; 
$form_title = 'Sign Up';
$form_subtitle = 'Create your account to get started';
include '../../components/auth_card_start.php'; 

if (!empty($message)) {
    echo '<div class="mb-4 p-3 rounded-md bg-red-100 text-red-700" role="alert"><p>'.htmlspecialchars($message).'</p></div>';
} 

// --- Form Fields (THE FIX) ---
// We now set $name for every input.
$id = 'first_name'; $name = 'first_name'; $label = 'First Name'; $type = 'text'; $value = $sticky_first_name; $autocomplete = 'given-name';
include '../../components/input.php';

$id = 'last_name'; $name = 'last_name'; $label = 'Last Name'; $type = 'text'; $value = $sticky_last_name; $autocomplete = 'family-name';
include '../../components/input.php';

$id = 'age'; $name = 'age'; $label = 'Age'; $type = 'number'; $value = $sticky_age; $autocomplete = 'off';
include '../../components/input.php';

// Special: Role Dropdown
?>
<div>
    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
    <select id="role" name="role" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-[#005949]">
        <option value="" <?php if ($sticky_role == "") echo 'selected'; ?>>Select your role</option>
        <option value="Patient" <?php if ($sticky_role == "Patient") echo 'selected'; ?>>Patient</option>
        <option value="Professional" <?php if ($sticky_role == "Professional") echo 'selected'; ?>>Healthcare Professional</option>
    </select>
</div>
<?php

$id = 'email'; $name = 'email'; $label = 'Email'; $type = 'email'; $value = $sticky_email; $autocomplete = 'email';
include '../../components/input.php';

$id = 'password'; $name = 'password'; $label = 'Password'; $type = 'password'; $value = ''; $autocomplete = 'new-password';
include '../../components/input.php';

// Special: Terms and Conditions
?>
<div class="flex items-center">
    <input id="agree_terms" name="agree_terms" type="checkbox" class="h-4 w-4 text-green-700 focus:ring-green-500 border-gray-300 rounded">
    <label for="agree_terms" class="ml-2 block text-sm text-gray-900">
        I agree to all the terms and conditions
    </abel>
</div>
<?php

$button_text = 'Sign Up'; $button_type = 'submit'; $extra_classes = 'w-full'; 
include '../../components/button.php';

$link_text = "Already have an account?"; $link_url = 'login.php'; $link_label = 'Log in';
include '../../components/auth_card_end.php'; 
?>