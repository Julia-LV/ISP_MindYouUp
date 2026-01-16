<?php
// pages/patient/contact_patient.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../../config.php');

// --- ACCESS CONTROL --- //
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

$currentUser = $_SESSION['user_id']; // Patient's User_ID

// --- FETCH ASSIGNED PROFESSIONALS AND LAST MESSAGE --- //

$sql = "
SELECT 
    ppl.Professional_ID AS Prof_User_ID,
    CONCAT(u.First_Name, ' ', u.Last_Name) AS Name,
    u.User_Image, 
    MAX(cl.Chat_Time) AS Last_Message_Time,
    SUBSTRING_INDEX(
        GROUP_CONCAT(cl.Chat_Text ORDER BY cl.Chat_Time DESC SEPARATOR '||'),
        '||', 1
    ) AS Last_Message
FROM patient_professional_link ppl
JOIN user_profile u ON ppl.Professional_ID = u.User_ID
LEFT JOIN chat_log cl 
    ON (
        (cl.Sender = ? AND cl.Receiver = u.User_ID)
        OR 
        (cl.Sender = u.User_ID AND cl.Receiver = ?)
    )
WHERE ppl.Patient_ID = ?
GROUP BY ppl.Professional_ID
ORDER BY Last_Message_Time DESC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $currentUser, $currentUser, $currentUser);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<title>Messages – Contact List</title>

</head>
<body class="bg-[#E9F0E9] min-h-screen flex flex-col font-sans">

    <div class="flex-shrink-0">
        <?php include '../../includes/navbar.php'; ?>
    </div>

    <div class="flex-grow w-full max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        <h2 class="text-2xl md:text-3xl font-bold text-[#005949] mb-6">Messages</h2>

        <div class="space-y-4">

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>

                    <?php
                        $prof_id = $row['Prof_User_ID'];
                        $name    = $row['Name'];
                        
                        
                        $db_image_path = $row['User_Image'];
                        $default_image = 'https://via.placeholder.com/150';
                        $profile_image = $default_image;

                        if (!empty($db_image_path)) {
                            
                            if (substr($db_image_path, 0, 1) === '/') {
                                $profile_image = '../..' . $db_image_path;
                            } else {
                                $profile_image = '../../' . $db_image_path;
                            }
                        }
                        
                        
                        $final_image_src = htmlspecialchars($profile_image) . "?" . time();

                        
                        $preview = $row['Last_Message'] 
                                    ? substr($row['Last_Message'], 0, 60) . (strlen($row['Last_Message']) > 60 ? "..." : "")
                                    : "Start a conversation...";
                        
                        $timestamp = $row['Last_Message_Time']
                                    ? date("M d, H:i", strtotime($row['Last_Message_Time']))
                                    : "";
                        
                        
                        $textColor = $row['Last_Message'] ? "text-gray-600" : "text-gray-400 italic";
                    ?>

                    <div onclick="openChat(<?php echo $prof_id; ?>)" 
                         class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer flex items-center border border-gray-100">
                        
                        <div class="flex-shrink-0 mr-4">
                            <img class="h-12 w-12 rounded-full object-cover border-2 border-[#005949]" 
                                 src="<?php echo $final_image_src; ?>" 
                                 alt="<?php echo htmlspecialchars($name); ?>">
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline mb-1">
                                <h3 class="text-lg font-semibold text-gray-900 truncate pr-2">
                                    <?php echo htmlspecialchars($name); ?>
                                </h3>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    <?php echo htmlspecialchars($timestamp); ?>
                                </span>
                            </div>
                            <p class="text-sm <?php echo $textColor; ?> truncate">
                                <?php echo htmlspecialchars($preview); ?>
                            </p>
                        </div>

                        <div class="ml-2 text-gray-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                    <?php endwhile; ?>
            <?php else: ?>
                
                <div class="text-center py-10 bg-white rounded-xl shadow-sm">
                    <p class="text-gray-500 text-lg">No professionals assigned yet.</p>
                    <p class="text-gray-400 text-sm mt-1">Contact your administrator to get started.</p>
                </div>

            <?php endif; ?>

        </div>
    </div>

<script>
function openChat(profUserId) {
    window.location.href = "chat.php?professional_user_id=" + profUserId;
}
</script>

</body>
</html>