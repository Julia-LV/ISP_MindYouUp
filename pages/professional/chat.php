<?php
/*
 * pages/professional/chat.php
 */
session_start();
include('../../config.php');

// 1. Security Check: Must be a Professional
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. GET THE LINK_ID (This matches your new URL)
if (!isset($_GET['link_id'])) {
    header("Location: my_patients.php");
    exit;
}

$link_id = intval($_GET['link_id']);
$user_id = $_SESSION['user_id']; // This is the Professional's ID

// 3. Verify the link and get Patient Details for the header
// We join with user_profile on Patient_ID to get the patient's name
$sql = "SELECT link.Link_ID, u.User_ID, u.First_Name, u.Last_Name, u.User_Image 
        FROM patient_professional_link link
        JOIN user_profile u ON link.Patient_ID = u.User_ID
        WHERE link.Link_ID = ? AND link.Professional_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $link_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Error: Connection not found or you do not have permission to view this chat.");
}

$row = $res->fetch_assoc();
$patient_id = $row['User_ID'];
$patient_name = $row['First_Name'] . " " . $row['Last_Name'];
$patient_image = $row['User_Image'];

// 4. Load Layout
include('../../components/header_component.php');
?>

<div class="flex h-screen bg-[#E9F0E9] font-sans overflow-hidden">
    <?php include('../../includes/navbar.php'); ?>

    <main class="flex-1 flex flex-col h-full relative">
        <div class="p-4 bg-white border-b flex justify-between items-center shadow-sm">
            <h2 class="font-bold text-gray-700">Chatting with: <?= htmlspecialchars($patient_name) ?></h2>
        </div>

        <?php
        // The chat_box.php component uses these variables
        $chat_link_id = $link_id;
        $chat_user_type = 'Professional'; // CRITICAL: Tell the component you are the Professional
        $chat_theme_color = 'orange';    // Professional theme color
        $chat_my_id = $user_id;
        $chat_target_id = $patient_id;
        $chat_target_name = $patient_name;
        $chat_target_image = $patient_image;

        include('../../components/chat_box.php');
        ?>
    </main>
</div>