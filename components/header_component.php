<?php
/*
 * header_component.php
 */

$page_title = $page_title ?? 'Mind You Up';
$no_layout = $no_layout ?? false;
$body_class = $body_class ?? 'h-full bg-gray-100'
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/stylemain.css">

    <?php if (basename($_SERVER['PHP_SELF']) == 'new_emotional_diary.php'): ?>
        <link rel="stylesheet" href="../../css/new_emotional_diary.css">
    <?php endif; ?>

    <script src="../../js/main.js" defer></script>
</head>

<body class="<?php echo $body_class; ?>">

<?php 
include 'preloader.php'; 
?>

<?php if (!$no_layout): ?>
    <div id="main-wrapper" class="flex-1 flex flex-col min-h-screen w-full md:pl-20">
        <header class="h-16 bg-[#FCFBF7] border-b sticky top-0 z-50 grid grid-cols-3 items-center px-4 shadow-sm">
            <div class="flex items-center space-x-2">
                <button id="mobileMenuBtn" class="md:hidden p-2 focus:outline-none group rounded-full active:bg-[#005949]">
                    <svg id="hamburgerIcon" class="w-8 h-8 text-[#005949]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M0 96C0 78.3 14.3 64 32 64l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 128C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 288c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32L32 448c-17.7 0-32-14.3-32-32s14.3-32 32-32l384 0c17.7 0 32 14.3 32 32z"/>
                    </svg>
                </button>

                <?php 
                    $home_url = '../../index.php'; // Default fallback
                    
                    // Check 'role' instead of 'user_type'
                    if (isset($_SESSION['role'])) {
                        if ($_SESSION['role'] === 'Patient') {
                            $home_url = '../../pages/patient/home_patient.php';
                        } elseif ($_SESSION['role'] === 'Professional') {
                            $home_url = '../../pages/professional/home_professional.php';
                        }
                    }
                ?>
                
                <a href="<?php echo $home_url; ?>" class="cursor-pointer hover:opacity-75 transition-opacity">
                    <img src="../../assets/img/MYU%20logos/logo.png" alt="Mind You Up Logo" class="h-12 w-auto">
                </a>
            </div>

            <div class="text-center"></div>

            <div class="flex items-center justify-end space-x-6">
                <ul class="flex items-center space-x-6">
                    <li>
                        <a href="../../pages/patient/patient_profile.php" class="sidebar-link flex p-2 rounded-full hover:bg-[#005949] group">
                            <svg class="w-6 h-6 text-gray-700 group-hover:text-white" fill="currentColor" viewBox="0 0 448 512"><path d="M224 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56C95.8 304 16 383.8 16 482.3 16 498.7 29.3 512 45.7 512l356.6 0c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3l-59.4 0z"/></svg>
                        </a>
                    </li>
                    <li>
                        <a href="../../pages/common/settings.php" class="sidebar-link flex p-2 rounded-full hover:bg-[#005949] group">
                            <svg class="w-6 h-6 text-gray-700 group-hover:text-white" fill="currentColor" viewBox="0 0 512 512"><path d="M195.1 9.5C198.1-5.3 211.2-16 226.4-16l59.8 0c15.2 0 28.3 10.7 31.3 25.5L332 79.5c14.1 6 27.3 13.7 39.3 22.8l67.8-22.5c14.4-4.8 30.2 1.2 37.8 14.4l29.9 51.8c7.6 13.2 4.9 29.8-6.5 39.9L447 233.3c.9 7.4 1.3 15 1.3 22.7s-.5 15.3-1.3 22.7l53.4 47.5c11.4 10.1 14 26.8 6.5 39.9l-29.9 51.8c-7.6 13.1-23.4 19.2-37.8 14.4l-67.8-22.5c-12.1 9.1-25.3 16.7-39.3 22.8l-14.4 69.9c-3.1 14.9-16.2 25.5-31.3 25.5l-59.8 0c-15.2 0-28.3-10.7 -31.3-25.5l-14.4-69.9c-14.1-6-27.2-13.7-39.3-22.8L73.5 432.3c-14.4 4.8-30.2-1.2-37.8-14.4L5.8 366.1c-7.6-13.2-4.9-29.8 6.5-39.9l53.4-47.5c-.9-7.4-1.3-15-1.3-22.7s.5-15.3 1.3-22.7L12.3 185.8c-11.4-10.1-14-26.8-6.5-39.9L35.7 94.1c7.6-13.2 23.4-19.2 37.8-14.4l67.8 22.5c12.1-9.1 25.3-16.7 39.3-22.8L195.1 9.5zM256.3 336a80 80 0 1 0 -.6-160 80 80 0 1 0 .6 160z"/></svg>
                        </a>
                    </li>
                </ul>
            </div>
        </header>
<?php endif; ?>