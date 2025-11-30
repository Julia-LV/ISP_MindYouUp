<?php
/*
 * components/diary_tabs.php
 *
 * A Reusable Tab Bar.
 * * Variables to pass:
 * $tabs       : Array ['Label' => 'Action/Link']
 * $active_tab : The Label of the currently active tab
 * $is_js      : true/false (If true, treats the link as an ID or JS call)
 */

$tabs = $tabs ?? []; // Default to empty if not passed
$active_tab = $active_tab ?? '';
$is_js = $is_js ?? false;

// Styles
$active_classes = 'font-semibold text-[#005949] border-b-2 border-[#005949]';
$inactive_classes = 'font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent';
?>

<div class="flex space-x-2 -mt-3 mb-6 border-b border-gray-300">
    <?php foreach ($tabs as $label => $action): ?>
        
        <?php if($is_js): ?>
            <button 
                type="button"
                onclick="<?php echo $action; ?>" 
                class="flex-1 py-3 text-center focus:outline-none transition-colors <?php echo ($active_tab === $label) ? 'active ' . $active_classes : $inactive_classes; ?>"
                id="tab-btn-<?php echo strtolower(explode(' ', $label)[0]); ?>" 
            >
                <?php echo htmlspecialchars($label); ?>
            </button>

        <?php else: ?>
            <a 
                href="<?php echo $action; ?>" 
                class="flex-1 py-3 text-center transition-colors <?php echo ($active_tab === $label) ? $active_classes : $inactive_classes; ?>"
            >
                <?php echo htmlspecialchars($label); ?>
            </a>
        <?php endif; ?>

    <?php endforeach; ?>
</div>