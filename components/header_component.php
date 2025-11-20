<?php
/*
 * header_component.php
 *
 * --- FIX ---
 * REMOVED all layout classes from the <body> tag.
 * It now only sets the background color.
 * This will remove the "peeping" background color at the top.
 */

// Set a default page title if one isn't provided
$page_title = $page_title ?? 'Mind You Up';
$no_layout = $no_layout ?? false;
// Default body class if one isn't provided by the page
$body_class = $body_class ?? 'h-full bg-gray-100'
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- TailwindCSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Link to your global stylesheet -->
    <link rel="stylesheet" href="../../css/stylemain.css">

    <?php if (basename($_SERVER['PHP_SELF']) == 'new_emotional_diary.php'): ?>
        <link rel="stylesheet" href="../../css/new_emotional_diary.css">
    <?php endif; ?>

    <script src="../../js/main.js" defer></script>

</head>

<!-- 
  DYNAMIC BODY CLASS
  This allows Login to be "flex items-center" and Dashboard to be "h-full block"
-->
<body class="<?php echo $body_class; ?>">

<?php if (!$no_layout): ?>
    <!-- 
      RESPONSIVE PADDING FIX:
      - md:pl-20 (approx 80px): For Tablet/Laptop when sidebar is just icons.
      - xl:pl-64 (approx 256px): For Big Screens when sidebar is full width.
    -->
    <div class="flex-1 flex flex-col min-h-screen w-full md:pl-20 xl:pl-64 transition-all duration-300">
        
        <!-- TOPBAR -->
        <header class="h-16 bg-[#FFF7E1] border-b sticky top-0 z-50 grid grid-cols-3 items-center px-4 shadow-sm">
            <div class="flex items-center space-x-4">
                 <!-- Optional: Add a Mobile Menu Button here if needed -->
            </div>

            <div class="text-center">
                <!-- Title or Logo could go here -->
            </div>

            <!-- Right: Profile & Settings Icons -->
            <div class="flex items-center justify-end space-x-6">
                <ul class="flex items-center space-x-6">
                    <!-- PROFILE -->
                    <li>
                        <a href="../../pages/patient/patient_profile.php" class="p-2 block rounded-full transition-transform duration-200 hover:scale-110">
                            <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor">
                                <path d="M224 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56C95.8 304 16 383.8 16 482.3 16 498.7 29.3 512 45.7 512l356.6 0c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3l-59.4 0z"/>
                            </svg>
                        </a>
                    </li>
                    <!-- SETTINGS -->
                    <li>
                        <a href="../../pages/common/settings.php" class="p-2 block rounded-full transition-transform duration-200 hover:scale-110">
                            <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                <path d="M195.1 9.5C198.1-5.3 211.2-16 226.4-16l59.8 0c15.2 0 28.3 10.7 31.3 25.5L332 79.5c14.1 6 27.3 13.7 39.3 22.8l67.8-22.5c14.4-4.8 30.2 1.2 37.8 14.4l29.9 51.8c7.6 13.2 4.9 29.8-6.5 39.9L447 233.3c.9 7.4 1.3 15 1.3 22.7s-.5 15.3-1.3 22.7l53.4 47.5c11.4 10.1 14 26.8 6.5 39.9l-29.9 51.8c-7.6 13.1-23.4 19.2-37.8 14.4l-67.8-22.5c-12.1 9.1-25.3 16.7-39.3 22.8l-14.4 69.9c-3.1 14.9-16.2 25.5-31.3 25.5l-59.8 0c-15.2 0-28.3-10.7 -31.3-25.5l-14.4-69.9c-14.1-6-27.2-13.7-39.3-22.8L73.5 432.3c-14.4 4.8-30.2-1.2-37.8-14.4L5.8 366.1c-7.6-13.2-4.9-29.8 6.5-39.9l53.4-47.5c-.9-7.4-1.3-15-1.3-22.7s.5-15.3 1.3-22.7L12.3 185.8c-11.4-10.1-14-26.8-6.5-39.9L35.7 94.1c7.6-13.2 23.4-19.2 37.8-14.4l67.8 22.5c12.1-9.1 25.3-16.7 39.3-22.8L195.1 9.5zM256.3 336a80 80 0 1 0 -.6-160 80 80 0 1 0 .6 160z"/>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
        </header>
<?php endif; ?>                  
                

               
<!-- 
  This body tag is now clean. 
  The 'h-full' and 'bg-gray-100' work with the layout.
-->
<!--body class="bg-[#FFF7E1] flex items-center justify-center min-h-screen p-4"-->