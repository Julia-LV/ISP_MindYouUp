<?php
// --- 1. PHP Logic ---
session_start();
$message = "";
// Sticky values
$sticky_first_name = "";
$sticky_last_name = "";
$sticky_dob = "";
$sticky_email = "";
$sticky_role = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../../config.php';

    // Get values
    $first_name = $conn->real_escape_string(trim($_POST['first_name'] ?? ''));
    $last_name  = $conn->real_escape_string(trim($_POST['last_name'] ?? ''));
    $dob        = $conn->real_escape_string(trim($_POST['dob'] ?? '')); // Changed from age to dob
    $email      = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $password   = $_POST['password'] ?? '';
    $role       = $conn->real_escape_string(trim($_POST['role'] ?? ''));
    $agree      = $_POST['agree_terms'] ?? '';

    // Set sticky values
    $sticky_first_name = $first_name;
    $sticky_last_name = $last_name;
    $sticky_dob = $dob;
    $sticky_email = $email;
    $sticky_role = $role;

    // --- Validation ---
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role)) {
        $message = "Please fill in all required fields.";

        // Check for DOB *only if* the selected role is "Patient"
    } elseif ($role == 'Patient' && empty($dob)) {
        $message = "Please enter your Date of Birth. This is required for patients.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (empty($agree)) {
        $message = "You must agree to the terms and conditions.";
    } else {
        // Check if email exists
        $sql_check = "SELECT User_ID FROM user_profile WHERE `Email` = ?";
        if ($stmt = $conn->prepare($sql_check)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = "This email is already registered.";
            } else {
                // Email is new, insert data
                // CHANGED: 'Age' -> 'Birthday'
                $sql_insert = "INSERT INTO user_profile (First_Name, Last_Name, Birthday, `Email`, `Password`, `Role`) VALUES (?, ?, ?, ?, ?, ?)";

                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // If DOB is empty (Professional), save NULL.
                    $dob_to_save = empty($dob) ? NULL : $dob;

                    // CHANGED: bind_param types from "ssisss" to "ssssss" 
                    // (Date is a string in SQL, unlike Age which was an int)
                    $stmt_insert->bind_param("ssssss", $first_name, $last_name, $dob_to_save, $email, $hashed_password, $role);

                    if ($stmt_insert->execute()) {
                        header("Location: login.php?registration=success");
                        exit;
                    } else {
                        $message = "Something went wrong. Please try again later. (Error: " . $stmt_insert->error . ")";
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
$no_layout = true;
$body_class = "bg-[#E9F0E9] min-h-screen flex items-center justify-center p-4 ";
include '../../components/header_component.php';

$form_title = 'Sign Up';
$form_subtitle = 'Create your account to get started';
include '../../components/auth_card_start.php';

if (!empty($message)) {
    echo '<div class="mb-4 p-3 rounded-md bg-red-100 text-red-700" role="alert"><p>' . htmlspecialchars($message) . '</p></div>';
}

// --- Form Fields ---
$id = 'first_name';
$name = 'first_name';
$label = 'First Name';
$type = 'text';
$value = $sticky_first_name;
$autocomplete = 'given-name';
include '../../components/input.php';

$id = 'last_name';
$name = 'last_name';
$label = 'Last Name';
$type = 'text';
$value = $sticky_last_name;
$autocomplete = 'family-name';
include '../../components/input.php';

// --- DATE OF BIRTH FIELD (Replaces Age) ---
// Note: We use type='date'.
$id = 'dob';
$name = 'dob';
$label = 'Date of Birth';
$type = 'date';
$value = $sticky_dob;
$autocomplete = 'bday';
include '../../components/input.php';

// --- ROLE DROPDOWN ---
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

$id = 'email';
$name = 'email';
$label = 'Email';
$type = 'email';
$value = $sticky_email;
$autocomplete = 'email';
include '../../components/input.php';

$id = 'password';
$name = 'password';
$label = 'Password';
$type = 'password';
$value = '';
$autocomplete = 'new-password';
include '../../components/input.php';

// --- TERMS ---
// --- TERMS ---
?>
<div class="flex items-center">
    <input id="agree_terms" name="agree_terms" type="checkbox" class="h-4 w-4 text-green-700 focus:ring-green-500 border-gray-300 rounded">
    <label for="agree_terms" class="ml-2 block text-sm text-gray-900">
        I agree to all the
        <button type="button" onclick="toggleModal('terms-modal')" class="text-green-700 font-bold hover:underline focus:outline-none">
            terms and conditions
        </button>
    </label>
</div>

<div id="terms-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('terms-modal')"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">

                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-xl font-semibold leading-6 text-gray-900 mb-2" id="modal-title">Terms & Privacy Policy</h3>

                            <div class="mt-4 max-h-[60vh] overflow-y-auto p-4 border rounded-md bg-gray-50 text-sm text-gray-600 space-y-4 text-justify pr-2">

                                <div>
                                    <h4 class="font-bold text-gray-800 text-base">Privacy Policy</h4>
                                    <p class="text-xs text-gray-400">Last Updated: December 12, 2025</p>
                                </div>

                                <div>
                                    <strong class="text-gray-800">1. Introduction</strong>
                                    <p class="mt-1">This Privacy Policy explains how NEXUS, the provider of the TICTRACKER mobile application ("tictracker", "we", "our", or "us") collects, uses, discloses, and safeguards your information when you use our mobile application (the "App"), a digital space built for neurodivergent women to help track energy, avoid overwhelm, and feel more in sync with oneself.</p>
                                    <p class="mt-1">By using the App, you agree to the collection and use of information in accordance with this policy. If you do not agree with our policies and practices, please do not download, register with, or use this App.</p>
                                </div>

                                <div>
                                    <strong class="text-gray-800">2. Information We Collect</strong>
                                    <p class="mt-1 italic">Personal Information:</p>
                                    <ul class="list-disc pl-5 mt-1 space-y-1">
                                        <li>Account information (email address, first name, last name, password)</li>
                                        <li>User profile information</li>
                                        <li>Notification preferences (check-ins, insights)</li>
                                        <li>Onboarding status</li>
                                    </ul>

                                    <p class="mt-2 italic">Energy and Activity Data:</p>
                                    <ul class="list-disc pl-5 mt-1 space-y-1">
                                        <li>Daily tic logs, activities, and emotional logs</li>
                                        <li>Activity categories, duration, and energy impact</li>
                                        <li>Custom activities created by you</li>
                                    </ul>
                                </div>

                                <div>
                                    <strong class="text-gray-800">3. How We Use Your Information</strong>
                                    <p class="mt-1">We use the information we collect to:</p>
                                    <ul class="list-disc pl-5 mt-1 space-y-1">
                                        <li>Provide, operate, and maintain the App</li>
                                        <li>Create and manage your account</li>
                                        <li>Process your daily logs and generate insights</li>
                                        <li>Send you technical notices, updates, and support messages</li>
                                        <li>Monitor and analyze trends and usage</li>
                                    </ul>
                                </div>

                                <div>
                                    <strong class="text-gray-800">4. Data Storage and Security</strong>
                                    <p class="mt-1">We implement appropriate technical and organizational security measures designed to protect the security of any personal information we process. However, please also remember that we cannot guarantee that the internet itself is 100% secure.</p>
                                </div>

                                <div>
                                    <strong class="text-gray-800">5. GDPR Compliance</strong>
                                    <p class="mt-1">If you are a resident of the European Economic Area (EEA), you have certain data protection rights. Mind You Up aims to take reasonable steps to allow you to correct, amend, delete, or limit the use of your Personal Data.</p>
                                    <p class="mt-1"><strong>Legal Basis for Processing:</strong></p>
                                    <ul class="list-disc pl-5 mt-1 space-y-1">
                                        <li><strong>Consent:</strong> You have given your consent for processing personal data.</li>
                                        <li><strong>Performance of a contract:</strong> Processing is necessary for the performance of a contract with you.</li>
                                        <li><strong>Legal obligations:</strong> Processing is necessary for compliance with a legal obligation.</li>
                                    </ul>
                                </div>

                                <div>
                                    <strong class="text-gray-800">6. Changes to This Privacy Policy</strong>
                                    <p class="mt-1">We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page. You are advised to review this Privacy Policy periodically for any changes.</p>
                                </div>

                                <div>
                                    <strong class="text-gray-800">7. Contact Us</strong>
                                    <p class="mt-1">If you have any questions about this Privacy Policy, you can contact us by email: <a href="mailto:myu.geral@gmail.com" class="text-blue-600 hover:underline">myu.geral@gmail.com</a></p>
                                </div>

                                <div class="text-center pt-4 text-xs text-gray-400">
                                    <p>Follow us: mind_you_up</p>
                                    <p>&copy; 2025 Mind You Up. All rights reserved.</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" onclick="toggleModal('terms-modal'); document.getElementById('agree_terms').checked = true;" class="inline-flex w-full justify-center rounded-md bg-green-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 sm:ml-3 sm:w-auto">
                        I Understand & Agree
                    </button>
                    <button type="button" onclick="toggleModal('terms-modal')" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        modal.classList.toggle('hidden');
    }
</script>

<?php
$label = 'Sign Up';
$type = 'submit';
include '../../components/button.php';

$link_text = "Already have an account?";
$link_url = 'login.php';
$link_label = 'Log in';
include '../../components/auth_card_end.php';
?>