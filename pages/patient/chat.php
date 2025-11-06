<?php
// pages/patient/chat.php
session_start();
include('../../config.php');

// Ensure user is logged in as Patient
if (!isset($_SESSION['User_ID']) || $_SESSION['Role'] !== 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

$currentUserId = $_SESSION['User_ID'];

// Map session User_ID to Patient_ID
$stmt = $conn->prepare("SELECT Patient_ID FROM patient_profile WHERE User_ID = ?");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$stmt->bind_result($currentPatientId);
if (!$stmt->fetch()) {
    die("Patient profile not found.");
}
$stmt->close();

// Get professional ID from URL
if (!isset($_GET['professional_id']) || !is_numeric($_GET['professional_id'])) {
    die("Invalid professional ID.");
}
$professionalId = intval($_GET['professional_id']);

// Validate that this professional is assigned to this patient
$stmt = $conn->prepare("SELECT User_ID FROM professional_profile WHERE Professional_ID = ?");
$stmt->bind_param("i", $professionalId);
$stmt->execute();
$stmt->bind_result($professionalUserId);
if (!$stmt->fetch()) {
    die("Professional not found.");
}
$stmt->close();

// Check the link exists
$stmt = $conn->prepare("SELECT 1 FROM patient_professional_link WHERE Patient_ID = ? AND Professional_ID = ?");
$stmt->bind_param("ii", $currentPatientId, $professionalId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    die("This professional is not assigned to you.");
}
$stmt->close();

// Fetch professional name
$stmt = $conn->prepare("SELECT First_Name, Last_Name FROM user_profile WHERE User_ID = ?");
$stmt->bind_param("i", $professionalUserId);
$stmt->execute();
$stmt->bind_result($firstName, $lastName);
$stmt->fetch();
$stmt->close();

// Handle sending messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message']))) {
    $messageText = trim($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO chat_log (Sender, Receiver, Chat_Text, Chat_Time) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $currentUserId, $professionalUserId, $messageText);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid resubmission
    header("Location: chat.php?professional_id=$professionalId");
    exit;
}

// Fetch previous messages between patient and professional
$sql = "
SELECT c.Sender, c.Receiver, c.Chat_Text, c.Chat_Time
FROM chat_log c
WHERE (c.Sender = ? AND c.Receiver = ?) OR (c.Sender = ? AND c.Receiver = ?)
ORDER BY c.Chat_Time ASC
";
$stmt = $conn->prepare($sql);
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
<style>
body { font-family: Arial, sans-serif; background: #f4f6f9; margin:0; }
.container { max-width: 700px; margin: 30px auto; background: white; border-radius: 12px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.chat-box { height: 400px; overflow-y: auto; padding: 10px; border: 1px solid #ccc; border-radius: 10px; background: #fafafa; margin-bottom: 15px; display: flex; flex-direction: column; gap: 5px; }
.message { margin-bottom: 10px; padding: 8px 12px; border-radius: 12px; max-width: 70%; display:inline-block; }
.message.patient { background-color: #4CAF50; color: white; align-self: flex-end; }
.message.professional { background-color: #ddd; color: black; align-self: flex-start; }
.message-time { font-size: 11px; color: #555; margin-top: 2px; }
form { display:flex; gap:10px; }
input[type="text"] { flex:1; padding:10px; border-radius: 10px; border:1px solid #ccc; }
button { padding:10px 20px; border:none; border-radius:10px; background:#4CAF50; color:white; cursor:pointer; }
button:hover { background:#45a049; }
</style>
</head>
<body>
<div class="container">
    <h2>Chat with <?= htmlspecialchars($firstName . ' ' . $lastName) ?></h2>

    <div class="chat-box" id="chatBox">
        <?php foreach ($messages as $msg): ?>
            <?php 
            $isPatient = ($msg['Sender'] == $currentUserId);
            $class = $isPatient ? 'patient' : 'professional';
            ?>
            <div class="message <?= $class ?>">
                <?= htmlspecialchars($msg['Chat_Text']) ?>
                <div class="message-time"><?= date("M d, H:i", strtotime($msg['Chat_Time'])) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="POST">
        <input type="text" name="message" placeholder="Type your message..." required>
        <button type="submit">Send</button>
    </form>
</div>

<script>
// Scroll chat to bottom on page load
const chatBox = document.getElementById('chatBox');
chatBox.scrollTop = chatBox.scrollHeight;
</script>
</body>
</html>
