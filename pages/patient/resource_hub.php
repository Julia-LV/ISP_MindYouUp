<?php
session_start();
require_once __DIR__ . '/../../config.php';

// If download requested, stream PDF blob and exit (single-page handling)
if (!empty($_GET['download'])) {
    $did = (int)$_GET['download'];
    $stmtD = mysqli_prepare($conn, "SELECT RESOURCE_PDF FROM resource_hub WHERE RESOURCE_ID = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtD, 'i', $did);
    mysqli_stmt_execute($stmtD);
    mysqli_stmt_bind_result($stmtD, $blob);
    if (mysqli_stmt_fetch($stmtD) && $blob !== null) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="resource_' . $did . '.pdf"');
        echo $blob;
        mysqli_stmt_close($stmtD);
        exit;
    }
    mysqli_stmt_close($stmtD);
    http_response_code(404);
    echo 'Not found';
    exit;
}

// Determine patient id to show resources for (prefer explicit param, fallback to session)
$patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : ($_SESSION['patient_id'] ?? null);
// Placeholder: do not enforce user verification for now. If no patient_id is provided
// we'll show all resources for demo purposes. Replace with proper auth later.
$showingAll = false;
if (!$patientId) {
    $showingAll = true;
}

// Detect available columns in resource_hub to support pdf/link/video fields flexibly
$cols = [];
$sqlCols = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'resource_hub'";
$resCols = mysqli_query($conn, $sqlCols);
if ($resCols) {
    while ($r = mysqli_fetch_assoc($resCols)) {
        $cols[] = $r['COLUMN_NAME'];
    }
}

// Build select - include commonly expected columns if present
$selectCols = [];
foreach (['RESOURCE_ID','USE_USER_ID','PATIENT_ID','PROFESSIONAL_ID','RESOURCE_PDF','RESOURCE_URL','RESOURCE_TYPE','RESOURCE_NAME','RESOURCE_TITLE','RESOURCE_DESC'] as $c) {
    if (in_array($c, $cols)) $selectCols[] = $c;
}
if (empty($selectCols)) {
    // fallback - select all
    $select = "*";
} else {
    $select = implode(',', $selectCols);
}

$res = false;
if ($showingAll) {
    $stmt = mysqli_prepare($conn, "SELECT $select FROM resource_hub ORDER BY RESOURCE_ID DESC");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $stmt = mysqli_prepare($conn, "SELECT $select FROM resource_hub WHERE PATIENT_ID = ? ORDER BY RESOURCE_ID DESC");
    mysqli_stmt_bind_param($stmt, 'i', $patientId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
}
$resources = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) $resources[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Resource Hub</title>
    <style>
        :root{--bg-creme:#FFF7E1;--accent-orange:#F26647;--accent-green:#005949;--radius:10px}
        body{font-family:Arial,Helvetica,sans-serif;background:var(--bg-creme);margin:0;padding:20px;color:#0b2a24}
        .wrap{max-width:980px;margin:0 auto}
        .header{display:flex;align-items:center;gap:12px;margin-bottom:18px}
        .back{color:var(--accent-green);text-decoration:none}
        h1{margin:0;color:var(--accent-green)}

        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}
        .card{background:#fff;border-radius:var(--radius);padding:14px;box-shadow:0 6px 16px rgba(0,0,0,0.06);display:flex;flex-direction:column;gap:10px}
        .card .title{font-weight:600;color:#102b23}
        .card .meta{color:#666;font-size:.9rem}
        .card .actions{margin-top:auto;display:flex;gap:8px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:0;color:#fff;background:linear-gradient(180deg,var(--accent-orange),#e6553e);text-decoration:none}
        .btn.secondary{background:linear-gradient(180deg,var(--accent-green),#00463f)}

        .pdf-icon{width:48px;height:48px;border-radius:6px;background:#f6f6f6;display:flex;align-items:center;justify-content:center;font-weight:700;color:#c33}
        .link-preview{word-break:break-all;color:var(--accent-green)}
        .video-wrap iframe{width:100%;height:180px;border-radius:6px}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <a class="back" href="../common/patient_profile.php">&larr; Back to profile</a>
            <h1>Resource Hub</h1>
        </div>
        <?php if (!empty($showingAll)): ?>
            <div class="note" style="margin-bottom:12px;padding:8px;background:#fff8f4;border-left:4px solid var(--accent-orange);border-radius:6px">Authentication placeholder: showing all resources for demo purposes.</div>
        <?php endif; ?>

        <?php if (empty($resources)): ?>
            <p>No resources have been added by your professional yet.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($resources as $r): ?>
                    <div class="card">
                        <?php
                        // Determine title/desc
                        $title = $r['RESOURCE_TITLE'] ?? $r['RESOURCE_NAME'] ?? ('Resource #' . ($r['RESOURCE_ID'] ?? ''));
                        $desc = $r['RESOURCE_DESC'] ?? '';
                        echo '<div class="title">' . htmlspecialchars($title) . '</div>';
                        if ($desc) echo '<div class="meta">' . htmlspecialchars($desc) . '</div>';

                        // Detect type
                        $type = null;
                        if (!empty($r['RESOURCE_PDF'])) $type = 'pdf';
                        if (!empty($r['RESOURCE_URL'])) {
                            $url = trim($r['RESOURCE_URL']);
                            // crude video detection
                            if (strpos($url,'youtube.com') !== false || strpos($url,'youtu.be') !== false) $type = 'video';
                            else $type = 'link';
                        }
                        // If RESOURCE_TYPE column exists, prefer it
                        if (!empty($r['RESOURCE_TYPE'])) $type = strtolower($r['RESOURCE_TYPE']);

                        if ($type === 'pdf'):
                            $id = (int)$r['RESOURCE_ID'];
                        ?>
                            <div style="display:flex;gap:10px;align-items:center">
                                <div class="pdf-icon">PDF</div>
                                <div style="flex:1">
                                    <div class="meta">PDF document</div>
                                </div>
                            </div>
                            <div class="actions">
                                <a class="btn" href="?download=<?php echo $id ?>" target="_blank">Open / Download</a>
                            </div>
                        <?php elseif ($type === 'video'):
                            $url = $r['RESOURCE_URL'];
                            // simple youtube embed handling
                            $embed = '';
                            if (preg_match('#(?:v=|youtu\.be/)([A-Za-z0-9_-]{6,})#', $url, $m)) {
                                $vid = $m[1];
                                $embed = 'https://www.youtube.com/embed/' . $vid;
                            }
                        ?>
                            <?php if ($embed): ?>
                                <div class="video-wrap"><iframe src="<?php echo htmlspecialchars($embed) ?>" frameborder="0" allowfullscreen></iframe></div>
                            <?php else: ?>
                                <div class="meta">Video</div>
                            <?php endif; ?>
                            <div class="actions">
                                <a class="btn" href="<?php echo htmlspecialchars($url) ?>" target="_blank">Open video</a>
                            </div>
                        <?php elseif ($type === 'link'):
                            $url = $r['RESOURCE_URL'];
                        ?>
                            <div class="meta">Link</div>
                            <div class="link-preview"><?php echo htmlspecialchars($url) ?></div>
                            <div class="actions">
                                <a class="btn" href="<?php echo htmlspecialchars($url) ?>" target="_blank">Open link</a>
                                <a class="btn secondary" href="?save=<?php echo (int)$r['RESOURCE_ID'] ?>">Save</a>
                            </div>
                        <?php else: ?>
                            <div class="meta">Unknown resource type</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
