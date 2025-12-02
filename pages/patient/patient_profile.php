<?php
session_start();
include('../../config.php');

// 1. Security Check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Get User ID from session
$user_id = $_SESSION['user_id'];

// 3. Database Query (Joining user_profile and patient_profile)
$sql = "SELECT 
            up.User_ID, up.First_Name, up.Last_Name, up.Age, up.User_Image, up.Email,
            pp.Patient_Status, pp.Treatment_Type, pp.Start_Date
        FROM user_profile up
        LEFT JOIN patient_profile pp ON up.User_ID = pp.User_ID
        WHERE up.User_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Error: User profile not found.";
    exit;
}

// 4. IMAGE PATH FIX: Constructing the RELATIVE path 
$db_image_path = $user['User_Image'];
$default_image = 'https://via.placeholder.com/150';

if (!empty($db_image_path)) {
    // Remove the leading slash (/) and prepend the necessary folder traversal (../../)
    $relative_path_segment = substr($db_image_path, 1); 
    $profile_image = '../../' . $relative_path_segment;
} else {
    $profile_image = $default_image;
}

// CACHE BUSTER FIX: Append a query parameter with the current time.
$cache_buster = time();
$final_image_src = htmlspecialchars($profile_image) . "?" . $cache_buster;

// Format Start Date
$start_date_display = $user['Start_Date'] ? date("d-m-Y", strtotime($user['Start_Date'])) : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#E9F0E9] min-h-screen">
<?php include '../../includes/navbar.php'; ?>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900">Patient Profile</h1>
            <p class="mt-2 text-gray-600">View your personal and treatment information.</p>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            
            <div class="px-4 py-5 sm:px-6 flex flex-col items-center pb-8 border-b border-gray-200">
                <img class="h-32 w-32 rounded-full object-cover border-4 border-[#005949]" 
                     src="<?php echo $final_image_src; ?>" 
                     alt="Profile Picture"> 
                
                <h2 class="mt-4 text-2xl font-bold text-gray-900">
                    <?php echo htmlspecialchars($user['First_Name'] . ' ' . $user['Last_Name']); ?>
                </h2>
                <p class="text-sm text-gray-500">User ID: #<?php echo htmlspecialchars($user['User_ID']); ?></p>
            </div>

            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Age</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php echo htmlspecialchars($user['Age']); ?> years
                        </dd>
                    </div>

                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php echo htmlspecialchars($user['Email']); ?>
                        </dd>
                    </div>

                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Patient Status</dt>
                        <dd class="mt-1 text-sm font-semibold text-[#005949] sm:mt-0 sm:col-span-2">
                            <?php echo htmlspecialchars($user['Patient_Status'] ?? 'Not defined'); ?>
                        </dd>
                    </div>

                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Treatment Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php echo htmlspecialchars($user['Treatment_Type'] ?? 'Not defined'); ?>
                        </dd>
                    </div>

                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php echo htmlspecialchars($start_date_display); ?>
                        </dd>
                    </div>
                </dl>
            </div>
            
            <div class="bg-white px-4 py-5 sm:px-6 border-t border-gray-200">
                <form action="edit_profile.php" method="GET">
                    <?php
                        // Configuration for button.php
                        $button_text = 'Edit Profile';
                        $button_type = 'submit'; 
                        $extra_classes = 'w-full sm:w-auto'; 
                        
                        // FIX: Using __DIR__ for a reliable path for include
                        $path_to_button = __DIR__ . '/../../components/simple_button.php';

                        if (file_exists($path_to_button)) {
                            include($path_to_button); 
                        } else {
                            echo "<p class='text-red-600 font-bold text-center'>ERROR: Button component file not found.</p>";
                        }
                    ?>
                </form>
            </div>

        </div>
    </div>

</body>
</html>