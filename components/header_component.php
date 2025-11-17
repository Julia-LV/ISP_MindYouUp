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
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Link to your global stylesheet -->
    <link rel="stylesheet" href="../../css/stylemain.css">

    <!-- 
      This is the NEW page-specific CSS link for the diary.
      We will add this to the header component
      so the diary page itself stays clean.
    -->
    <?php if (basename($_SERVER['PHP_SELF']) == 'new_emotional_diary.php'): ?>
        <link rel="stylesheet" href="../../css/new_emotional_diary.css">
    <?php endif; ?>

    <script src="../../js/main.js" defer></script>

</head>
<!-- 
  This body tag is now clean. 
  The 'h-full' and 'bg-gray-100' work with the layout.
-->
<!--body class="bg-[#FFF7E1] flex items-center justify-center min-h-screen p-4"-->