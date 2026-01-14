<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Ensure DB connection is available
require_once __DIR__ . '/../../config.php';

$CURRENT_USER = null;
if (!empty($_SESSION['user_id']) && isset($conn)) {
    $uid = (int) $_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "SELECT User_ID, First_Name, Last_Name, Email, Role FROM user_profile WHERE User_ID = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $uid);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $CURRENT_USER = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

$page_title = 'Settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>
    <!-- TailwindCSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="../../CSS/settings.css?v=2" rel="stylesheet">
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../../components/header_component.php'; ?>

    <div class="main-content">
        <div class="settings-wrapper">
            <div class="settings-header">
                <h1>Settings</h1>
            </div>

            <div class="settings-container">
                <?php 
                $type = 'link'; $href = 'language.php'; $label = 'Language'; $variant = 'primary'; $width = 'w-full';
                include __DIR__ . '/../../components/button.php';
                ?>
                <?php 
                $type = 'link'; $href = 'notifications.php'; $label = 'Notifications'; $variant = 'primary'; $width = 'w-full';
                include __DIR__ . '/../../components/button.php';
                ?>
                <?php 
                $type = 'link'; $href = 'privacy.php'; $label = 'Privacy Policy'; $variant = 'primary'; $width = 'w-full';
                include __DIR__ . '/../../components/button.php';
                ?>
                <?php 
                $type = 'link'; $href = 'aboutus.php'; $label = 'About the app'; $variant = 'primary'; $width = 'w-full';
                include __DIR__ . '/../../components/button.php';
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
