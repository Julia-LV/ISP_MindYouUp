<?php
// Common header include - start session and load language helper if available
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Load language helper (optional; file may not exist in some branches)
$langFile = __DIR__ . '/lang.php';
if (file_exists($langFile)) {
	require_once $langFile;
}

?>

