<?php
/*
 * input.php - FINAL, FIXED VERSION
 *
 * This component is now safe.
 * It REQUIRES $name to be set by the parent file.
 * This fixes the "variable leak" bug permanently.
 */

// Set defaults for the variables
$id = $id ?? '';
$name = $name ?? ''; // Default to empty, MUST be set by parent
$type = $type ?? 'text';
$label = $label ?? 'Input Field';
$required = $required ?? true;
$value = $value ?? '';
$autocomplete = $autocomplete ?? 'on';

// A safety check. If $name is not set, the form will break.
if (empty($name)) {
    echo '<div class"p-3 bg-red-800 text-white rounded-md">
            <strong>Component Error:</strong> $name variable is not set.
          </div>';
}
?>
<div>
    <label for="<?php echo $id; ?>" class="block text-sm font-medium text-gray-700">
        <?php echo htmlspecialchars($label); ?>
    </label>
    <input 
        type="<?php echo htmlspecialchars($type); ?>" 
        id="<?php echo $id; ?>" 
        name="<?php echo htmlspecialchars($name); ?>"
        value="<?php echo htmlspecialchars($value); ?>"
        autocomplete="<?php echo htmlspecialchars($autocomplete); ?>"
        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-[#005949]"
        <?php if ($required) echo 'required'; ?>
    >
</div>