<?php
/*
 * new_patient_layout_start.php
 *
 * This is the NEW, FIXED layout shell.
 * It replaces the old, broken 'patient_layout_start.php'.
 * It correctly creates the sidebar and the full-width main content area.
 */

// Get the current page's filename (e.g., new_emotional_diary.php)
$current_page = basename($_SERVER['PHP_SELF']);

// Set a default page title if one isn't provided
$page_title = $page_title ?? 'Mind You Up';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Link to your global stylesheet (for fonts) -->
    <link rel="stylesheet" href="../../css/stylemain.css">

    <!-- 
      This is the NEW page-specific CSS link for the diary.
      We check if the current page is the new diary page.
    -->
    <?php if ($current_page == 'new_emotional_diary.php'): ?>
        <link rel="stylesheet" href="../../css/new_emotional_diary.css">
    <?php endif; ?>
</head>
<!-- 
  This body tag is now clean. 
  'h-full' and 'bg-gray-100' are the base.
-->
<body class="h-full bg-gray-100">
    <!-- 
      This is the main flex container for the whole screen.
      It will be at least the full height of the screen.
    -->
    <div class="flex min-h-screen">
        
        <!-- 
          SIDEBAR (Desktop)
          - `h-screen` makes it fill the screen height.
          - `sticky top-0` keeps it locked in place on scroll.
          - `w-64` gives it a fixed width, stopping the "zoom" bug.
        -->
        <aside class="w-64 flex-shrink-0 bg-white border-r hidden md:flex flex-col sticky top-0 h-screen">
            <!-- Logo -->
            <div class="h-16 flex items-center justify-center border-b">
                <!-- 
                  The `w-32` class here is what sizes the logo.
                  It wasn't working before because the parent was broken.
                -->
                <img src="../../assets/img/MYU logos/MYU_Horizontal Logo.png" alt="Mind You Up Logo" class="w-32 h-auto">
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-4 space-y-2">
                <?php
                // We'll update this nav as we build new pages
                $nav_links = [
                    'home_patient.php' => 'Dashboard',
                    'new_emotional_diary.php' => 'Emotional Diary',
                    'new_tic_log.php' => 'Tic Log' // Future page
                ];
                
                foreach ($nav_links as $url => $title) {
                    $is_active = ($current_page == $url);
                    $base_classes = "flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors";
                    $active_classes = "bg-green-100 text-green-800 font-semibold";
                    $inactive_classes = "text-gray-600 hover:bg-gray-100 hover:text-gray-900";
                    $link_class = $is_active ? ($base_classes . " " . $active_classes) : ($base_classes . " " . $inactive_classes);
                    
                    echo '<a href="' . $url . '" class="' . $link_class . '">';
                    echo '<span>' . htmlspecialchars($title) . '</span>';
                    echo '</a>';
                }
                ?>
            </nav>

            <!-- Bottom/Logout Area -->
            <div class="p-4 border-t">
                <a href="../auth/logout.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-gray-600 hover:bg-red-50 hover:text-red-600">
                    <span>Log Out</span>
                </a>
            </div>
        </aside>

        <!-- 
          MAIN CONTENT AREA
          - This is now a simple flex container that grows.
          - It has the cream background.
          - The page (body) will scroll.
        -->
        <div class="flex-1 flex flex-col bg-[#FFFDF5]">

            <!-- MOBILE HEADER -->
            <header class="h-16 bg-white border-b flex items-center justify-between px-4 md:hidden sticky top-0 z-10">
                <div class-="flex-1">
                    <img src="../../assets/img/MYU logos/MYU_Horizontal Logo.png" alt="Mind You Up Logo" class="w-28 h-auto">
                </div>
                <button class="p-2">
                    <!-- Hamburger icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </header>

            <!-- 
              This is the main content wrapper.
              - It has `w-full` (full-width)
              - It has NO PADDING.
              - Your new_emotional_diary.php page adds its own padding.
            -->
            <main class="w-full">