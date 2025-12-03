<?php
// File: /c:/Users/rodri/OneDrive/Documents/GitHub/ISP_MindYouUp/pages/common/privacy.php
// Terms & Conditions / Privacy Policy page with links and inline viewer for two PDF files.

if (session_status() === PHP_SESSION_NONE) { session_start(); }
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

// Configure PDF locations (relative to this PHP file or absolute paths)
$docs = [
  'terms'   => __DIR__ . '/../../assets/privacy/Terms  Conditions_.pdf',
  'privacy' => __DIR__ . '/../../assets/privacy/Terms  Conditions2.pdf',
];

// Public URLs for download/view (relative to site root)
$publicUrls = [
    'terms'   => '../../assets/privacy/Terms  Conditions_.pdf',
    'privacy' => '../../assets/privacy/Terms  Conditions2.pdf',
];

$docKey = isset($_GET['doc']) ? $_GET['doc'] : null;
$available = array_keys($docs);
$selectedFileExists = $docKey && isset($docs[$docKey]) && file_exists($docs[$docKey]);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Terms & Conditions — MindYouUp</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- TailwindCSS CDN (needed for navbar utility classes) -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

  <!-- Page CSS (relative path) -->
  <link href="../../CSS/privacy.css" rel="stylesheet">
</head>
<body>
  <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
  <?php include __DIR__ . '/../../components/header_component.php'; ?>
  <div class="container">
    <h1>Terms & Conditions and Privacy Policy</h1>
    <p class="lead">Download or view the official documents below. If you need these files in another format, contact the site administrator.</p>

    <div class="list">
      <div class="card">
        <strong>Terms &amp; Conditions</strong>
        <div class="notice">Last updated: <!-- you can update this date manually --> 2025-01-01</div>
        <?php if (file_exists($docs['terms'])): ?>
          <div>
            <a href="<?php echo htmlspecialchars($publicUrls['terms']); ?>" target="_blank" rel="noopener">Open in new tab</a>
            <a href="?doc=terms" style="background:#10a37f;margin-left:8px">View inline</a>
            <a href="<?php echo htmlspecialchars($publicUrls['terms']); ?>" download style="background:#6c757d;margin-left:8px">Download</a>
          </div>
        <?php else: ?>
          <div class="fallback">Terms PDF not found on server.</div>
        <?php endif; ?>
      </div>

      <div class="card">
        <strong>Privacy Policy</strong>
        <div class="notice">Last updated: <!-- you can update this date manually --> 2025-01-01</div>
        <?php if (file_exists($docs['privacy'])): ?>
          <div>
            <a href="<?php echo htmlspecialchars($publicUrls['privacy']); ?>" target="_blank" rel="noopener">Open in new tab</a>
            <a href="?doc=privacy" style="background:#10a37f;margin-left:8px">View inline</a>
            <a href="<?php echo htmlspecialchars($publicUrls['privacy']); ?>" download style="background:#6c757d;margin-left:8px">Download</a>
          </div>
        <?php else: ?>
          <div class="fallback">Privacy PDF not found on server.</div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($selectedFileExists): 
        // Map to public URL for embedding (do not expose filesystem path to client)
        $embedUrl = $publicUrls[$docKey];
    ?>
      <h2>Viewing: <?php echo $docKey === 'terms' ? 'Terms & Conditions' : 'Privacy Policy'; ?></h2>
      <div class="viewer">
        <iframe src="<?php echo htmlspecialchars($embedUrl); ?>" title="Document viewer" width="100%" height="100%" style="border:0"></iframe>
      </div>
      <p class="notice">If your browser cannot display PDF inline, use the download button above.</p>
    <?php elseif ($docKey): ?>
      <div class="fallback">Requested document not available.</div>
    <?php endif; ?>

  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>