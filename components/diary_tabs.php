<?php
/*
 * components/diary_tabs.php
 *
 * This component builds the "Entry" and "Visuals" tabs.
 * It's smart and knows which page is active.
 *
 * We pass it a variable:
 * $active_tab = 'Entry'; (or 'Visuals')
 */

$active_tab = $active_tab ?? 'Entry'; // Default to Entry

// Define our two tabs
$tabs = [
    'Entry' => 'new_emotional_diary.php',
    'Visuals' => '#' // This is the page we will build later
];

// Define the styles
$active_classes = 'font-semibold text-green-800 border-b-2 border-green-800';
$inactive_classes = 'font-medium text-gray-500 hover:text-gray-700';

?>
<div class="flex space-x-2 -mt-3 mb-2 border-b border-gray-300">
    <?php foreach ($tabs as $title => $url): ?>
        
        <a href="<?php echo $url; ?>" 
           class="flex-1 py-3 text-center <?php echo ($active_tab == $title) ? $active_classes : $inactive_classes; ?>">
            <?php echo $title; ?>
        </a>

    <?php endforeach; ?>
</div>