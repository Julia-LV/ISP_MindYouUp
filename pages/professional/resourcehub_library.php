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

$currentProfessionalId = (int)($_SESSION['user_id'] ?? ($_SESSION['userid'] ?? 0));

$errors  = array();
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
        return 'Uncategorised';
    }
    return $skills[$key] ?? $key;
}

function current_page_url_without_query(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $clean = strtok($uri, '?');
    return $clean ?: 'resourcehub_library.php';
}

/*
 * Handle inline "share with" form submissions from the library.
 * Expects: share_action = share, resource_id, skill_key, patient_ids[]
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_action']) && $_POST['share_action'] === 'share') {
    $resourceId = isset($_POST['resource_id']) ? (int)$_POST['resource_id'] : 0;
    $skillKey   = isset($_POST['skill_key']) ? trim($_POST['skill_key']) : '';

    $patientIds = array();
    if (!empty($_POST['patient_ids']) && is_array($_POST['patient_ids'])) {
        foreach ($_POST['patient_ids'] as $pid) {
            $pid = (int)$pid;
            if ($pid > 0) {
                $patientIds[] = $pid;
            }
        }
    }

    if ($resourceId <= 0 || empty($patientIds)) {
        $errors[] = 'Please select at least one patient to share this resource with.';
    } else {
        $skillKeyVar = $skillKey !== '' ? $skillKey : '';
        $stmt = $conn->prepare(
            "INSERT INTO patient_resources (patient_id, resource_id, sent_by, skill_key, sent_at)
             SELECT ?, ?, ?, ?, NOW()
             FROM DUAL
             WHERE NOT EXISTS (
                 SELECT 1 FROM patient_resources
                 WHERE patient_id = ? AND resource_id = ? AND sent_by = ?
             )"
        );

        if ($stmt) {
            foreach ($patientIds as $pid) {
                $stmt->bind_param(
                    'iiisiii',
                    $pid,
                    $resourceId,
                    $currentProfessionalId,
                    $skillKeyVar,
                    $pid,
                    $resourceId,
                    $currentProfessionalId
                );
                $stmt->execute();
            }
            $stmt->close();
            $_SESSION['flash_success'] = 'Resource shared successfully.';
            header('Location: ' . current_page_url_without_query());
            exit;
        } else {
            $errors[] = 'Database error when sharing: ' . mysqli_error($conn);
        }
    }
}

// filters from GET
$filterCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
$filterTitle    = isset($_GET['q']) ? trim($_GET['q']) : '';

// ---------- LOAD LINKED PATIENTS ----------
$linkedPatients = array();
if ($currentProfessionalId) {
    $sql = "SELECT up.User_ID, up.First_Name, up.Last_Name
            FROM patient_professional_link ppl
            JOIN user_profile up ON up.User_ID = ppl.Patient_ID
            WHERE ppl.Professional_ID = ?
              AND LOWER(up.Role) = 'patient'
            ORDER BY up.Last_Name, up.First_Name";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $currentProfessionalId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $linkedPatients[(int)$row['User_ID']] = $row;
        }
        $stmt->close();
    }
}

// ---------- LOAD RESOURCES + SHARING INFO ----------
$resources = array();

if ($currentProfessionalId) {
    $params = array($currentProfessionalId);
    $types  = 'i';

    $where  = "pr.sent_by = ?";

    if ($filterCategory !== '') {
        $where .= " AND IFNULL(pr.skill_key,'') = ?";
        $params[] = $filterCategory;
        $types   .= 's';
    }

    if ($filterTitle !== '') {
        $where .= " AND rh.title LIKE ?";
        $params[] = '%' . $filterTitle . '%';
        $types   .= 's';
    }

    $sql = "SELECT 
                rh.id,
                rh.item_type,
                rh.title,
                rh.subtitle,
                rh.media_url,
                rh.content,
                rh.created_at,
                IFNULL(pr.skill_key,'') AS skill_key,
                GROUP_CONCAT(DISTINCT pr.patient_id) AS shared_patient_ids
            FROM resource_hub rh
            JOIN patient_resources pr ON pr.resource_id = rh.id
            WHERE $where
            GROUP BY rh.id, IFNULL(pr.skill_key,'')
            ORDER BY rh.created_at DESC, rh.id DESC";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $resources[] = $row;
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Resource library</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        :root { --creme:#FFF7E1; --coral:#F26647; --rosa:#F282A9; --verde:#005949; --preto:#231F20; --light-green:#E9F0E9; --border-soft:#F0E3CC; --radius-lg:20px; --radius-md:14px; }

        * { box-sizing:border-box; }
        body { margin:0; font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; background:var(--light-green); color:var(--preto); }
        a { text-decoration:none; color:inherit; }

        .shell { min-height:100vh; padding:32px 24px 40px; }
        .shell-inner { max-width:1200px; margin:0 auto; }

        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .top-title { font-size:28px; font-weight:700; color:var(--verde); }
        .top-subtitle { font-size:14px; color:#6b7280; margin-top:4px; }
        .top-left { display:flex; flex-direction:column; }

        .top-actions { display:flex; gap:10px; }

        .btn-back {
            padding:10px 20px;
            border-radius:999px;
            border:1px solid var(--verde);
            background:#ffffff;
            color:var(--verde);
            font-size:14px;
            font-weight:600;
            cursor:pointer;
        }
        .btn-back:hover { background:#e6f3ec; }

        .card { background:#fff; border-radius:var(--radius-lg); border:1px solid var(--border-soft); padding:18px 20px 20px; box-shadow:0 10px 28px rgba(0,0,0,0.06); }

        .flash-errors,.flash-success { padding:10px 12px; border-radius:12px; margin-bottom:14px; font-size:13px; }
        .flash-errors { background:#FEE2E2; border-left:4px solid #DC2626; }
        .flash-success { background:#DCFCE7; border-left:4px solid #16A34A; }

        .filters { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
        .filters select,
        .filters input[type="text"] {
            padding:8px 10px;
            border-radius:999px;
            border:1px solid #D1D5DB;
            background:#F9FAFB;
            font-size:13px;
        }
        .filters button {
            padding:8px 16px;
            border-radius:999px;
            border:none;
            background:var(--verde);
            color:#ffffff;
            font-size:13px;
            font-weight:600;
            cursor:pointer;
        }

        .library-list {
            display:flex;
            flex-direction:column;
            gap:10px;
            max-height:480px;
            overflow:auto;
            padding-right:4px;
        }

        .resource-row {
            border-radius:var(--radius-md);
            border:1px solid #E5E7EB;
            background:#F9FAFB;
            padding:10px 12px;
            display:flex;
            flex-direction:column;
            gap:6px;
        }

        .row-header {
            display:flex;
            justify-content:space-between;
            align-items:baseline;
            gap:8px;
        }

        .res-title {
            font-size:15px;
            font-weight:600;
            color:#111827;
        }
        .res-meta {
            font-size:12px;
            color:#6B7280;
        }

        .badge {
            display:inline-flex;
            align-items:center;
            padding:2px 8px;
            border-radius:999px;
            font-size:11px;
            border:1px solid #D1D5DB;
            background:#ffffff;
            margin-right:4px;
        }

        .share-cols {
            display:flex;
            gap:10px;
            margin-top:4px;
        }
        .share-col {
            flex:1;
            border-radius:12px;
            border:1px dashed #E5E7EB;
            background:#ffffff;
            padding:6px 8px;
        }
        .share-col-title {
            font-size:11px;
            font-weight:600;
            text-transform:uppercase;
            color:#6B7280;
            margin-bottom:4px;
        }

        .name-list {
            display:flex;
            flex-wrap:wrap;
            align-items:flex-start;
        }

        .name-pill {
            display:inline-block;
            margin:2px 4px 2px 0;
            padding:2px 8px;
            border-radius:999px;
            font-size:11px;
            background:#E5F3EC;
            color:#005949;
        }
        .name-pill.missing {
            background:#FFF7E1;
            color:#B45309;
        }

        .more-toggle {
            display:inline-flex;
            align-items:center;
            margin:4px 0 2px 0;
            padding:2px 8px;
            border-radius:999px;
            font-size:11px;
            border:1px dashed #D1D5DB;
            background:#ffffff;
            color:#6B7280;
            cursor:pointer;
        }

        .empty-text {
            font-size:11px;
            color:#9CA3AF;
        }

        .share-with-toggle {
            display:inline-flex;
            align-items:center;
            margin:6px 0 0;
            padding:6px 10px;
            border-radius:999px;
            border:1px solid var(--verde);
            background:#ffffff;
            color:var(--verde);
            font-size:11px;
            font-weight:600;
            cursor:pointer;
        }
        .share-with-toggle:hover {
            background:#E5F3EC;
        }

        .share-with-panel {
            margin-top:6px;
            padding:8px 10px;
            border-radius:12px;
            border:1px solid #E5E7EB;
            background:#F9FAFB;
            display:none;
        }
        .share-with-list {
            max-height:120px;
            overflow:auto;
            margin-bottom:6px;
        }
        .share-with-row {
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:3px 0;
            font-size:12px;
        }
        .share-with-name {
            flex:1;
            margin-right:8px;
            color:#374151;
        }
        .share-with-apply {
            padding:6px 12px;
            border-radius:999px;
            border:none;
            background:var(--verde);
            color:#ffffff;
            font-size:11px;
            font-weight:600;
            cursor:pointer;
        }
        .share-with-apply:hover {
            background:#00453F;
        }

        @media (max-width: 768px) {
            .shell { padding:20px 14px 28px; }
            .top-bar { flex-direction:column; align-items:flex-start; gap:12px; }
            .top-title { font-size:24px; }
            .library-list { max-height:420px; }
            .share-cols { flex-direction:column; }
        }
    </style>
</head>
<body>
<div class="shell">
    <div class="shell-inner">

        <header class="top-bar">
            <div class="top-left">
                <div class="top-title">Resource library</div>
                <div class="top-subtitle">See all files by category and which patients already have them.</div>
            </div>
            <div class="top-actions">
                <a href="resourcehub_professional.php">
                    <button type="button" class="btn-back">Back to Resource Hub</button>
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
            <form method="get" class="filters">
                <select name="category">
                    <option value="">All categories</option>
                    <?php foreach ($skills as $key => $label): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>" <?php if ($filterCategory === $key) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="q" placeholder="Search by title" value="<?php echo htmlspecialchars($filterTitle); ?>">

                <button type="submit">Filter</button>
            </form>

            <?php if (empty($resources)): ?>
                <p class="empty-text">No resources found for this professional yet.</p>
            <?php else: ?>
                <div class="library-list">
                    <?php
                    $previewCount = 4; // how many names to show before "+X more"
                    ?>
                    <?php foreach ($resources as $index => $r): ?>
                        <?php
                        $skillKey  = $r['skill_key'] ?? '';
                        $catLabel  = skill_label($skillKey, $skills);

                        $sharedIds = array();
                        if (!empty($r['shared_patient_ids'])) {
                            foreach (explode(',', $r['shared_patient_ids']) as $pidStr) {
                                $pid = (int)$pidStr;
                                if ($pid > 0) $sharedIds[$pid] = true;
                            }
                        }

                        $haveNames    = array();
                        $missingNames = array();
                        $missingIds   = array();

                        foreach ($linkedPatients as $pid => $p) {
                            $full = trim($p['First_Name'] . ' ' . $p['Last_Name']);
                            if (isset($sharedIds[$pid])) {
                                $haveNames[] = $full;
                            } else {
                                $missingNames[] = $full;
                                $missingIds[]   = $pid;
                            }
                        }

                        $createdDisplay = '';
                        if (!empty($r['created_at'])) {
                            $ts = strtotime($r['created_at']);
                            if ($ts) $createdDisplay = date('d.m.Y', $ts);
                        }

                        $resId = (int)$r['id'];
                        ?>
                        <article class="resource-row">
                            <div class="row-header">
                                <div>
                                    <div class="res-title"><?php echo htmlspecialchars($r['title']); ?></div>
                                    <div class="res-meta">
                                        <span class="badge"><?php echo htmlspecialchars(ucfirst($r['item_type'])); ?></span>
                                        <span class="badge"><?php echo htmlspecialchars($catLabel); ?></span>
                                        <?php if ($createdDisplay): ?>
                                            <span class="badge"><?php echo htmlspecialchars($createdDisplay); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="res-meta">
                                    <?php echo count($haveNames); ?> / <?php echo count($linkedPatients); ?> patients
                                </div>
                            </div>

                            <div class="share-cols">
                                <div class="share-col">
                                    <div class="share-col-title">Already shared with</div>
                                    <?php if ($haveNames): ?>
                                        <?php
                                        $visibleHave = array_slice($haveNames, 0, $previewCount);
                                        $hiddenHave  = array_slice($haveNames, $previewCount);
                                        ?>
                                        <div class="name-list">
                                            <?php foreach ($visibleHave as $n): ?>
                                                <span class="name-pill"><?php echo htmlspecialchars($n); ?></span>
                                            <?php endforeach; ?>
                                            <?php if ($hiddenHave): ?>
                                                <?php foreach ($hiddenHave as $n): ?>
                                                    <span class="name-pill extra-pill" data-group="have-<?php echo $resId; ?>" style="display:none;"><?php echo htmlspecialchars($n); ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($hiddenHave): ?>
                                            <button type="button"
                                                    class="more-toggle"
                                                    data-target-group="have-<?php echo $resId; ?>"
                                                    data-state="collapsed">
                                                +<?php echo count($hiddenHave); ?> more
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="empty-text">No patients yet.</div>
                                    <?php endif; ?>
                                </div>

                                <div class="share-col">
                                    <div class="share-col-title">Not shared yet</div>
                                    <?php if ($missingNames): ?>
                                        <?php
                                        $visibleMissing = array_slice($missingNames, 0, $previewCount);
                                        $hiddenMissing  = array_slice($missingNames, $previewCount);
                                        ?>
                                        <div class="name-list">
                                            <?php foreach ($visibleMissing as $n): ?>
                                                <span class="name-pill missing"><?php echo htmlspecialchars($n); ?></span>
                                            <?php endforeach; ?>
                                            <?php if ($hiddenMissing): ?>
                                                <?php foreach ($hiddenMissing as $n): ?>
                                                    <span class="name-pill missing extra-pill" data-group="missing-<?php echo $resId; ?>" style="display:none;"><?php echo htmlspecialchars($n); ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($hiddenMissing): ?>
                                            <button type="button"
                                                    class="more-toggle"
                                                    data-target-group="missing-<?php echo $resId; ?>"
                                                    data-state="collapsed">
                                                +<?php echo count($hiddenMissing); ?> more
                                            </button>
                                        <?php endif; ?>

                                        <!-- Share with button + inline panel -->
                                        <button type="button"
                                                class="share-with-toggle"
                                                data-panel-id="share-panel-<?php echo $resId; ?>">
                                            Share with…
                                        </button>

                                        <div class="share-with-panel" id="share-panel-<?php echo $resId; ?>">
                                            <form method="post">
                                                <input type="hidden" name="share_action" value="share">
                                                <input type="hidden" name="resource_id" value="<?php echo $resId; ?>">
                                                <input type="hidden" name="skill_key" value="<?php echo htmlspecialchars($skillKey); ?>">

                                                <div class="share-with-list">
                                                    <?php foreach ($linkedPatients as $pid => $p): ?>
                                                        <?php if (!isset($sharedIds[$pid])): ?>
                                                            <div class="share-with-row">
                                                                <span class="share-with-name">
                                                                    <?php echo htmlspecialchars($p['First_Name'] . ' ' . $p['Last_Name']); ?>
                                                                </span>
                                                                <input type="checkbox"
                                                                       name="patient_ids[]"
                                                                       value="<?php echo (int)$pid; ?>">
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>

                                                <button type="submit" class="share-with-apply">Apply sharing</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-text">All linked patients have this resource.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Show/hide extra pills
    var toggles = document.querySelectorAll('.more-toggle');
    toggles.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var group = btn.getAttribute('data-target-group');
            var state = btn.getAttribute('data-state') || 'collapsed';
            var pills = document.querySelectorAll('.extra-pill[data-group="' + group + '"]');
            var isCollapsed = state === 'collapsed';

            pills.forEach(function (pill) {
                pill.style.display = isCollapsed ? 'inline-block' : 'none';
            });

            if (isCollapsed) {
                btn.textContent = 'Show less';
                btn.setAttribute('data-state', 'expanded');
            } else {
                var extraCount = pills.length;
                btn.textContent = '+' + extraCount + ' more';
                btn.setAttribute('data-state', 'collapsed');
            }
        });
    });

    // Toggle "share with" panels
    var shareButtons = document.querySelectorAll('.share-with-toggle');
    shareButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var panelId = btn.getAttribute('data-panel-id');
            var panel   = document.getElementById(panelId);
            if (!panel) return;
            var isShown = panel.style.display === 'block';
            panel.style.display = isShown ? 'none' : 'block';
        });
    });
});
</script>
</body>
</html>
