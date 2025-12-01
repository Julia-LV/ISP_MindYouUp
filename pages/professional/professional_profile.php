<?php
session_start();
include('../../config.php');

// 1. Security Check: Only allow logged-in Professionals
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Get Professional ID from session
$professional_id = $_SESSION['user_id'];

// 3. Database Query (Fetch Basic Profile Details ONLY)
// Removida a tentativa de JOIN com professional_profile, pois só precisamos da User_ID.
$sql = "SELECT 
            u.User_ID, u.First_Name, u.Last_Name, u.Age, u.User_Image, u.Email
        FROM user_profile u
        WHERE u.User_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professional_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "Error: Professional profile not found.";
    exit;
}

// 4. IMAGE PATH FIX: Constructing the RELATIVE path 
$db_image_path = $user['User_Image'];
$default_image = 'https://via.placeholder.com/150';

if (!empty($db_image_path)) {
    $relative_path_segment = substr($db_image_path, 1); 
    $profile_image = '../../' . $relative_path_segment;
} else {
    $profile_image = $default_image;
}

// CACHE BUSTER FIX
$cache_buster = time();
$final_image_src = htmlspecialchars($profile_image) . "?" . $cache_buster;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Professional Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#E9F0E9] min-h-screen">
<?php include '../../includes/navbar.php'; ?>
   
    <?php if (isset($_SESSION['link_message'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="p-4 rounded-md <?php 
                $type = $_SESSION['link_message_type'];
                if ($type == 'success') echo 'bg-green-100 text-green-700';
                elseif ($type == 'warning') echo 'bg-yellow-100 text-yellow-700';
                else echo 'bg-red-100 text-red-700';
            ?>" role="alert">
                <p><?php echo htmlspecialchars($_SESSION['link_message']); ?></p>
            </div>
        </div>
        <?php 
        unset($_SESSION['link_message']); 
        unset($_SESSION['link_message_type']); 
        ?>
    <?php endif; ?>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900">Professional Profile</h1>
            <p class="mt-2 text-gray-600">View and manage your professional details.</p>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            
            <div class="px-4 py-5 sm:px-6 flex flex-col items-center pb-8 border-b border-gray-200">
                <img class="h-32 w-32 rounded-full object-cover border-4 border-[#005949]" 
                     src="<?php echo $final_image_src; ?>" 
                     alt="Profile Picture"> 
                
                <h2 class="mt-4 text-2xl font-bold text-gray-900">
                    <?php echo htmlspecialchars($user['First_Name'] . ' ' . $user['Last_Name']); ?>
                </h2>
                <p class="text-sm text-gray-500">Professional ID: #<?php echo htmlspecialchars($user['User_ID']); ?></p>
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
                        <dt class="text-sm font-medium text-gray-500">Role</dt>
                        <dd class="mt-1 text-sm font-semibold text-[#005949] sm:mt-0 sm:col-span-2">
                            Professional
                        </dd>
                    </div>

                </dl>
            </div>
            
            <div class="bg-white px-4 py-5 sm:px-6 border-t border-gray-200 flex justify-between space-x-4">
                
                <form action="edit_professional_profile.php" method="GET" class="w-1/2">
                    <?php
                        $button_text = 'Edit Profile';
                        $button_type = 'submit'; 
                        $extra_classes = 'w-full'; 
                        $path_to_button = __DIR__ . '/../../components/button.php';

                        if (file_exists($path_to_button)) {
                            include($path_to_button); 
                        } else {
                            echo "<p class='text-red-600 font-bold text-center'>ERROR: Button component file not found.</p>";
                        }
                    ?>
                </form>

                <button type="button" onclick="document.getElementById('link-form-container').classList.toggle('hidden')" 
                        class="w-1/2 py-2.5 px-4 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Link Patient
                </button>
            </div>

            <div id="link-form-container" class="px-4 py-5 sm:px-6 bg-gray-50 hidden">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 border-b pb-2">Link Patient to Your Profile</h3>
                
                <form action="add_patient_link.php" method="POST" class="space-y-4">
                    <input type="hidden" name="professional_id" value="<?php echo htmlspecialchars($professional_id); ?>">
                    
                    <div>
                        <label for="patient_id_input" class="block text-sm font-medium text-gray-700">Patient User ID</label>
                        <input type="number" name="patient_id" id="patient_id_input" required
                               placeholder="Enter Patient's User ID"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">The Patient ID must be a number.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#005949] hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#005949]">
                            Confirm Link
                        </button>
                    </div>
                </form>
            </div>
            </div>
    </div>
    
    <script>
        // Simple JS to toggle the form visibility (optional, but good UX)
    </script>

</body>
</html>