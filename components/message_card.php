<?php
/*
 * message_card.php
 * 
 * Reusable component for message list items.
 * SAFE VERSION – DOES NOT AFFECT NAVBAR.
 *
 * REQUIRED VARIABLES:
 * - $prof_id       => Professional User_ID
 * - $name          => Full name of the professional
 * - $preview       => Message preview text
 * - $timestamp     => Date string (optional)
 */

if (!isset($prof_id) || !isset($name)) {
    echo "<!-- Missing required parameters for message_card.php -->";
    return;
}

$avatar = strtoupper(substr($name, 0, 1));
?>

<!-- 
    LOCAL WRAPPER
    All classes isolated – NO CSS OVERRIDES.
-->
<div 
    onclick="openChat(<?= htmlspecialchars($prof_id) ?>)"
    class="cursor-pointer bg-white rounded-2xl p-5 shadow-sm border border-gray-200 
           hover:shadow-md transition duration-200 w-full"
>
    <div class="flex items-center gap-4">

        <!-- AVATAR (Tailwind only, safe) -->
        <div class="w-14 h-14 flex items-center justify-center 
                    rounded-full bg-[#005949] text-white text-xl font-bold">
            <?= $avatar ?>
        </div>

        <!-- TEXT CONTENT -->
        <div class="flex-1 min-w-0">
            <div class="text-lg font-semibold text-[#005949] truncate">
                <?= htmlspecialchars($name) ?>
            </div>

            <div class="text-gray-600 text-sm mt-1 truncate">
                <?= htmlspecialchars($preview) ?>
            </div>
        </div>

        <!-- TIMESTAMP (optional) -->
        <?php if (!empty($timestamp)) : ?>
            <div class="text-xs text-gray-500 whitespace-nowrap">
                <?= htmlspecialchars($timestamp) ?>
            </div>
        <?php endif; ?>

    </div>
</div>
