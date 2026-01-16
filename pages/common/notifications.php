<?php
require_once __DIR__ . '/../../config.php'; // adjust path if needed

$CURRENT_USER = null;
$uid = null;
if (!empty($_SESSION['user_id']) && isset($conn)) {
    $uid = (int) $_SESSION['user_id'];
    $stmtUsr = mysqli_prepare($conn, "SELECT User_ID, First_Name, Last_Name, Email, Role FROM user_profile WHERE User_ID = ? LIMIT 1");
    if ($stmtUsr) {
        mysqli_stmt_bind_param($stmtUsr, 'i', $uid);
        mysqli_stmt_execute($stmtUsr);
        $resUsr = mysqli_stmt_get_result($stmtUsr);
        if ($resUsr && $rowu = mysqli_fetch_assoc($resUsr)) {
            $CURRENT_USER = $rowu;
        }
        mysqli_stmt_close($stmtUsr);
    }
}

// ==================================================================
// NOTIFICATION HELPER FUNCTIONS (merged from send_notification.php)
// ==================================================================

/**
 * Save a notification to the database
 */
function saveNotificationToDatabase($conn, $userId, $title, $message, $type = 'system') {
    $stmt = mysqli_prepare($conn, "INSERT INTO notifications (User_ID, Title, Message, Type, Is_Read, Created_At) VALUES (?, ?, ?, ?, 0, NOW())");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'isss', $userId, $title, $message, $type);
        if (mysqli_stmt_execute($stmt)) {
            $insertId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            return $insertId;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

/**
 * Get unread notification count for a user
 */
function getUnreadNotificationCount($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM notifications WHERE User_ID = ? AND Is_Read = 0");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return (int)$row['count'];
        }
        mysqli_stmt_close($stmt);
    }
    return 0;
}

/**
 * Mark a notification as read
 */
function markNotificationAsRead($conn, $notificationId, $userId) {
    $stmt = mysqli_prepare($conn, "UPDATE notifications SET Is_Read = 1, Read_At = NOW() WHERE Notification_ID = ? AND User_ID = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $notificationId, $userId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

/**
 * Mark all notifications as read for a user
 */
function markAllNotificationsAsRead($conn, $userId) {
    $stmt = mysqli_prepare($conn, "UPDATE notifications SET Is_Read = 1, Read_At = NOW() WHERE User_ID = ? AND Is_Read = 0");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

/**
 * Get recent notifications for a user
 */
function getNotifications($conn, $userId, $limit = 20, $unreadOnly = false) {
    $sql = "SELECT * FROM notifications WHERE User_ID = ?";
    if ($unreadOnly) {
        $sql .= " AND Is_Read = 0";
    }
    $sql .= " ORDER BY Created_At DESC LIMIT ?";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $notifications = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $notifications;
    }
    return [];
}

/**
 * Delete a notification
 */
function deleteNotification($conn, $notificationId, $userId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM notifications WHERE Notification_ID = ? AND User_ID = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $notificationId, $userId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

/**
 * Delete old notifications (cleanup function)
 */
function deleteOldNotifications($conn, $daysOld = 30) {
    $result = mysqli_query($conn, "DELETE FROM notifications WHERE Created_At < DATE_SUB(NOW(), INTERVAL $daysOld DAY)");
    return mysqli_affected_rows($conn);
}

/**
 * Generate JavaScript code for triggering a push notification
 */
function generatePushNotificationJS($title, $message, $type = 'system', $icon = null) {
    $title = addslashes($title);
    $message = addslashes($message);
    $type = addslashes($type);
    $iconJS = $icon ? "'" . addslashes($icon) . "'" : "null";
    
    return "<script>
        if (typeof MYUNotifications !== 'undefined' && MYUNotifications.isSupported() && Notification.permission === 'granted') {
            MYUNotifications.showPush('$title', {
                body: '$message',
                tag: '$type',
                icon: $iconJS
            });
        }
    </script>";
}

// ============================================================
// HANDLE API REQUESTS (merged from save_notification.php)
// ============================================================

// Handle AJAX API requests for saving notifications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_action']) && $_POST['api_action'] === 'save_notification') {
    header('Content-Type: application/json');
    
    if (!$uid) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    $targetUserId = (int)($_POST['target_user_id'] ?? $uid);
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type = trim($_POST['type'] ?? 'system');
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'error' => 'Title is required']);
        exit;
    }
    
    $notifId = saveNotificationToDatabase($conn, $targetUserId, $title, $message, $type);
    
    if ($notifId) {
        echo json_encode(['success' => true, 'notification_id' => $notifId]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save notification']);
    }
    exit;
}

// ============================================================
// HANDLE FORM SUBMISSIONS
// ============================================================

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notifId = (int)$_POST['notification_id'];
    markNotificationAsRead($conn, $notifId, $uid);
}

// Handle mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    markAllNotificationsAsRead($conn, $uid);
}

// Handle delete notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    $delId = (int)$_POST['notification_id'];
    deleteNotification($conn, $delId, $uid);
}

$userId = $_SESSION['user_id'] ?? null;

$notifications = [];
$unreadCount = 0;

// Fetch notifications from the notifications table
if ($conn && $userId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM notifications WHERE User_ID = ? ORDER BY Created_At DESC LIMIT 50");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $notifications[] = $row;
            if (!$row['Is_Read']) {
                $unreadCount++;
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// Also fetch medication reminders
$medicationReminders = [];
if ($conn && $userId) {
    $medStmt = mysqli_prepare($conn, "SELECT * FROM track_medication WHERE Patient_ID = ? AND Medication_Status = 0 ORDER BY Medication_Time ASC LIMIT 10");
    if ($medStmt) {
        mysqli_stmt_bind_param($medStmt, 'i', $userId);
        mysqli_stmt_execute($medStmt);
        $medRes = mysqli_stmt_get_result($medStmt);
        while ($row = mysqli_fetch_assoc($medRes)) {
            $medicationReminders[] = $row;
        }
        mysqli_stmt_close($medStmt);
    }
}

// Helper function to get notification icon
function getNotificationIcon($type) {
    $icons = [
        'message' => '💬',
        'medication' => '💊',
        'diary' => '📔',
        'ticlog' => '📋',
        'connection' => '🤝',
        'appointment' => '📅',
        'system' => '🔔',
        'reminder' => '⏰'
    ];
    return $icons[$type] ?? '🔔';
}

// Helper function to format time ago
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Page styles -->
    <link rel="stylesheet" href="../../CSS/notifications.css?v=2">
</head>
<body>
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../../components/push_notification.php'; ?>
    
    <div class="page-wrap">
        <div class="notifications-panel" style="max-width: 800px; margin: 0 auto; padding: 20px;">
            <a class="back" href="settings.php">&larr; Back to Settings</a>
            
            <div class="section-header">
                <h1 style="display: flex; align-items: center;">
                    Notifications
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge-count"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </h1>
                <?php if (!empty($notifications)): ?>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="mark_all_read" class="btn btn-sm btn-outline-primary">
                            Mark all as read
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Push Notification Permission Banner -->
            <div id="push-permission-banner" class="push-notification-banner" style="display: none;">
                <div>
                    <strong>🔔 Enable Push Notifications</strong>
                    <p style="margin: 4px 0 0 0; opacity: 0.9; font-size: 14px;">
                        Get notified about messages, reminders, and updates even when you're not on the site.
                    </p>
                </div>
                <button onclick="enablePushNotifications()">Enable</button>
            </div>

            <!-- Tabs for filtering -->
            <div class="tabs">
                <button class="tab-btn active" onclick="filterNotifications('all')">All</button>
                <button class="tab-btn" onclick="filterNotifications('unread')">Unread</button>
                <button class="tab-btn" onclick="filterNotifications('message')">Messages</button>
                <button class="tab-btn" onclick="filterNotifications('reminder')">Reminders</button>
            </div>

            <!-- Notifications List -->
            <div id="notifications-container">
                <?php if (empty($notifications) && empty($medicationReminders)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🔔</div>
                        <h3>No notifications yet</h3>
                        <p>When you receive notifications, they'll appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $note): ?>
                        <div class="notification-card <?= !$note['Is_Read'] ? 'unread' : '' ?>" data-type="<?= htmlspecialchars($note['Type'] ?? 'system') ?>" data-read="<?= $note['Is_Read'] ? 'true' : 'false' ?>">
                            <div style="display: flex; gap: 16px;">
                                <div class="notification-icon">
                                    <?= getNotificationIcon($note['Type'] ?? 'system') ?>
                                </div>
                                <div style="flex: 1;">
                                    <div class="notification-title"><?= htmlspecialchars($note['Title']) ?></div>
                                    <div class="notification-message"><?= nl2br(htmlspecialchars($note['Message'])) ?></div>
                                    <div class="notification-time"><?= timeAgo($note['Created_At']) ?></div>
                                </div>
                                <div class="notification-actions">
                                    <?php if (!$note['Is_Read']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="notification_id" value="<?= $note['Notification_ID'] ?>">
                                            <button type="submit" name="mark_read" class="btn-mark-read" title="Mark as read">✓</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="notification_id" value="<?= $note['Notification_ID'] ?>">
                                        <button type="submit" name="delete_notification" class="btn-delete" title="Delete" onclick="return confirm('Delete this notification?')">✕</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Medication Reminders Section -->
                    <?php if (!empty($medicationReminders)): ?>
                        <h3 style="margin-top: 30px; margin-bottom: 16px; color: #374151;">
                            💊 Medication Reminders
                        </h3>
                        <?php foreach ($medicationReminders as $med): ?>
                            <div class="notification-card" data-type="medication" data-read="false">
                                <div style="display: flex; gap: 16px;">
                                    <div class="notification-icon" style="background: #fef3c7;">
                                        💊
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="notification-title"><?= htmlspecialchars($med['Medication_Name']) ?></div>
                                        <div class="notification-message">
                                            Time: <?= date('H:i', strtotime($med['Medication_Time'])) ?> - Status: <?= htmlspecialchars($med['Medication_Status']) ?>
                                        </div>
                                        <div class="notification-time"><?= date('M d, Y', strtotime($med['Medication_Time'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Check push notification permission status
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof MYUNotifications !== 'undefined' && MYUNotifications.isSupported()) {
                if (Notification.permission === 'default') {
                    document.getElementById('push-permission-banner').style.display = 'flex';
                }
            }
        });

        // Enable push notifications
        async function enablePushNotifications() {
            if (typeof MYUNotifications !== 'undefined') {
                const granted = await MYUNotifications.requestPermission();
                if (granted) {
                    document.getElementById('push-permission-banner').style.display = 'none';
                    // Show success notification
                    MYUNotifications.showPush('Notifications Enabled! 🎉', {
                        body: 'You will now receive push notifications for important updates.',
                        icon: '/images/logo.png'
                    });
                }
            }
        }

        // Filter notifications by type
        function filterNotifications(filter) {
            // Update active tab
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Filter cards
            const cards = document.querySelectorAll('.notification-card');
            cards.forEach(card => {
                const type = card.dataset.type;
                const isRead = card.dataset.read === 'true';

                let show = false;
                switch(filter) {
                    case 'all':
                        show = true;
                        break;
                    case 'unread':
                        show = !isRead;
                        break;
                    case 'message':
                        show = type === 'message';
                        break;
                    case 'reminder':
                        show = type === 'medication' || type === 'reminder' || type === 'diary' || type === 'ticlog';
                        break;
                }
                card.style.display = show ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>
