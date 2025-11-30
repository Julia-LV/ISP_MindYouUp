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
    <?php 
        $label = 'Anxiety Level'; 
        $id = 'anxiety'; 
        $name = 'anxiety'; 
        include 'slider_card.php'; 
    ?>

    <?php 
        $label = 'Stress Level'; 
        $id = 'stress'; 
        $name = 'stress'; 
        include 'slider_card.php'; 
    ?>

</div> <!-- End 3-Column Grid -->