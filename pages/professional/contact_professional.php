<?php
// pages/professional/contact_professional.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../../config.php');

// --- ACCESS CONTROL --- //
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$currentProfessional = $_SESSION['user_id']; // Professional User_ID

// --- FETCH LINKED PATIENTS AND LAST MESSAGE ---
$sql = "
SELECT 
    u.User_ID AS Patient_User_ID,
    CONCAT(u.First_Name, ' ', u.Last_Name) AS Name,
    lm.Last_Message,
    lm.Last_Message_Time
FROM patient_professional_link ppl
JOIN user_profile u ON ppl.Patient_ID = u.User_ID
LEFT JOIN (
    SELECT 
        CASE WHEN Sender = ? THEN Receiver ELSE Sender END AS Patient_ID,
        Chat_Text AS Last_Message,
        Chat_Time AS Last_Message_Time
    FROM chat_log
    WHERE Sender = ? OR Receiver = ?
    ORDER BY Chat_Time DESC
) lm ON lm.Patient_ID = u.User_ID
WHERE ppl.Professional_ID = ?
GROUP BY u.User_ID
ORDER BY lm.Last_Message_Time DESC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $currentProfessional, $currentProfessional, $currentProfessional, $currentProfessional);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<!-- Tailwind CSS -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<title>Patients – Messages</title>

</head>
<body class="bg-[#E9F0E9]">

<!-- NAVBAR -->
<?php include '../../includes/navbar.php'; ?>

<!-- MAIN CONTENT WRAPPER -->
<div class="w-full max-w-5xl mx-auto pt-6 px-4">

    <h2 class="text-3xl font-bold text-[#005949] mb-6">Your Patients</h2>

    <div class="space-y-4">

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>

                <?php
                    // Prepare variables for message_card.php
                    $prof_id   = $row['Patient_User_ID'];
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
            <p class="text-gray-700">No linked patients yet.</p>
        <?php endif; ?>

    </div>
</div>

<script>
function openChat(patientUserID) {
    window.location.href = "chat.php?patient_user_id=" + patientUserID;
}
</script>

</body>
</html>
