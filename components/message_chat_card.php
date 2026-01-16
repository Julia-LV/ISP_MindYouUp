<?php
/*
 * message_card_chat.php
 * 
 * Reusable chat message component.
 * REQUIRED VARIABLES:
 * - $senderId        => ID of the sender
 * - $currentUserId   => ID of the logged-in patient
 * - $text            => Message text
 * - $time            => Timestamp string
 * 
 * Usage: include this inside a loop over messages.
 */

if (!isset($senderId) || !isset($currentUserId) || !isset($text)) {
    echo "<!-- Missing required parameters for message_card_chat.php -->";
    return;
}

// Determine class based on sender
$class = ($senderId == $currentUserId) ? 'patient' : 'professional';
?>

<div class="message <?= $class ?>">
    <?= htmlspecialchars($text) ?>
    <?php if (!empty($time)): ?>
        <div class="message-time"><?= date("M d, H:i", strtotime($time)) ?></div>
    <?php endif; ?>
</div>
