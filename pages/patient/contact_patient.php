<?php
// pages/patient/contact_patient.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../../config.php');

// Ensure user is logged in and is a patient
if (!isset($_SESSION['User_ID']) || $_SESSION['Role'] !== 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

$currentUser = $_SESSION['User_ID'];

// Fetch assigned professionals for this patient with most recent chat
$sql = "
SELECT 
    p.Professional_ID,
    p.User_ID AS Professional_UserID,
    CONCAT(u.First_Name, ' ', u.Last_Name) AS Name,
    MAX(cl.Chat_Time) AS Last_Message_Time,
    SUBSTRING_INDEX(GROUP_CONCAT(cl.Chat_Text ORDER BY cl.Chat_Time DESC SEPARATOR '||'), '||', 1) AS Last_Message
FROM patient_professional_link ppl
JOIN professional_profile p ON ppl.Professional_ID = p.Professional_ID
JOIN user_profile u ON p.User_ID = u.User_ID
LEFT JOIN chat_log cl ON 
    (cl.Sender = ? AND cl.Receiver = u.User_ID)
    OR 
    (cl.Sender = u.User_ID AND cl.Receiver = ?)
WHERE ppl.Patient_ID = (
    SELECT Patient_ID FROM patient_profile WHERE User_ID = ?
)
GROUP BY p.Professional_ID
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
<title>Messages – Contact List</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
}
.container {
    max-width: 600px;
    margin: 30px auto;
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.contact {
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.contact:hover {
    background: #f0f0f0;
}
.name {
    font-weight: bold;
    font-size: 16px;
}
.preview {
    font-size: 13px;
    color: #555;
    margin-top: 4px;
}
.timestamp {
    font-size: 12px;
    color: #888;
    white-space: nowrap;
}
</style>
</head>
<body>
<div class="container">
    <h2>Messages</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="contact" onclick="openChat(<?= htmlspecialchars($row['Professional_ID']) ?>)">
                <div>
                    <div class="name"><?= htmlspecialchars($row['Name']) ?></div>
                    <div class="preview">
                        <?= $row['Last_Message'] ? htmlspecialchars(substr($row['Last_Message'], 0, 40)) . "..." : "No messages yet" ?>
                    </div>
                </div>
                <div class="timestamp">
                    <?= $row['Last_Message_Time'] ? date("M d", strtotime($row['Last_Message_Time'])) : "" ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No assigned professionals yet.</p>
    <?php endif; ?>
</div>

<script>
function openChat(professionalId) {
    window.location.href = "chat.php?professional_id=" + professionalId;
}
</script>
</body>
</html>
