<?php
/*
 * components/diary_metrics_grid.php
 *
 * This component is the 3-column grid for Sleep, Anxiety, and Stress.
 * It is responsive and will stack on mobile.
 */
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
    <!-- Box 1: Sleep (Reusing Input Component) -->
    <div class="bg-white p-6 rounded-lg shadow-sm ">
        <?php
        // We reuse our existing, working input.php component!
        // We set $required to false.
        $id = 'sleep'; $name = 'sleep'; $label = 'How many hours did you sleep?'; $type = 'number'; $value = ''; $autocomplete = 'off'; $required = false; 
        include 'input.php';
        ?>
    </div>

    <!-- Box 2: Anxiety Slider -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <label for="anxiety" class="block text-sm font-semibold text-gray-700 mb-2">Anxiety Level</label>
        <!-- We use our custom CSS class "diary-slider" -->
        <input type="range" id="anxiety" name="anxiety" min="0" max="10" value="0" class="diary-slider">
        <span class="text-xs text-gray-500" id="anxiety-value">Selected: 0</span> 
    </div>

    <!-- Box 3: Stress Slider -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <label for="stress" class="block text-sm font-semibold text-gray-700 mb-2">Stress Level</label>
        <!-- We use our custom CSS class "diary-slider" -->
        <input type="range" id="stress" name="stress" min="0" max="10" value="0" class="diary-slider">
        <span class="text-xs text-gray-500" id="stress-value">Selected: 0</span>
    </div>

</div> <!-- End 3-Column Grid -->