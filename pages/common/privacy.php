<<<<<<< Updated upstream
<!DOCTYPE html>
=======
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

// Single PDF used for both Terms & Privacy, keep filename as provided
$docs = [
  'policy' => __DIR__ . '/../../Privacy_Policy.pdf',
];

// Public URL for client access (relative to this page)
$publicUrls = [
  'policy' => '../../Privacy_Policy.pdf',
];

$docKey = isset($_GET['doc']) ? $_GET['doc'] : null;
$available = array_keys($docs);
$selectedFileExists = $docKey && isset($docs[$docKey]) && file_exists($docs[$docKey]);
?>
<!doctype html>
>>>>>>> Stashed changes
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        h1 {
            text-align: center;
            margin-bottom: 24px;
        }
        h2 {
            margin-top: 24px;
        }
        p {
            line-height: 1.6;
        }
    </style>
</head>
<body>
<<<<<<< Updated upstream
    <div class="container">
        <h1>Privacy Policy</h1>
        <p>
            Your privacy is important to us. This page explains how we collect, use, and protect your information when you use our app.
        </p>
        <h2>Information We Collect</h2>
        <p>
            We may collect personal information such as your name, email address, and usage data to improve your experience.
        </p>
        <h2>How We Use Your Information</h2>
        <p>
            Your information is used to provide and improve our services, personalize your experience, and communicate updates.
        </p>
        <h2>Data Protection</h2>
        <p>
            We implement security measures to protect your data from unauthorized access. Your information is not shared with third parties except as required by law.
        </p>
        <h2>Contact Us</h2>
        <p>
            If you have any questions about our privacy policy, please contact us at support@mindyouup.com.
        </p>
    </div>
=======
  <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
  <?php include __DIR__ . '/../../components/header_component.php'; ?>
  <div class="container">
    <h1>Terms & Conditions and Privacy Policy</h1>
    <p class="lead">Download or view the official documents below. If you need these files in another format, contact the site administrator.</p>

    <div class="list">
      <div class="card">
        <strong>Terms &amp; Conditions and Privacy Policy</strong>
        <div class="notice">Last updated: <!-- update manually if needed --> 2025-01-01</div>
        <?php if (file_exists($docs['policy'])): ?>
          <div>
            <a href="<?php echo htmlspecialchars($publicUrls['policy']); ?>" target="_blank" rel="noopener">Open in new tab</a>
            <a href="?doc=policy" style="background:#10a37f;margin-left:8px">View inline</a>
            <a href="<?php echo htmlspecialchars($publicUrls['policy']); ?>" download style="background:#6c757d;margin-left:8px">Download</a>
          </div>
        <?php else: ?>
          <div class="fallback">Policy PDF not found on server.</div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($selectedFileExists): 
        // Map to public URL for embedding (do not expose filesystem path to client)
        $embedUrl = $publicUrls[$docKey];
    ?>
      <h2>Viewing: Terms &amp; Conditions and Privacy Policy</h2>
      <div class="viewer">
        <iframe src="<?php echo htmlspecialchars($embedUrl); ?>" title="Document viewer" width="100%" height="100%" style="border:0"></iframe>
      </div>
      <p class="notice">If your browser cannot display PDF inline, use the download button above.</p>
    <?php elseif ($docKey): ?>
      <div class="fallback">Requested document not available.</div>
    <?php endif; ?>

  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
>>>>>>> Stashed changes
</body>
</html>