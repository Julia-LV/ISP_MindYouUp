<?php
/*
 * logout.php
 *
 * This script destroys the user's session (logs them out)
 * and redirects them back to the login page.
 */

// 1. We must start the session so we can access it.
session_start();

// 2. Unset all of the session variables.
//    $_SESSION = array(); will clear everything in the session.
$_SESSION = array();

// 3. Finally, destroy the session.
//    This fully logs the user out.
session_destroy();

// 4. Redirect the user back to the login page.
//    Since logout.php is in the same /auth/ folder as login.php,
//    we can just redirect to "login.php".
header("Location: login.php");
exit; // Always call exit() after a header redirect.

?>
