<?php
session_start();
include('../../config.php');

// 1. Security Check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$professional_id = $_SESSION['user_id'];
$message = '';
$message_type = ''; // 'success' or 'error'

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize text inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $age = (int)$_POST['age'];

    // Start Transaction (Good practice)
    $conn->begin_transaction();
    $update_successful = false;

    // Update Text Data (First Name, Last Name, Age)
    $sql_update = "UPDATE user_profile SET First_Name = ?, Last_Name = ?, Age = ? WHERE User_ID = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssii", $first_name, $last_name, $age, $professional_id);

    if ($stmt->execute()) {
        $update_successful = true;
    } 
    $stmt->close();

    // 3. Handle Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "../../images/users/"; // Ensure this folder exists
        
        // Create the directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_types)) {
            // New filename: user_ID_timestamp.ext (for unique file names)
            $new_filename = "user_" . $professional_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            // Path to store in DB (relative path for the website to use)
            $db_path = "/images/users/" . $new_filename;

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                // Update DB with new image path
                $sql_img = "UPDATE user_profile SET User_Image = ? WHERE User_ID = ?";
                $stmt_img = $conn->prepare($sql_img);
                $stmt_img->bind_param("si", $db_path, $professional_id);
                
                if ($stmt_img->execute()) {
                    $update_successful = true;
                } else {
                    $update_successful = false;
                }
                $stmt_img->close();
                
            } else {
                $message = "Error uploading the file.";
                $message_type = "error";
                $update_successful = false;
            }
        } else {
            $message = "Invalid file type. Only JPG, JPEG, PNG & GIF allowed.";
            $message_type = "error";
            $update_successful = false;
        }
    }
    
    // Commit or Rollback transaction
    if ($update_successful) {
        $conn->commit();
        $message = "Profile updated successfully.";
        $message_type = "success";
    } elseif (empty($message)) {
        $conn->rollback();
        $message = "Error updating profile.";
        $message_type = "error";
    }
}

// 4. Fetch Current Data (to pre-fill the form)
$sql = "SELECT First_Name, Last_Name, Age, User_Image FROM user_profile WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professional_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// IMAGE PATH FIX: Constructing the RELATIVE path 
$db_image_path = $user['User_Image'];
$default_image = 'https://via.placeholder.com/150';

if (!empty($db_image_path)) {
    // Path: Go up two levels (../../) to get to the root, then find the image.
    $relative_path_segment = substr($db_image_path, 1); 
    $current_image = '../../' . $relative_path_segment;
} else {
    $current_image = $default_image;
}

// CACHE BUSTER FIX: Append timestamp for image preview
$cache_buster = time();
$final_image_src = htmlspecialchars($current_image) . "?" . $cache_buster;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Professional Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#E9F0E9] min-h-screen">
<?php include '../../includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Edit Professional Profile</h1>
            <a href="professional_profile.php" class="text-sm text-[#005949] hover:underline">Back to Profile</a>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
            
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <div class="flex flex-col items-center">
                    <span class="text-gray-700 text-sm font-bold mb-2">Current Photo</span>
                    <img class="h-24 w-24 rounded-full object-cover border-2 border-[#005949] mb-4" 
                         src="<?php echo $final_image_src; ?>" 
                         alt="Current Profile">
                    
                    <label class="block text-sm font-medium text-gray-700">
                        Change Photo
                    </label>
                    <input type="file" name="profile_image" accept="image/*" 
                           class="mt-1 block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-green-50 file:text-[#005949]
                                  hover:file:bg-green-100">
                </div>

                <hr class="border-gray-200">

                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first_name" required
                           value="<?php echo htmlspecialchars($user['First_Name']); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required
                           value="<?php echo htmlspecialchars($user['Last_Name']); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>

                <div>
                    <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
                    <input type="number" name="age" id="age" required
                           value="<?php echo htmlspecialchars($user['Age']); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>

                <div class="pt-4">
                    <?php
                        // Configuração para button.php
                        $button_text = 'Save Changes';
                        $button_type = 'submit'; 
                        $extra_classes = 'w-full'; 
                        
                        // FIX: Usando __DIR__ para um caminho fiável para include
                        $path_to_button = __DIR__ . '/../../components/simple_button.php';

                        if (file_exists($path_to_button)) {
                            include($path_to_button); 
                        } else {
                            echo "<p class='text-red-600 font-bold text-center'>ERROR: Button component file not found.</p>";
                        }
                    ?>
                </div>

            </form>
        </div>
    </div>

</body>
</html>