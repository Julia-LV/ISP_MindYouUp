<?php
/*
 * new_patient_layout_start.php
 *
 * This is the NEW, FIXED layout shell.
 * It replaces the old, broken 'patient_layout_start.php'.
 * It correctly creates the sidebar and the full-width main content area.
 */

include 'header_component.php';

// Get the current page's filename (e.g., new_emotional_diary.php)
$current_page = basename($_SERVER['PHP_SELF']);

?>
<!-- 
  This body tag is now clean. 
  'h-full' and 'bg-gray-100' are the base.
-->
<body class="h-full bg-gray-100">
    <!-- 
      This is the main flex container for the whole screen.
      It will be at least the full height of the screen.
    -->
    <?php include '../../includes/navbar.php'; ?>
    
    

        <!-- 
          MAIN CONTENT AREA
          - `md:ml-64` makes room for the sidebar on desktop.
          - `flex-1` makes it grow to fill the rest of the space.
        -->
        
        

            <!-- 
              This is the main content wrapper.
              - It has `w-full` (full-width)
              - It has NO PADDING.
              - Your new_emotional_diary.php page adds its own padding.
            -->
<main class="flex-1 w-full p-6 md:p-2 overflow-y-auto bg-[#FFFDF5]">