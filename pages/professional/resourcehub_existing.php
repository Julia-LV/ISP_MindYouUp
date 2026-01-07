<?php
session_start();
require_once __DIR__ . '/../../config.php';

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }
}

// ---------- AUTH GUARD ----------
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

$role = $_SESSION['role'] ?? '';
if (strtolower($role) !== 'professional') {
    header('Location: ../patient/home_patient.php');
    exit;
}

/*
 * IMPORTANT: support both keys so it works
 */
$currentProfessionalId = (int)($_SESSION['user_id'] ?? ($_SESSION['userid'] ?? 0));

$errors = array();
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$skills = array(
    'competing_behaviours'   => 'Competing Behaviours',
    'habit_reversal'         => 'Habit Reversal Training',
    'anxiety_management'     => 'Anxiety Management',
    'pmr_training'           => 'Progressive Muscle Relaxation Training',
);

function skill_label(?string $key, array $skills): string
{
    if ($key === null || $key === '') {
        return '';
    }
    return $skills[$key] ?? $key;
}

function current_page_url_without_query(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $clean = strtok($uri, '?');
    return $clean ?: 'resourcehub_list.php';
}

function delete_resource(mysqli $conn, int $id, array &$errors, string &$success): void
{
    if ($id <= 0) {
        return;
    }

    $media = null; $image = null; $thumb = null;
    if ($stmt = mysqli_prepare($conn, "SELECT media_url, image_url, thumb_url FROM resource_hub WHERE id = ?")) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $media = $row['media_url'] ?? null;
            $image = $row['image_url'] ?? null;
            $thumb = $row['thumb_url'] ?? null;
        }
        mysqli_stmt_close($stmt);
    }

    foreach (array($media, $image, $thumb) as $stored) {
        if (!empty($stored)) {
            $stored = trim($stored);
            $rel = str_starts_with($stored, 'uploads/')
                ? $stored
                : 'uploads/' . ltrim($stored, '/');
            $fsPath = __DIR__ . '/../../' . $rel;
            if (file_exists($fsPath) && is_file($fsPath)) {
                @unlink($fsPath);
            }
        }
    }

    if ($stmt = mysqli_prepare($conn, "DELETE FROM patient_resources WHERE resource_id = ?")) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($stmt = mysqli_prepare($conn, "DELETE FROM resource_hub WHERE id = ?")) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Resource and all patient links deleted successfully.';
        } else {
            $errors[] = 'Database error when deleting: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

/* DELETE via ?delete=ID */
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    delete_resource($conn, $deleteId, $errors, $success);

    if (empty($errors) && !empty($success)) {
        $_SESSION['flash_success'] = $success;
        header('Location: ' . current_page_url_without_query());
        exit;
    }
}

/* ---------- LOAD EXISTING ITEMS FOR THIS PROFESSIONAL ONLY ---------- */
$items = array();
if ($currentProfessionalId) {
    $sql = "SELECT 
                rh.*,
                GROUP_CONCAT(DISTINCT NULLIF(pr.skill_key, '') ORDER BY pr.skill_key SEPARATOR ',') AS skill_keys,
                GROUP_CONCAT(
                    DISTINCT CONCAT(up.First_Name, ' ', up.Last_Name)
                    ORDER BY up.Last_Name, up.First_Name
                    SEPARATOR ', '
                ) AS shared_with
            FROM resource_hub rh
            JOIN patient_resources pr ON pr.resource_id = rh.id
            JOIN user_profile up       ON up.User_ID = pr.patient_id
            WHERE pr.sent_by = ?
            GROUP BY rh.id
            ORDER BY rh.created_at DESC, rh.id DESC";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $currentProfessionalId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Existing Resources</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        :root { --verde:#005949; --preto:#111827; --light-green:#E9F0E9; --border-soft:#E5E7EB; --radius-lg:18px; }

        * { box-sizing:border-box; }

        body {
            margin:0;
            font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            background:var(--light-green);
            color:var(--preto);
        }

        a { text-decoration:none; color:inherit; }

        .shell {
            min-height:100vh;
            padding:32px 24px 40px;
        }

        .shell-inner {
            max-width:1200px;
            margin:0 auto;
        }

        .top-bar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }

        .top-title {
            font-size:28px;
            font-weight:700;
            color:var(--verde);
        }

        .top-subtitle {
            font-size:14px;
            color:#6b7280;
            margin-top:4px;
        }

        .top-left {
            display:flex;
            flex-direction:column;
            gap:2px;
        }

        .top-actions { display:flex; align-items:center; gap:10px; }

        .btn-green {
            padding:10px 20px;
            border-radius:999px;
            border:1px solid var(--verde);
            background:var(--verde);
            color:#ffffff;
            font-size:14px;
            font-weight:600;
            cursor:pointer;
        }

        .btn-green:hover { background:#00453F; }

        .card {
            background:#ffffff;
            border-radius:var(--radius-lg);
            border:1px solid var(--border-soft);
            padding:18px 20px 20px;
            box-shadow:0 10px 28px rgba(0,0,0,0.06);
        }

        .flash-errors,.flash-success {
            padding:10px 12px;
            border-radius:12px;
            margin-bottom:14px;
            font-size:13px;
        }
        .flash-errors { background:#FEE2E2; border-left:4px solid #DC2626; }
        .flash-success { background:#DCFCE7; border-left:4px solid #16A34A; }

        .list-header {
            display:flex;
            justify-content:flex-end;
            font-size:12px;
            color:#6B7280;
            margin-bottom:6px;
        }

        .list-count {
            padding:3px 8px;
            border-radius:999px;
            background:#F3F4F6;
        }

        /* fixed-height card with internal scroll on ALL screen sizes */
        .resource-scroll {
            max-height:420px;   /* adjust height if needed */
            overflow-y:auto;
            padding-right:4px;
        }

        .resource-grid {
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:10px 14px;
        }

        .resource-card {
            border-radius:14px;
            border:1px solid #E5E7EB;
            padding:10px 12px 12px;
            background:#F9FAFB;
            display:flex;
            flex-direction:column;
            gap:6px;
            min-width:0;
        }

        .resource-header {
            display:flex;
            justify-content:space-between;
            align-items:baseline;
            gap:8px;
        }

        .resource-title {
            margin:0;
            font-size:14px;
            font-weight:600;
            color:#111827;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .resource-date {
            font-size:11px;
            color:#4B5563;
            padding:2px 7px;
            border-radius:999px;
            background:#E5F3EC;
            white-space:nowrap;
        }

        .resource-meta {
            display:flex;
            flex-direction:column;
            gap:3px;
            font-size:12px;
        }

        .meta-row {
            display:flex;
            justify-content:space-between;
            gap:6px;
        }

        .meta-label {
            color:#6B7280;
            flex:0 0 auto;
        }

        .meta-value {
            flex:1 1 auto;
            text-align:right;
            color:#111827;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .meta-muted {
            color:#9CA3AF;
        }

        .pill-link {
            border:none;
            background:#E5F3EC;
            color:#005949;
            border-radius:999px;
            padding:2px 7px;
            font-size:11px;
            cursor:pointer;
        }

        .shared-names {
            display:none;
            margin:4px 0 0;
            padding:0;
            list-style:none;
            text-align:right;
            font-size:11px;
            color:#374151;
        }

        .shared-names li {
            white-space:nowrap;
        }

        .shared-names.show {
            display:block;
        }

        .resource-actions {
            margin-top:4px;
            display:flex;
            justify-content:flex-end;
        }

        .btn-delete {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:4px 12px;
            border-radius:999px;
            border:1px solid #DC2626;
            background:#FEE2E2;
            color:#B91C1C;
            font-size:12px;
            font-weight:600;
            text-decoration:none;
            cursor:pointer;
            transition:
                background-color 0.15s ease,
                color 0.15s ease,
                box-shadow 0.15s ease,
                transform 0.05s ease;
        }

        .btn-delete:hover {
            background:#DC2626;
            color:#ffffff;
            box-shadow:0 0 0 1px rgba(220,38,38,0.12);
        }

        .btn-delete:active { transform:scale(0.97); }

        .no-resources { font-size:13px; color:#6B7280; padding-top:4px; }

        @media (max-width: 768px) {
            .shell { padding:20px 14px 28px; }
            .shell-inner { max-width:100%; }
            .top-bar { flex-direction:column; align-items:flex-start; gap:12px; }
            .top-actions { align-self:stretch; justify-content:flex-end; }
            .top-title { font-size:24px; }

            .resource-grid {
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>
<div class="shell">
    <div class="shell-inner">

        <header class="top-bar">
            <div class="top-left">
                <div class="top-title">Existing resources</div>
                <div class="top-subtitle">Items already sent to your patients</div>
            </div>
            <div class="top-actions">
                <a href="resourcehub_professional.php">
                    <button type="button" class="btn-green">Back to Resource Hub</button>
                </a>
            </div>
        </header>

        <?php if ($errors): ?>
            <div class="flash-errors">
                <?php foreach ($errors as $e): ?><div><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <section class="card">
            <?php if ($items): ?>
                <div class="list-header">
                    <span class="list-count"><?php echo count($items); ?> resource(s)</span>
                </div>

                <div class="resource-scroll">
                    <div class="resource-grid">
                        <?php foreach ($items as $item): ?>
                            <?php
                            $skillText = '';
                            if (!empty($item['skill_keys'])) {
                                $labels = array();
                                $keys = array_unique(array_filter(explode(',', $item['skill_keys'])));
                                foreach ($keys as $k) {
                                    $label = skill_label($k, $skills);
                                    if ($label !== '') {
                                        $labels[] = $label;
                                    }
                                }
                                if ($labels) {
                                    $skillText = implode(', ', $labels);
                                }
                            }
                            $typeLabel = $skillText;

                            $sharedWithStr = $item['shared_with'] ?? '';
                            $sharedNames   = array_filter(array_map('trim', explode(',', $sharedWithStr)));

                            $createdAt = $item['created_at'] ?? '';
                            $createdDisplay = '';
                            if ($createdAt) {
                                $ts = strtotime($createdAt);
                                if ($ts) {
                                    $createdDisplay = date('d.m.Y', $ts);
                                }
                            }
                            ?>
                            <article class="resource-card">
                                <header class="resource-header">
                                    <h3 class="resource-title" title="<?php echo htmlspecialchars($item['title']); ?>">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h3>
                                    <?php if ($createdDisplay): ?>
                                        <span class="resource-date"><?php echo htmlspecialchars($createdDisplay); ?></span>
                                    <?php endif; ?>
                                </header>

                                <div class="resource-meta">
                                    <div class="meta-row">
                                        <span class="meta-label">Type</span>
                                        <span class="meta-value" title="<?php echo htmlspecialchars($typeLabel); ?>">
                                            <?php echo htmlspecialchars($typeLabel); ?>
                                        </span>
                                    </div>

                                    <div class="meta-row">
                                        <span class="meta-label">Shared with</span>
                                        <span class="meta-value">
                                            <?php if (empty($sharedNames)): ?>
                                                <span class="meta-muted">Not shared</span>
                                            <?php else: ?>
                                                <button type="button" class="pill-link" onclick="this.nextElementSibling.classList.toggle('show')">
                                                    <?php echo count($sharedNames); ?> patient(s)
                                                </button>
                                                <ul class="shared-names">
                                                    <?php foreach ($sharedNames as $name): ?>
                                                        <li><?php echo htmlspecialchars($name); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>

                                <footer class="resource-actions">
                                    <a href="?delete=<?php echo (int)$item['id']; ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Delete this resource, its file, and all patient links?');">
                                        Delete
                                    </a>
                                </footer>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="no-resources">No resources yet. Once you send items to patients, they will appear here.</p>
            <?php endif; ?>
        </section>
    </div>
</div>
</body>
</html>
