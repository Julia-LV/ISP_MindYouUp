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
$professionalIsSender = $professionalUserID; 

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

    // Redireciona para evitar re-submissão
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
<title>Chat with <?= htmlspecialchars($patientName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<style>
/* Estilos garantindo a altura fixa e a barra de rolagem */
body { 
    display: block; 
    margin: 0; 
    font-family: Arial, sans-serif; 
    background: #f4f6f9; 
}
.chat-container { 
    /* CORREÇÃO APLICADA: max-width para 80% */
    max-width: 80%; 
    margin: 50px auto 30px; 
    background: white; 
    border-radius: 15px; 
    padding: 20px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
    display: flex; 
    flex-direction: column; 
    gap: 20px; 
}
.chat-box { 
    flex: 1; 
    max-height: 70vh; 
    overflow-y: auto; 
    padding: 15px; 
    border-radius: 12px; 
    background: #fafafa; 
    display: flex; 
    flex-direction: column; 
    gap: 10px; 
}
.message { 
    padding: 10px 15px; 
    border-radius: 12px; 
    max-width: 70%; 
    word-wrap: break-word; 
}
.message.sent { 
    background-color: #005949; 
    color: white; 
    align-self: flex-end; 
}
.message.received { 
    background-color: #f26647; 
    color: white; 
    align-self: flex-start; 
}
.message-time { 
    font-size: 11px; 
    color: #fff; 
    margin-top: 2px; 
    opacity: 0.8; 
    text-align: right; 
}
form { 
    display: flex; 
    gap: 10px; 
}
input[type="text"] { 
    flex: 1; 
    padding: 10px; 
    border-radius: 10px; 
    border: 1px solid #ccc; 
}
button { 
    padding: 10px 20px; 
    border: none; 
    border-radius: 10px; 
    background: #005949; 
    color: white; 
    cursor: pointer; 
}
button:hover { background: #004437; }
</style>
</head>
<body>
<?php include '../../includes/navbar.php'; ?>
<div class="chat-container">
    <h2 class="text-2xl font-bold text-[#005949]">Chat with <?= htmlspecialchars($patientName) ?></h2>

    <div class="chat-box" id="chatBox">
        <?php while ($row = $messages->fetch_assoc()): ?>
            <?php
            // Se o remetente for o profissional atual (o utilizador), a classe é 'sent'. Caso contrário, é 'received'.
            $messageClass = ($row['Sender'] == $professionalUserID) ? 'sent' : 'received';
            ?>
            <div class="message <?= $messageClass ?>">
                <?= nl2br(htmlspecialchars($row['Chat_Text'])) ?>
                <div class="message-time"><?= date("M d H:i", strtotime($row['Chat_Time'])) ?></div>
            </div>
        <?php endwhile; ?>
    </div>

    <form method="POST">
        <input type="text" name="message" placeholder="Type your message..." required>
        <button type="submit">Send</button>
    </form>
</div>

<script>
// Scroll automático para o final da caixa de mensagens
const chatBox = document.getElementById('chatBox');
chatBox.scrollTop = chatBox.scrollHeight;
</script>

</body>
</html>