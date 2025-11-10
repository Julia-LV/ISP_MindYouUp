<?php
// Language selector page
// Presents English and Portuguese options. Sets a cookie and session, then redirects back.
require_once __DIR__ . '/../../includes/header.php';

/*
 // USER INFO (COMMENTED OUT)
 // Example: read the logged-in user id from session and fetch profile data.
 // All lines are commented out by design.
 // if (session_status() === PHP_SESSION_NONE) session_start();
 // require_once __DIR__ . '/../../config.php';
 // $current_user_id = $_SESSION['user_id'] ?? null;
 // $CURRENT_USER = null;
 // if ($current_user_id) {
 //     $sql = "SELECT User_ID, First_Name, Last_Name, `E-mail`, `Role` FROM user_profile WHERE User_ID = ? LIMIT 1";
 //     if ($stmt = $conn->prepare($sql)) {
 //         $stmt->bind_param('i', $current_user_id);
 //         $stmt->execute();
 //         $stmt->bind_result($u_id,$u_first,$u_last,$u_email,$u_role);
 //         if ($stmt->fetch()) {
 //             $CURRENT_USER = ['id'=>(int)$u_id,'first'=>$u_first,'last'=>$u_last,'email'=>$u_email,'role'=>$u_role];
 //         }
 //         $stmt->close();
 //     }
 // }
 */

// Process POST from the form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['lang'])) {
    $choice = $_POST['lang'] === 'pt' ? 'pt' : 'en';
    if (function_exists('set_language')) {
        set_language($choice);
    } else {
        // fallback: set cookie and session directly
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        setcookie('site_lang', $choice, time() + 60 * 60 * 24 * 30, '/');
        $_SESSION['site_lang'] = $choice;
    }
    // Redirect back to where the user came from (if safe) or homepage
    $redirect = '/';
    if (!empty($_POST['redirect']) && strpos($_POST['redirect'], '/') === 0) {
        $redirect = $_POST['redirect'];
    } elseif (!empty($_SERVER['HTTP_REFERER'])) {
        $redirect = $_SERVER['HTTP_REFERER'];
    }
    // Add a short query param to show it was saved
    $sep = strpos($redirect, '?') === false ? '?' : '&';
    header('Location: ' . $redirect . $sep . 'lang_saved=1');
    exit;
}

// Show the selector
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Choose language</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; padding: 2rem; background: #FFF7E1; }
        .lang-wrap { max-width: 420px; margin: 2rem auto; text-align: center; }
        button { padding: 0.6rem 1.2rem; margin: 0.5rem; font-size: 1rem; border-radius: 8px; border: none; cursor: pointer; }
        .en { background: linear-gradient(180deg,#F26647,#e6553e); color: white; }
        .pt { background: linear-gradient(180deg,#005949,#00463f); color: white; }
    </style>
</head>
<body>
    <div class="lang-wrap">
        <h1>Choose language</h1>
        <form method="post">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER'] ?? '/'); ?>">
            <button class="en" type="submit" name="lang" value="en">English</button>
            <button class="pt" type="submit" name="lang" value="pt">Português</button>
        </form>
    </div>
</body>
</html>
