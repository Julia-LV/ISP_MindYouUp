<<<<<<< Updated upstream

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
=======
<?php

session_start();
require_once 'C:/Users/rodri/OneDrive/Documents/GitHub/ISP_MindYouUp/config.php'; // adjust path if needed
>>>>>>> Stashed changes

// If you store logged-in user id in session use it to show only their reminders
$userId = $_SESSION['user_id'] ?? null;

$notifications = [];

if ($conn) {
    if ($userId) {
        $stmt = mysqli_prepare($conn, "SELECT Reminder_ID, Reminder_Text, Reminder_Time FROM reminder WHERE User_ID = ? ORDER BY Reminder_Time DESC");
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
    } else {
        $res = mysqli_query($conn, "SELECT Reminder_ID, User_ID, Reminder_Text, Reminder_Time FROM reminder ORDER BY Reminder_Time DESC");
    }

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $notifications[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <style>
<<<<<<< Updated upstream
        /* Brand colors */
        :root {
            --bg-creme: #FFF7E1; /* RGB: 255,247,225 */
            --accent-orange: #F26647; /* RGB: 242,102,71 */
            --accent-green:  #005949; /* RGB: 0,89,73 */
            --text-dark: #0b2a24;
            --muted: rgba(11,42,36,0.6);
            --radius: 10px;
        }

        body { font-family: Arial, sans-serif; background: var(--bg-creme); margin: 0; padding: 0; color: var(--text-dark); }
        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 20px; border-radius: var(--radius); box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
        h1 { text-align: center; color: var(--accent-green); margin-top: 0; }
        .notification { border-bottom: 1px solid #eee; padding: 16px 0; }
        .notification:last-child { border-bottom: none; }
        .meta { color: var(--muted); font-size: 0.9em; margin-bottom: 6px; }
        .message { margin-top: 8px; white-space: pre-wrap; }

        /* Back link styled as button using orange accent */
        .back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 16px 0;
            color: white;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            background: linear-gradient(180deg, var(--accent-orange), #e6553e);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .back:hover { box-shadow: 0 6px 16px rgba(0,0,0,0.12); }

        @media (max-width: 600px) {
            .container { margin: 20px; padding: 16px; }
        }
=======
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; }
        h1 { text-align: center; }
        .notification { border-bottom: 1px solid #eee; padding: 16px 0; }
        .notification:last-child { border-bottom: none; }
        .meta { color: #888; font-size: 0.9em; margin-bottom: 6px; }
        .message { margin-top: 8px; white-space: pre-wrap; }
        .back { display:block; margin:12px 0; color:#333; text-decoration:none; }
>>>>>>> Stashed changes
    </style>
</head>
<body>
    <div class="container">
        <a class="back" href="settings.php">&larr; Back to Settings</a>
        <h1>Notifications</h1>

        <?php if (empty($notifications)): ?>
            <p>No notifications to display.</p>
        <?php else: ?>
            <?php foreach ($notifications as $note): ?>
                <div class="notification">
                    <div class="meta">
                        <?php if (isset($note['User_ID'])): ?>
                            User: <?= htmlspecialchars($note['User_ID']) ?> &nbsp;|&nbsp;
                        <?php endif; ?>
                        <?= htmlspecialchars(date('Y-m-d H:i', strtotime($note['Reminder_Time'] ?? $note['date'] ?? 'now'))) ?>
                    </div>
                    <div class="message"><?= nl2br(htmlspecialchars($note['Reminder_Text'] ?? $note['message'] ?? '')) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
<<<<<<< Updated upstream
</html>
=======
</html>
>>>>>>> Stashed changes
