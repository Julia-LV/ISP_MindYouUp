<?php
/*
 * pages/patient/chat.php
 */
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['link_id'])) {
    header("Location: my_professionals.php");
    exit;
}

$link_id = intval($_GET['link_id']);
$user_id = $_SESSION['user_id'];

// Fetch Professional details including image
$sql = "SELECT link.Link_ID, link.Professional_ID, u.First_Name, u.Last_Name, u.User_Image 
        FROM patient_professional_link link
        JOIN user_profile u ON link.Professional_ID = u.User_ID
        WHERE link.Link_ID = ? AND link.Patient_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $link_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Error: Connection not found.");
}

$row = $res->fetch_assoc();
$target_id = $row['Professional_ID'];
$target_name = "Dr. " . $row['First_Name'] . " " . $row['Last_Name'];
$chat_target_image = $row['User_Image'];

include('../../components/header_component.php');
?>

<div class="flex h-screen bg-[#E9F0E9] font-sans overflow-hidden">
    <?php include('../../includes/navbar.php'); ?>

    <main class="flex-1 flex flex-col h-full relative">
        <?php 
            $chat_link_id = $link_id; 
            $chat_user_type = 'Patient'; 
            $chat_theme_color = 'emerald'; 
            $chat_my_id = $user_id;
            $chat_target_id = $target_id;
            $chat_target_name = $target_name;
            $chat_target_image = $chat_target_image;
            
            include('../../components/chat_box.php'); 
        ?>
    </main>
</div>