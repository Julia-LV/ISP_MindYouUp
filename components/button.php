<?php
/*
 * components/button.php
 * * A Universal Button Component.
 * Variables you can pass:
 * $label    : Text on the button (default: 'Submit')
 * $type     : 'submit', 'button', or 'link'
 * $href     : URL (only used if type is 'link')
 * $variant  : 'primary' (Green) or 'secondary' (Gray/Cancel)
 * $onclick  : JS function (optional)
 * $width    : 'w-full' or 'w-auto' (default: w-full)
 */

// Defaults
$label   = $label ?? 'Submit';
$type    = $type ?? 'submit';
$href    = $href ?? '#';
$variant = $variant ?? 'primary';
$width   = $width ?? 'w-full';
$onclick = $onclick ?? '';

// Define Styles based on Variant
$base_classes = "flex justify-center py-3 px-6 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200";

if ($variant === 'primary') {
    // Green Style
    $color_classes = "border-transparent text-white bg-[#005949] hover:bg-[#004539] focus:ring-green-500";
} else {
    // Secondary/Cancel Style (Gray/White)
    $color_classes = "border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-indigo-500";
}

$final_classes = "$base_classes $color_classes $width";
?>

<?php if ($type === 'link'): ?>
    <a href="<?php echo htmlspecialchars($href); ?>" class="<?php echo $final_classes; ?>">
        <?php echo htmlspecialchars($label); ?>
    </a>

<?php else: ?>
    <button 
        type="<?php echo htmlspecialchars($type); ?>" 
        class="<?php echo $final_classes; ?>"
        <?php if(!empty($onclick)) echo 'onclick="' . htmlspecialchars($onclick) . '"'; ?>
    >
        <?php echo htmlspecialchars($label); ?>
    </button>
<?php endif; ?>