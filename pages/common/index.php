<?php
// Simple index for common pages
if (session_status() === PHP_SESSION_NONE) session_start();
// Optional: include header for language helper if exists
$header = __DIR__ . '/../../includes/header.php';
if (file_exists($header)) require_once $header;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Common Index</title>
  <style>
    :root{--bg-creme:#FFF7E1;--accent-orange:#F26647;--accent-green:#005949;--radius:10px}
    body{font-family:Arial,Helvetica,sans-serif;background:var(--bg-creme);margin:0;padding:40px;color:#0b2a24}
    .wrap{max-width:720px;margin:0 auto}
    h1{color:var(--accent-green);margin:0 0 18px}
    .grid{display:flex;flex-direction:column;gap:12px}
    a.btn{display:inline-flex;align-items:center;justify-content:center;padding:12px 16px;background:linear-gradient(180deg,var(--accent-orange),#e6553e);color:#fff;text-decoration:none;border-radius:8px}
    a.btn.secondary{background:linear-gradient(180deg,var(--accent-green),#00463f)}
    .note{color:#555;font-size:.95rem;margin-top:12px}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Quick Links</h1>
    <div class="grid">
      <a class="btn" href="settings.php">Settings</a>
      <a class="btn" href="../patient/resource_hub.php">Resource Hub</a>
      <a class="btn secondary" href="../patient/track_medication.php">Track Medication</a>
    </div>
    <p class="note">This is a simple index page that links to common patient pages.</p>
  </div>
</body>
</html>
