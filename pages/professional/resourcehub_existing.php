<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Fallback for PHP versions without str_starts_with
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }
}

// ---------- AUTH GUARD: only logged‑in professionals ----------
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
 * whether the session uses user_id or userid.
 */
$currentProfessionalId = (int)($_SESSION['user_id'] ?? ($_SESSION['userid'] ?? 0));

$errors  = array();

// pull any flash success message from previous request (then clear it)
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// Fixed skills list: same for everyone
$skills = array(
    'competing_behaviours'   => 'Competing Behaviours',
    'habit_reversal'         => 'Habit Reversal Training',
    'anxiety_management'     => 'Anxiety Management',
    'pmr_training'           => 'Progressive Muscle Relaxation Training',
);

// Helper to turn key into label
function skill_label(?string $key, array $skills): string
{
    if ($key === null || $key === '') {
        return '';
    }
    return $skills[$key] ?? $key;
}

// helper: current page URL without query string (for PRG redirects)
function current_page_url_without_query(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $clean = strtok($uri, '?');
    return $clean ?: 'resourcehub_list.php';
}

/**
 * Delete a resource, its files and all patient links (silent if already gone).
 */
function delete_resource(mysqli $conn, int $id, array &$errors, string &$success): void
{
    if ($id <= 0) {
        return;
    }

    // 1) Try to get file paths (ok if not found)
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

    // 2) Delete files (accepts "filename" or "uploads/filename")
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

    // 3) Delete patient links
    if ($stmt = mysqli_prepare($conn, "DELETE FROM patient_resources WHERE resource_id = ?")) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // 4) Delete resource row
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

// DELETE via ?delete=ID  (PRG with flash message)
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
            ORDER BY rh.sort_order, rh.id";
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
        :root { --verde:#005949; --preto:#231F20; --light-green:#E9F0E9; --border-soft:#F0E3CC; --radius-lg:20px; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; background:var(--light-green); color:var(--preto); }
        a { text-decoration:none; color:inherit; }
        .shell { min-height:100vh; padding:28px 20px 36px; }
        .shell-inner { max-width:1100px; margin:0 auto; }

        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
        .top-title { font-size:24px; font-weight:700; color:var(--verde); }
        .top-subtitle { font-size:13px; color:#6b7280; margin-top:4px; }
        .top-left { display:flex; flex-direction:column; gap:2px; }

        .top-actions { display:flex; align-items:center; gap:10px; }
        .btn-green {
            padding:8px 18px;
            border-radius:999px;
            border:1px solid var(--verde);
            background:var(--verde);
            color:#ffffff;
            font-size:13px;
            font-weight:600;
            cursor:pointer;
        }
        .btn-green:hover { background:#00453F; }

        .card { background:#fff; border-radius:var(--radius-lg); border:1px solid var(--border-soft); padding:18px 20px 20px; box-shadow:0 10px 28px rgba(0,0,0,0.07); }

        .flash-errors,.flash-success { padding:10px 12px; border-radius:12px; margin-bottom:14px; font-size:13px; }
        .flash-errors { background:#FEE2E2; border-left:4px solid #DC2626; }
        .flash-success { background:#DCFCE7; border-left:4px solid #16A34A; }

        .table-wrapper {
            margin-top:8px;
            border-radius:var(--radius-lg);
            overflow:hidden;
        }
        .table-scroll {
            max-height:360px;
            overflow-y:auto;
        }

        table { width:100%; border-collapse:collapse; font-size:12px; table-layout:fixed; }
        th,td { padding:7px 8px; text-align:left; border-bottom:1px solid #E5E7EB; vertical-align:middle; }
        th { background:#E2F3EB; color:var(--verde); font-weight:600; }
        tr:last-child td { border-bottom:none; }

        /* Column widths: Title / Skill / Shared with / Media / Action */
        th:nth-child(1), td:nth-child(1) { width:28%; }
        th:nth-child(2), td:nth-child(2) { width:18%; }
        th:nth-child(3), td:nth-child(3) { width:28%; }
        th:nth-child(4), td:nth-child(4) { width:26%; }
        th:nth-child(5), td:nth-child(5) {
            width:80px;
            text-align:right;
            white-space:nowrap;
        }

        td:nth-child(2),
        td:nth-child(3),
        td:nth-child(4) {
            white-space:normal;
            overflow-wrap:anywhere;
        }

        .actions a {
            font-size:12px;
            color:#B91C1C;
            font-weight:600;
            text-decoration:none;
        }

        .btn-delete {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:4px 10px;
            border-radius:999px;
            border:1px solid #DC2626;
            background:#FEE2E2;
            color:#B91C1C;
            font-size:11px;
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
            <div class="table-wrapper">
                <?php if ($items): ?>
                    <div class="table-scroll">
                        <table>
                            <thead>
                            <tr>
                                <th>Title</th>
                                <th>Skill</th>
                                <th>Shared with</th>
                                <th>Media</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php
                                // Build plain text list of skill labels
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
                                $sharedWith = $item['shared_with'] ?? '';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo htmlspecialchars($skillText); ?></td>
                                    <td><?php echo htmlspecialchars($sharedWith); ?></td>
                                    <td><?php echo htmlspecialchars($item['media_url'] ?? ''); ?></td>
                                    <td class="actions">
                                        <a href="?delete=<?php echo (int)$item['id']; ?>"
                                           class="btn-delete"
                                           onclick="return confirm('Delete this resource, its file, and all patient links?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-resources">No resources yet. Once you send items to patients, they will appear here.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
</body>
</html>