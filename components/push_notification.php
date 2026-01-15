<?php
/**
 * Push Notification Component
 * 
 * Include this file in your pages to enable browser push notifications.
 * It handles permission requests, service worker registration, and provides
 * functions to send notifications both via browser push and in-site storage.
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only show for logged-in users
$push_user_id = $_SESSION['user_id'] ?? null;
$push_user_role = $_SESSION['role'] ?? 'patient';
?>

<!-- Service Worker Registration -->
<script>
// Register Service Worker for push notifications
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= dirname($_SERVER['PHP_SELF']) ?>/../../sw.js')
        .then(function(registration) {
            console.log('Service Worker registered with scope:', registration.scope);
        })
        .catch(function(error) {
            console.log('Service Worker registration failed:', error);
        });
}
</script>

<!-- Push Notification System -->
<script>
/**
 * MindYouUp Push Notification System
 */
const MYUNotifications = {
    // Configuration
    config: {
        userId: <?= json_encode($push_user_id) ?>,
        userRole: <?= json_encode($push_user_role) ?>,
        baseUrl: '<?= rtrim(dirname($_SERVER['PHP_SELF']), '/') ?>/../../',
        iconPath: '../../assets/img/MYU logos/logo.png',
        defaultTag: 'myu-notification'
    },
    
    /**
     * Check if browser supports notifications
     */
    isSupported: function() {
        return 'Notification' in window;
    },
    
    /**
     * Get current permission status
     * @returns {string} 'default', 'granted', or 'denied'
     */
    getPermission: function() {
        if (!this.isSupported()) return 'unsupported';
        return Notification.permission;
    },
    
    /**
     * Request notification permission from user
     * @returns {Promise<boolean>}
     */
    requestPermission: async function() {
        if (!this.isSupported()) {
            console.warn('Notifications not supported in this browser');
            return false;
        }
        
        try {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        } catch (error) {
            console.error('Error requesting notification permission:', error);
            return false;
        }
    },
    
    /**
     * Show a browser push notification
     * @param {string} title - Notification title
     * @param {object} options - Notification options
     */
    showPush: function(title, options = {}) {
        if (this.getPermission() !== 'granted') {
            console.warn('Notification permission not granted');
            return null;
        }
        
        const defaultOptions = {
            icon: this.config.iconPath,
            badge: this.config.iconPath,
            vibrate: [200, 100, 200],
            tag: options.tag || this.config.defaultTag,
            requireInteraction: options.requireInteraction || false,
            silent: false,
            ...options
        };
        
        try {
            const notification = new Notification(title, defaultOptions);
            
            // Handle click
            notification.onclick = function(event) {
                event.preventDefault();
                window.focus();
                if (options.url) {
                    window.location.href = options.url;
                }
                notification.close();
            };
            
            // Auto close after 10 seconds if not requiring interaction
            if (!defaultOptions.requireInteraction) {
                setTimeout(() => notification.close(), 10000);
            }
            
            return notification;
        } catch (error) {
            console.error('Error showing notification:', error);
            return null;
        }
    },
    
    /**
     * Send notification to server for in-site storage
     * @param {string} title - Notification title
     * @param {string} message - Notification message
     * @param {string} type - Notification type
     * @param {string} targetUserId - User to notify (optional, defaults to current user)
     */
    saveToDatabase: async function(title, message, type = 'general', targetUserId = null) {
        try {
            const response = await fetch(this.config.baseUrl + 'api/save_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    title: title,
                    message: message,
                    type: type,
                    target_user_id: targetUserId || this.config.userId
                })
            });
            
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error saving notification to database:', error);
            return false;
        }
    },
    
    /**
     * Send both push and in-site notification
     * @param {string} title - Notification title
     * @param {string} message - Notification message
     * @param {object} options - Additional options
     */
    send: function(title, message, options = {}) {
        // Show browser push notification
        this.showPush(title, {
            body: message,
            ...options
        });
        
        // Save to database for in-site notification
        if (options.saveToDb !== false) {
            this.saveToDatabase(title, message, options.type || 'general', options.targetUserId);
        }
    },
    
    // ==========================================
    // Pre-defined notification types
    // ==========================================
    
    /**
     * New chat message notification
     */
    newMessage: function(senderName, messagePreview, chatUrl = null) {
        this.send(
            `New message from ${senderName}`,
            messagePreview.substring(0, 100) + (messagePreview.length > 100 ? '...' : ''),
            {
                tag: 'new-message',
                url: chatUrl || this.config.baseUrl + 'pages/patient/chat.php',
                type: 'message'
            }
        );
    },
    
    /**
     * Medication reminder notification
     */
    medicationReminder: function(medicationName, time = null) {
        const message = time 
            ? `Time to take your ${medicationName} at ${time}`
            : `Time to take your ${medicationName}`;
            
        this.send(
            'Medication Reminder 💊',
            message,
            {
                tag: 'medication-reminder',
                url: this.config.baseUrl + 'pages/patient/medication_tracking.php',
                type: 'medication',
                requireInteraction: true
            }
        );
    },
    
    /**
     * Emotional diary reminder notification
     */
    diaryReminder: function() {
        this.send(
            'Daily Check-in 📝',
            "Don't forget to log your emotional state today!",
            {
                tag: 'diary-reminder',
                url: this.config.baseUrl + 'pages/patient/new_emotional_diary.php',
                type: 'diary'
            }
        );
    },
    
    /**
     * Tic log reminder notification
     */
    ticLogReminder: function() {
        this.send(
            'Tic Log Reminder 📋',
            "Remember to log your tics for today",
            {
                tag: 'ticlog-reminder',
                url: this.config.baseUrl + 'pages/patient/ticlog_motor.php',
                type: 'ticlog'
            }
        );
    },
    
    /**
     * Connection request notification (for professionals)
     */
    connectionRequest: function(patientName) {
        this.send(
            'New Connection Request',
            `${patientName} wants to connect with you`,
            {
                tag: 'connection-request',
                url: this.config.baseUrl + 'pages/professional/my_patients.php',
                type: 'connection',
                requireInteraction: true
            }
        );
    },
    
    /**
     * Connection accepted notification (for patients)
     */
    connectionAccepted: function(professionalName) {
        this.send(
            'Connection Accepted ✅',
            `${professionalName} has accepted your connection request`,
            {
                tag: 'connection-accepted',
                url: this.config.baseUrl + 'pages/patient/my_professionals.php',
                type: 'connection'
            }
        );
    },
    
    /**
     * Appointment reminder notification
     */
    appointmentReminder: function(withName, dateTime) {
        this.send(
            'Appointment Reminder 📅',
            `You have an appointment with ${withName} at ${dateTime}`,
            {
                tag: 'appointment-reminder',
                type: 'appointment',
                requireInteraction: true
            }
        );
    },
    
    /**
     * YGTSS form reminder
     */
    ygtssReminder: function() {
        this.send(
            'YGTSS Assessment Due 📊',
            'It\'s time to complete your weekly YGTSS assessment',
            {
                tag: 'ygtss-reminder',
                url: this.config.baseUrl + 'pages/patient/YGTSS_form.php',
                type: 'assessment',
                requireInteraction: true
            }
        );
    },
    
    /**
     * Generic success notification
     */
    success: function(message) {
        this.showPush('Success ✅', {
            body: message,
            tag: 'success'
        });
    },
    
    /**
     * Generic error notification
     */
    error: function(message) {
        this.showPush('Error ❌', {
            body: message,
            tag: 'error'
        });
    }
};

// Make it globally available
window.MYUNotifications = MYUNotifications;
</script>

<?php if ($push_user_id): ?>
<!-- Notification Permission Prompt -->
<div id="notification-permission-prompt" style="display: none; position: fixed; bottom: 20px; right: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); padding: 20px; max-width: 350px; z-index: 9999;">
    <div style="display: flex; align-items: flex-start; gap: 12px;">
        <span style="font-size: 28px;">🔔</span>
        <div style="flex: 1;">
            <strong style="display: block; margin-bottom: 4px; color: #005949;">Stay Updated!</strong>
            <p style="margin: 0 0 12px 0; font-size: 13px; color: #666;">Enable notifications to receive alerts for new messages, medication reminders, and more.</p>
            <div style="display: flex; gap: 8px;">
                <button onclick="MYUNotifications.requestPermission().then(function(granted) { document.getElementById('notification-permission-prompt').style.display = 'none'; if(granted) { MYUNotifications.success('Notifications enabled!'); } });" 
                        style="background: #005949; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px;">
                    Enable
                </button>
                <button onclick="document.getElementById('notification-permission-prompt').style.display = 'none'; localStorage.setItem('notification_prompt_dismissed', Date.now());" 
                        style="background: #f0f0f0; color: #333; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px;">
                    Not Now
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Show permission prompt if needed
document.addEventListener('DOMContentLoaded', function() {
    // Check if notifications are supported and permission not yet decided
    if (!MYUNotifications.isSupported()) return;
    if (MYUNotifications.getPermission() !== 'default') return;
    
    // Check if user dismissed recently
    const dismissed = localStorage.getItem('notification_prompt_dismissed');
    if (dismissed) {
        const daysSince = (Date.now() - parseInt(dismissed)) / (1000 * 60 * 60 * 24);
        if (daysSince < 7) return; // Don't show for 7 days after dismissing
    }
    
    // Show prompt after 3 seconds
    setTimeout(function() {
        document.getElementById('notification-permission-prompt').style.display = 'block';
    }, 3000);
});
</script>
<?php endif; ?>
