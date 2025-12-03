<?php
/*
 * chat.php
 * Assembler Page: Combines Layout + Chat Component
 */
session_start();

// 1. Config & Security
$config_path = '../../config.php';
if (file_exists($config_path)) { require_once $config_path; } else { include('../../config.php'); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Validate Link ID
if (!isset($_GET['link_id'])) {
    header("Location: my_professionals.php");
    exit;
}
$link_id = intval($_GET['link_id']);
$user_id = $_SESSION['user_id'];

// 3. Get Doctor Details (for the page title)
if (!$conn) {
    die("Error: Database connection failed.");
}

$sql = "SELECT link.Link_ID, u.First_Name, u.Last_Name 
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
$doc_name = "Dr. " . $row['First_Name'] . " " . $row['Last_Name'];

// 4. Load Layout
include('../../components/header_component.php');
?>

<div class="flex h-screen bg-[#E9F0E9] font-sans overflow-hidden">
    <!-- Sidebar -->
    <?php include('../../includes/navbar.php'); ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full relative">
        <!-- Pass variables to the component -->
        <?php 
            // The component expects these variables:
            $chat_link_id = $link_id; 
            $chat_user_type = 'Patient'; 
            $chat_theme_color = 'emerald'; 
            
            // This assumes chat_box.php is in the components folder
            include('../../components/chat_box.php'); 
        ?>
    </main>
</div>