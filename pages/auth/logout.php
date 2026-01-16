<?php


// 1. We must start the session so we can access it.
session_start();

// 2. Unset all of the session variables.

$_SESSION = array();

// 3. Finally, destroy the session.

session_destroy();

// 4. Redirect the user back to the login page.

header("Location: login.php");
exit; 

?>
