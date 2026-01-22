<?php
// check_medication_reminders.php
// Run this script periodically (e.g., via cron) to send medication reminders as notifications

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../pages/common/notifications.php';

date_default_timezone_set('UTC'); // Adjust if needed

// 1. Find all due medications (not taken, time passed)
$sql = "SELECT Track_Medication_ID, Patient_ID, Medication_Name, Medication_Time FROM track_medication WHERE Medication_Status = 0 AND Medication_Time <= NOW()";
$res = mysqli_query($conn, $sql);
if (!$res) {
    echo "DB error: " . mysqli_error($conn);
    exit(1);
}


while ($row = mysqli_fetch_assoc($res)) {
    $medId = $row['Track_Medication_ID'];
    $userId = $row['Patient_ID'];
    $medName = $row['Medication_Name'];
    $medTime = $row['Medication_Time'];

    echo "Checking medication: ID=$medId, User=$userId, Name=$medName, Time=$medTime\n";

    // 2. Check if notification already sent for this medication/time
    $notifCheck = mysqli_prepare($conn, "SELECT 1 FROM notifications WHERE User_ID = ? AND Type = 'medication' AND Title = ? AND Message = ? LIMIT 1");
    $title = 'Medication Reminder';
    $msg = "It's time to take your medication: $medName (scheduled at $medTime)";
    mysqli_stmt_bind_param($notifCheck, 'iss', $userId, $title, $msg);
    mysqli_stmt_execute($notifCheck);
    mysqli_stmt_store_result($notifCheck);
    $alreadySent = mysqli_stmt_num_rows($notifCheck) > 0;
    mysqli_stmt_close($notifCheck);

    if (!$alreadySent) {
        echo "Sending notification for medication ID $medId to user $userId.\n";
        saveNotificationToDatabase($conn, $userId, $title, $msg, 'medication');
    } else {
        echo "Notification already sent for medication ID $medId to user $userId.\n";
    }
}

echo "Medication reminders checked and notifications sent if needed.\n";
