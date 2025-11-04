<?php
/*
 * header.php
 *
 * This is your global header file. It includes:
 * - The HTML <head> section
 * - Viewport tag for responsiveness
 * - A flexible $page_title
 * - The Tailwind CDN link
 * - The link to your global style.css
 * - The opening <body> tag with your custom cream background
 */

// Set a default page title if one isn't provided
$page_title = $page_title ?? 'Mind You Up';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Link to your global stylesheet -->
    <!-- It goes UP two folders (from /pages/components/) to the root, then DOWN into /css/ -->
    <link rel="stylesheet" href="../../css/stylemain.css">
</head>
<!-- Apply the custom cream background and base layout to the whole body -->
<body class="bg-[#FFF7E1] flex items-center justify-center min-h-screen p-4">
<?php
