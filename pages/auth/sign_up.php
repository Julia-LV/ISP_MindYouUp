<?php
/*
 * sign_up.php
 *
 * This page handles new user registration.
 * - Displays the sign-up form (HTML/Tailwind).
 * - Processes the form submission (PHP/SQL).
 */

// Start a session to store messages or user data
session_start();

// Include the database connection file.
// We use '../../config.php' to go up two directories (from /pages/auth/ to the root).
require_once '../../config.php';

// Initialize a variable to store messages (like errors or success)
$message = "";

// --- Form Processing ---
// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get and sanitize form data
    // ** UPDATED ** to match your form fields
    $first_name = $conn->real_escape_string(trim($_POST['first_name']));
    $last_name = $conn->real_escape_string(trim($_POST['last_name']));
    // We are collecting 'age' from the form, but not saving it (as per your table screenshot)
    // $age = (int) $_POST['age']; 
    $role = $conn->real_escape_string(trim($_POST['role']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password']; // We won't trim the password
    $agree_terms = isset($_POST['agree_terms']);

    // 2. Basic Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role)) {
        $message = "Please fill in all required fields.";
    } elseif (!$agree_terms) {
        $message = "You must agree to the terms and conditions.";
    } else {
        // 3. Check if email already exists
        // ** UPDATED ** column name to `E-mail`
        $sql_check = "SELECT User_ID FROM user_profile WHERE `E-mail` = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $message = "This email address is already registered.";
            } else {
                // 4. Email is new, proceed with insertion

                // ** SECURITY: Hash the password **
                // Your 'Password' column already contains hashes, so this is correct.
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // 5. Create the SQL INSERT query using Prepared Statements
                //
                // !!! IMPORTANT !!!
                // ** UPDATED ** these column names to EXACTLY match your database screenshot:
                // `First_Name`, `Last_Name`, `E-mail`, `Password`, `Role`
                //
                // We are not inserting `User_Image` (it can be NULL or have a default)
                // We are not inserting `age` (it's not in your table)
                //
                $sql_insert = "INSERT INTO user_profile (First_Name, Last_Name, `E-mail`, `Password`, `Role`) VALUES (?, ?, ?, ?, ?)";

                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    // Bind variables to the prepared statement as parameters
                    // ** UPDATED ** bind_param to match: s-s-s-s-s (5 strings)
                    $stmt_insert->bind_param("sssss", $first_name, $last_name, $email, $password_hash, $role);

                    // Attempt to execute the prepared statement
                    if ($stmt_insert->execute()) {
                        // Success! Redirect to the login page
                        header("Location: login.php?registration=success");
                        exit;
                    } else {
                        $message = "Something went wrong. Please try again later. (Error: " . $stmt_insert->error . ")";
                    }
                    // Close insert statement
                    $stmt_insert->close();
                } else {
                    $message = "Something went wrong. Please try again later. (Error: " . $conn->error . ")";
                }
            }
            // Close check statement
            $stmt_check->close();
        }
    }
    // Close database connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Ensures proper rendering and touch zooming on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Mind You Up</title>
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- ** UPDATED ** -->
    <!-- Link to your new external stylesheet -->
    <!-- The path '../../style.css' goes up two levels from /pages/auth/ to the root -->
    <link rel="stylesheet" href="../../signup_login.css">

</head>
<body class="bg-[#FFF7E1] flex items-center justify-center min-h-screen p-4">
    <!-- Sign-Up Card -->
    <!--
      Responsive classes:
      - w-full: 100% width on small screens
      - max-w-md: Limits width to 'medium' size on all screens
      - This combination makes it mobile-friendly and looks good on desktop.
    -->
    <div class="bg-white w-full max-w-md p-8 rounded-xl shadow-lg">

        <!-- Logo/Header -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-[#F26647]">Sign Up</h1>
            <p class="text-gray-500 mt-2">Create your account to get started</p>
        </div>

        <!-- Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
            
            <!-- Display Error/Success Messages -->
            <?php if (!empty($message)): ?>
                <div class="mb-4 p-3 rounded-md bg-red-100 text-red-700" role="alert">
                    <p><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <!-- Form fields -->
            <div class="space-y-4">
                
                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" id="first_name" name="first_name" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-[#005949]"
                           required>
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</Gglabel>
                    <input type="text" id="last_name" name="last_name" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-[#005949]"
                           required>
                </div>

                <!-- Age -->
                <!-- This field is still on the form, but the PHP logic will ignore it -->
                <div>
                    <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
                    <input type="number" id="age" name="age" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-[#005949]"
                           required>
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="role" name="role" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#005949] focus:border-[#005949]"
                            required>
                        <option value="" disabled selected>Select your role</option>
                        <option value="Patient">Patient</option>
                        <option value="Professional">Healthcare Professional</option>
                        <!-- Make sure these values ('Patient', 'Professional')
                             match what you expect to store in your 'Role' column -->
                    </select>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-[#005949]"
                           required>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-[#005949]"
                           required>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-center">
                    <input id="agree_terms" name="agree_terms" type="checkbox" 
                           class="h-4 w-4 text-[#005949] focus:ring-[#005949] border-gray-300 rounded"
                           required>
                    <label for="agree_terms" class="ml-2 block text-sm text-gray-900">
                        I agree to all the terms and conditions
                    </labe>
                </div>

                <!-- Sign Up Button -->
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#005949] hover:bg-[#004539] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Sign Up
                    </button>
                </div>
            </div>
        </form>

        <!-- Link to Log In -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account?
            <a href="loginv1.php" class="font-medium text-[#005949] hover:text-[#004539]">
                Log in
            </a>
        </p>
    </div>

</body>
</html>