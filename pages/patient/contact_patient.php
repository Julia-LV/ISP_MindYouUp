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

<!-- Tailwind CSS -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<title>Messages – Contact List</title>

</head>
<body class="bg-[#E9F0E9]">

<!-- NAVBAR -->
<?php include '../../includes/navbar.php'; ?>

<!-- MAIN CONTENT WRAPPER -->
<div class="w-full max-w-5xl mx-auto pt-6 px-4">

    <h2 class="text-3xl font-bold text-[#005949] mb-6">Messages</h2>

    <div class="space-y-4">

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>

                <?php
                    // Prepare variables for the message card component
                    $prof_id   = $row['Prof_User_ID'];
                    $name      = $row['Name'];
                    $preview   = $row['Last_Message'] 
                                ? substr($row['Last_Message'], 0, 60) . "..."
                                : "No messages yet";

                    $timestamp = $row['Last_Message_Time']
                                ? date("M d", strtotime($row['Last_Message_Time']))
                                : "";
                ?>

                <?php include '../../components/message_card.php'; ?>

            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-700">No assigned professionals yet.</p>
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
