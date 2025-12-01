<?php
session_start();
require_once __DIR__ . '/../../config.php'; // adjust path if needed

/*
 // USER INFO (COMMENTED OUT)
 // Example: fetch current user's profile from DB using session user_id.
 // All lines are commented to avoid executing anything; remove leading // to enable.
 // $current_user_id = $_SESSION['user_id'] ?? null;
 // $CURRENT_USER = null;
 // if ($current_user_id) {
 //     $sql = "SELECT User_ID, First_Name, Last_Name, `E-mail`, `Role` FROM user_profile WHERE User_ID = ? LIMIT 1";
 //     if ($s = $conn->prepare($sql)) {
 //         $s->bind_param('i', $current_user_id);
 //         $s->execute();
 //         $s->bind_result($uid,$fname,$lname,$uemail,$urole);
 //         if ($s->fetch()) {
 //             $CURRENT_USER = ['id'=>$uid,'first'=>$fname,'last'=>$lname,'email'=>$uemail,'role'=>$urole];
 //         }
 //         $s->close();
 //     }
 // }
 */

// If you store logged-in user id in session use it to show only their medication entries
$userId = $_SESSION['user_id'] ?? null;

$notifications = [];

// Prefer the medication tracking table defined in the project's SQL schema.
$foundTable = null;
if ($conn) {
    $show = @mysqli_query($conn, "SHOW TABLES LIKE 'track_medication'");
    if ($show && mysqli_num_rows($show) > 0) {
        $foundTable = 'track_medication';
    }
}

if ($conn && $foundTable) {
    // Use the column names from your SQL dump: Patient_ID, Medication_Name, Medication_Time, Medication_Status
    if ($userId) {
        $stmt = mysqli_prepare($conn, "SELECT `Track_Medication_ID`, `Patient_ID`, `Medication_Name`, `Medication_Time`, `Medication_Status` FROM `track_medication` WHERE `Patient_ID` = ? ORDER BY `Medication_Time` DESC");
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
    } else {
        $res = mysqli_query($conn, "SELECT `Track_Medication_ID`, `Patient_ID`, `Medication_Name`, `Medication_Time`, `Medication_Status` FROM `track_medication` ORDER BY `Medication_Time` DESC");
    }

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $notifications[] = $row;
        }
    }
} else {
    // No medication-tracking table found; leave $notifications empty so the UI shows a friendly message.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <!-- Tailwind (needed for navbar utility classes) -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Page styles -->
    <link rel="stylesheet" href="../../CSS/notifications.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-wrap">
        <div class="notifications-panel">
        <a class="back" href="settings.php">&larr; Back to Settings</a>
        <h1>Notifications</h1>

        <?php if (empty($notifications)): ?>
            <p>No notifications to display.</p>
        <?php else: ?>
            <?php foreach ($notifications as $note): ?>
                <div class="notification">
                    <div class="meta">
                        <?php if (isset($note['Patient_ID'])): ?>
                            Patient: <?= htmlspecialchars($note['Patient_ID']) ?> &nbsp;|&nbsp;
                        <?php endif; ?>
                        <?php
                            $medTime = $note['Medication_Time'] ?? ($note['MEDICATION_TIME'] ?? ($note['date'] ?? null));
                            $medName = $note['Medication_Name'] ?? ($note['MEDICATION_NAME'] ?? null);
                            $medStatus = $note['Medication_Status'] ?? ($note['MEDICATION_STATUS'] ?? null);
                            if ($medTime) {
                                echo htmlspecialchars(date('Y-m-d H:i', strtotime($medTime)));
                            } else {
                                echo htmlspecialchars(date('Y-m-d H:i', time()));
                            }
                            if ($medName) { echo ' &nbsp;|&nbsp; ' . htmlspecialchars($medName); }
                            if ($medStatus !== null && $medStatus !== '') { echo ' &nbsp;|&nbsp; Status: ' . htmlspecialchars($medStatus); }
                        ?>
                    </div>
                    <div class="message"><?= nl2br(htmlspecialchars($note['Medication_Name'] ?? $note['MEDICATION_NAME'] ?? '')) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
