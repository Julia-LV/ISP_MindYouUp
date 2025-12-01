<?php
session_start();
include('../../config.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

$currentUserId = $_SESSION['user_id'];

if (!isset($_GET['professional_user_id']) || !is_numeric($_GET['professional_user_id'])) {
    die("Invalid professional ID.");
}

$professionalUserId = intval($_GET['professional_user_id']);

$sqlLink = "SELECT 1 FROM patient_professional_link WHERE Patient_ID = ? AND Professional_ID = ?";
$stmt = $conn->prepare($sqlLink);
$stmt->bind_param("ii", $currentUserId, $professionalUserId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) die("This professional is not assigned to you.");
$stmt->close();

$sqlName = "SELECT First_Name, Last_Name FROM user_profile WHERE User_ID = ?";
$stmt = $conn->prepare($sqlName);
$stmt->bind_param("i", $professionalUserId);
$stmt->execute();
$stmt->bind_result($firstName, $lastName);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message']))) {
    $msg = trim($_POST['message']);
    $sqlSend = "INSERT INTO chat_log (Sender, Receiver, Chat_Text, Chat_Time) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sqlSend);
    $stmt->bind_param("iis", $currentUserId, $professionalUserId, $msg);
    $stmt->execute();
    $stmt->close();

    header("Location: chat.php?professional_user_id=$professionalUserId");
    exit;
}

$sqlChat = "SELECT Sender, Receiver, Chat_Text, Chat_Time 
            FROM chat_log 
            WHERE (Sender = ? AND Receiver = ?) OR (Sender = ? AND Receiver = ?) 
            ORDER BY Chat_Time ASC";
$stmt = $conn->prepare($sqlChat);
$stmt->bind_param("iiii", $currentUserId, $professionalUserId, $professionalUserId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Chat with <?= htmlspecialchars($firstName . ' ' . $lastName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<style>
body.with-sidebar { display: block; margin: 0; font-family: Arial, sans-serif; background: #f4f6f9; }
.chat-container { max-width: 80%; margin: 100px auto 30px; background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; flex-direction: column; gap: 20px; }
.chat-box { flex: 1; max-height: 600px; overflow-y: auto; padding: 15px; border-radius: 12px; background: #fafafa; display: flex; flex-direction: column; gap: 10px; }
.message { padding: 10px 15px; border-radius: 12px; max-width: 70%; word-wrap: break-word; }
.message.patient { background-color: #005949; color: white; align-self: flex-end; }
.message.professional { background-color: #f26647; color: white; align-self: flex-start; }
.message-time { font-size: 11px; color: #fff; margin-top: 2px; opacity: 0.8; }
form { display: flex; gap: 10px; }
input[type="text"] { flex: 1; padding: 10px; border-radius: 10px; border: 1px solid #ccc; }
button { padding: 10px 20px; border: none; border-radius: 10px; background: #005949; color: white; cursor: pointer; }
button:hover { background: #004437; }
</style>
</head>
<body class="with-sidebar">

<?php include '../../includes/navbar.php'; ?>
<?php include '../../components/header_component.php'; ?>

<div class="chat-container">
    <h2 class="text-2xl font-bold text-[#005949]">Chat with <?= htmlspecialchars($firstName . ' ' . $lastName) ?></h2>

    <div class="chat-box" id="chatBox">
        <?php foreach ($messages as $msg): ?>
            <?php
            $senderId = $msg['Sender'];
            $text = $msg['Chat_Text'];
            $time = $msg['Chat_Time'];
            include '../../components/message_card_chat.php';
            ?>
        <?php endforeach; ?>
    </div>

    <form method="POST">
        <input type="text" name="message" placeholder="Type your message..." required>
        <button type="submit">Send</button>
    </form>
</div>

<script>
const chatBox = document.getElementById('chatBox');
chatBox.scrollTop = chatBox.scrollHeight;
</script>
</body>
</html>
