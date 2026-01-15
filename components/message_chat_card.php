<?php


if (!isset($senderId) || !isset($currentUserId) || !isset($text)) {
    echo "<!-- Missing required parameters for message_card_chat.php -->";
    return;
}


$class = ($senderId == $currentUserId) ? 'patient' : 'professional';
?>

<div class="message <?= $class ?>">
    <?= htmlspecialchars($text) ?>
    <?php if (!empty($time)): ?>
        <div class="message-time"><?= date("M d, H:i", strtotime($time)) ?></div>
    <?php endif; ?>
</div>
