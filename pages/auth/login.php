<?php
/*
 * login.php
 *
 * Handles user login and session creation.
 * - Displays the login form.
 * - Verifies credentials and redirects.
 */

// MUST be at the very top of the file to work
session_start();

// If user is already logged in, redirect them to their dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if ($_SESSION["role"] == 'Patient') {
        header("Location: ../patient/home_patient.php"); // Adjust path if needed
        exit;
    } elseif ($_SESSION["role"] == 'Professional') {
        header("Location: ../professional/home_professional.php"); // Adjust path if needed
        exit;
    }
}

// Include the database connection
require_once '../../config.php';

// Initialize variables
$message = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    // --- 1. Validate Input ---
    if (empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        // --- 2. Find User by Email ---
        // Select the columns we need to start a session
        $sql = "SELECT User_ID, First_Name, `Password`, `Role` FROM user_profile WHERE `E-mail` = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);

            if ($stmt->execute()) {
                $stmt->store_result();

                // --- 3. Check if User Exists ---
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($user_id, $first_name, $password_hash, $role);
                    
                    if ($stmt->fetch()) {
                        // --- 4. Verify The Password ---
                        // This checks the user's input against the stored hash
                        if (password_verify($password, $password_hash)) {
                            // Password is correct!
                            
                            // --- 5. Start Session ---
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["first_name"] = $first_name;
                            $_SESSION["role"] = $role;

                            // --- 6. Redirect based on Role ---
                            if ($role == 'Patient') {
                                header("Location: ../patient/home_patient.php"); // Adjust path
                                exit;
                            } elseif ($role == 'Professional') {
                                header("Location: ../professional/home_professional.php"); // Adjust path
                                exit;
                            }
                        } else {
                            // Password was wrong
                            $message = "Invalid email or password.";
                        }
                    }
                } else {
                    // No user with that email
                    $message = "Invalid email or password.";
                }
            } else {
                $message = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Mind You Up</title>
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Link to your external stylesheet -->
    <link rel="stylesheet" href="../../css/signup_login.css">
</head>
<body class="bg-[#FFF7E1] flex items-center justify-center min-h-screen p-4">


    <!-- Responsive Card -->
    <div class="bg-white w-full max-w-md p-6 sm:p-8 rounded-xl shadow-lg">

        <!-- Header -->
        <div class="text-center mb-6">
            <!-- I'm adding your logo here, based on your file structure "C:\xampp\htdocs\ISP_PROJECT\ISP_MindYouUp\assets\img\MYU logos\MYU_Horizontal Logo.png"-->
            <img src="../../assets/img/MYU logos/MYU_Horizontal Logo.png" alt="Mind You Up Logo" class="w-48 mx-auto mb-4">
            <p class="text-gray-500 mt-2">Welcome back! Please log in.</p>
        </div>

        <!-- Login Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
            
            <!-- Display Error Messages -->
            <?php if (!empty($message)): ?>
                <div class="mb-4 p-3 rounded-md bg-red-100 text-red-700" role="alert">
                    <p><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <!-- Display success message from registration -->
            <?php if (isset($_GET['registration']) && $_GET['registration'] == 'success'): ?>
                <div class="mb-4 p-3 rounded-md bg-green-100 text-green-700" role="alert">
                    <p>Registration successful! Please log in.</p>
                </div>
            <?php endif; ?>

            <!-- Form fields -->
            <div class="space-y-4">
                
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
                
                <!-- Forgot Password? -->
                <div class="text-right text-sm">
                    <a href="forgot_password.php" class="font-medium text-[#005949] hover:text-[#004539]">
                        Forgot Password?
                    </a>
                </div>

                <!-- Log In Button -->
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#005949] hover:bg-[#004539] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Log In
                    </button>
                </div>
            </div>
        </form>

        <!-- Link to Sign Up -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="sign_upv1.php" class="font-medium text-[#005949] hover:text-[#004539]">
                    Sign up
            </a>
        </p>
    </div>

</body>
</html>