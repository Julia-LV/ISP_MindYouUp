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
        <div class="flex-1 flex flex-col w-full md:pl-64">

            <!-- 
              TOPBAR (This is the new part!)
              This is the header for the *main content*.
            -->
            <header class="h-16 bg-[#FFF7E1] border-b flex items-center justify-between px-4 sticky top-0 z-10">
                
                <!-- Hamburger Button (controls sidebar) -->
                <div class="flex items-center space-x-2">
                    <!-- Hamburger Button (controls sidebar) -->
                    <button id="sidebar-toggle" class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    <!-- Logo (Now in the topbar) -->
                    <img src="../../assets/img/MYU logos/MYU_Horizontal Logo.png" alt="Mind You Up Logo" class="w-28 h-auto">
                </div>
                
                <!-- Right-side content (e.g., User Profile) -->
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION["first_name"]); ?>!</span>
                    <!-- You can add a profile picture/icon here -->
                </div>
            </header>
        

            <!-- 
              This is the main content wrapper.
              - It has `w-full` (full-width)
              - It has NO PADDING.
              - Your new_emotional_diary.php page adds its own padding.
            -->
<main class="flex-1 w-full p-6 md:p-2 overflow-y-auto bg-[#FFFDF5]">