<?php
session_start();
include('../../config.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- ACCESS CONTROL --- //
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$professionalUserID = $_SESSION['user_id']; // Professional User_ID

// --- VERIFY PATIENT USER_ID IN URL --- //
if (!isset($_GET['patient_user_id']) || !is_numeric($_GET['patient_user_id'])) {
    die("Invalid patient ID.");
}

$patientUserID = intval($_GET['patient_user_id']); 

// --- VERIFY THIS PROFESSIONAL IS LINKED TO THIS PATIENT --- //
$sqlCheck = "
    SELECT 1 
    FROM patient_professional_link
    WHERE Patient_ID = ? AND Professional_ID = ?
";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $patientUserID, $professionalUserID);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();

if ($resCheck->num_rows === 0) {
    die("You are not assigned to this patient.");
}

// --- SEND MESSAGE --- //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);

    if ($msg !== "") {
        $sqlSend = "
            INSERT INTO chat_log (Sender, Receiver, Chat_Text, Chat_Time)
            VALUES (?, ?, ?, NOW())
        ";
        $stmtSend = $conn->prepare($sqlSend);
        $stmtSend->bind_param("iis", $professionalUserID, $patientUserID, $msg);
        $stmtSend->execute();
    }

    // Redirect to prevent form resubmission
    header("Location: chat.php?patient_user_id=" . $patientUserID);
    exit;
}

// --- GET MESSAGES --- //
$sqlChat = "
SELECT 
    Sender,
    Chat_Text,
    Chat_Time
FROM chat_log
WHERE (Sender = ? AND Receiver = ?)
    OR (Sender = ? AND Receiver = ?)
ORDER BY Chat_Time ASC
";
$stmtChat = $conn->prepare($sqlChat);
$stmtChat->bind_param("iiii", $professionalUserID, $patientUserID, $patientUserID, $professionalUserID);
$stmtChat->execute();
$messages = $stmtChat->get_result();

// --- GET PATIENT NAME --- //
$sqlName = "SELECT First_Name, Last_Name FROM user_profile WHERE User_ID = ?";
$stmtName = $conn->prepare($sqlName);
$stmtName->bind_param("i", $patientUserID);
$stmtName->execute();
$resName = $stmtName->get_result()->fetch_assoc();
$patientName = $resName['First_Name'] . " " . $resName['Last_Name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat with <?= htmlspecialchars($patientName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<style>
    /* Custom Scrollbar for better look */
    .chat-box::-webkit-scrollbar {
        width: 6px;
    }
    .chat-box::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .chat-box::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 3px;
    }
    .chat-box::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }

    /* Message Bubbles Colors */
    .message.sent { 
        background-color: #005949; /* Green (Professional) */
        color: white; 
        align-self: flex-end; 
        border-bottom-right-radius: 2px;
    }
    .message.received { 
        background-color: #f26647; /* Orange (Patient) */
        color: white; 
        align-self: flex-start; 
        border-bottom-left-radius: 2px;
    }
</style>
</head>
<body class="bg-[#f4f6f9] h-screen flex flex-col font-sans overflow-hidden">

    <div class="flex-shrink-0">
        <?php include '../../includes/navbar.php'; ?>
    </div>

    <div class="flex-1 flex flex-col w-full md:w-4/5 mx-auto bg-white shadow-lg md:my-6 md:rounded-xl overflow-hidden border border-gray-200">
        
        <div class="p-4 border-b border-gray-200 bg-white flex-shrink-0">
            <h2 class="text-xl md:text-2xl font-bold text-[#005949] flex items-center"> 
                Chat with <?= htmlspecialchars($patientName) ?>
            </h2>
        </div>

        <div class="chat-box flex-1 overflow-y-auto p-4 bg-[#fafafa] flex flex-col gap-3" id="chatBox">
            <?php if ($messages->num_rows > 0): ?>
                <?php while ($row = $messages->fetch_assoc()): ?>
                    <?php
                    // Logic: If Sender is ME (Professional), class is 'sent'. Else 'received'.
                    $messageClass = ($row['Sender'] == $professionalUserID) ? 'sent' : 'received';
                    $alignment = ($row['Sender'] == $professionalUserID) ? 'items-end' : 'items-start';
                    ?>
                    
                    <div class="flex flex-col <?= $alignment ?> w-full">
                        <div class="message <?= $messageClass ?> px-4 py-2.5 rounded-xl max-w-[85%] md:max-w-[70%] shadow-sm text-sm md:text-base break-words">
                            <?= nl2br(htmlspecialchars($row['Chat_Text'])) ?>
                        </div>
                        <span class="text-[10px] md:text-xs text-gray-400 mt-1 mx-1">
                            <?= date("M d H:i", strtotime($row['Chat_Time'])) ?>
                        </span>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">
                    <p>No messages yet. Say hello!</p>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" class="p-3 md:p-4 bg-white border-t border-gray-200 flex gap-2 md:gap-3 flex-shrink-0">
            <input type="text" name="message" placeholder="Type your message..." required autocomplete="off"
                   class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#005949] focus:border-transparent transition-all">
            
            <button type="submit" 
                    class="bg-[#005949] hover:bg-[#004437] text-white px-5 md:px-8 py-3 rounded-lg font-medium transition-colors shadow-md flex items-center justify-center">
                Send
            </button>
        </form>

    </div>

<script>
    // Auto-scroll to bottom logic
    const chatBox = document.getElementById('chatBox');
    
    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Scroll immediately
    scrollToBottom();

    // Optional: Ensure scroll stays at bottom if images/content loads later (not applicable here strictly for text but good practice)
    window.addEventListener('load', scrollToBottom);
</script>

</body>
</html>