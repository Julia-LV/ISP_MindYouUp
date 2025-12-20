<!DOCTYPE html>
<?php
// File: /c:/Users/rodri/OneDrive/Documents/GitHub/ISP_MindYouUp/pages/common/privacy.p
// Single-source Terms & Conditions and Privacy Policy download/view page.

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
if (!isset($docs[$docKey])) {
    $docKey = null;
}
$selectedFileExists = $docKey && file_exists($docs[$docKey]);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terms &amp; Privacy  MindYouUp</title>
    <!-- Bootstrap CSS for layout and navbar compatibility -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            background: #f7f7f7;
        }
        .page-container {
            max-width: 1200px;
            margin: 32px auto 48px;
            padding: 0 16px;
        }
        .info-card, .doc-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 24px;
            margin-bottom: 16px;
        }
        .doc-card a {
            display: inline-block;
            margin-right: 8px;
            margin-top: 8px;
        }
        .viewer {
            width: 100%;
            height: 80vh;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../../components/header_component.php'; ?>

    <div class="page-container">
        <div class="doc-card">
            <h2 class="h4 mb-2">Terms &amp; Conditions and Privacy Policy</h2>
            <div class="text-muted mb-2">Last updated: <!-- update manually if needed --> 2025-01-01</div>
            <?php if (file_exists($docs['policy'])): ?>
                <div>
                    <a class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($publicUrls['policy']); ?>" target="_blank" rel="noopener">Open in new tab</a>
                    <a class="btn btn-success btn-sm ms-2" href="?doc=policy">View inline</a>
                    <a class="btn btn-secondary btn-sm ms-2" href="<?php echo htmlspecialchars($publicUrls['policy']); ?>" download>Download</a>
                </div>
            <?php else: ?>
                <div class="text-danger">Policy PDF not found on server.</div>
            <?php endif; ?>
        </div>

        <?php if ($selectedFileExists):
            // Map to public URL for embedding (do not expose filesystem path to client)
            $embedUrl = $publicUrls[$docKey];
        ?>
            <div class="doc-card">
                <h2 class="h5 mb-3">Viewing: Terms &amp; Conditions and Privacy Policy</h2>
                <div class="viewer">
                    <iframe src="<?php echo htmlspecialchars($embedUrl); ?>" title="Document viewer" width="100%" height="100%" style="border:0"></iframe>
                </div>
                <p class="text-muted mt-2 mb-0">If your browser cannot display PDF inline, use the download button above.</p>
            </div>
        <?php elseif ($docKey): ?>
            <div class="doc-card text-danger">Requested document not available.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>