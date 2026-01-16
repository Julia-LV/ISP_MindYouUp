<?php


// Set Defaults
$label = $label ?? 'Level';
$id    = $id ?? 'slider_' . uniqid();
$name  = $name ?? $id;
$min   = $min ?? 0;
$max   = $max ?? 10;
$val   = $val ?? 0;
?>

<div class="bg-white p-6 rounded-lg shadow-sm h-full flex flex-col justify-between">
    <label for="<?php echo $id; ?>" class="block text-sm font-semibold text-gray-700 mb-2">
        <?php echo htmlspecialchars($label); ?>
    </label>

    <input 
        type="range" 
        id="<?php echo $id; ?>" 
        name="<?php echo $name; ?>" 
        min="<?php echo $min; ?>" 
        max="<?php echo $max; ?>" 
        value="<?php echo $val; ?>" 
        class="diary-slider w-full"
        oninput="document.getElementById('<?php echo $id; ?>-value').innerText = 'Selected: ' + this.value"
    >

    <span class="text-xs text-gray-500 mt-2 block" id="<?php echo $id; ?>-value">
        Selected: <?php echo $val; ?>
    </span>
</div>