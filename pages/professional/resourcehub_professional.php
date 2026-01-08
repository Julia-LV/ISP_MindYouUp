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


// ---------- SETUP ----------
$errors  = array();


// pull any flash success message from previous request (then clear it)
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$selectedSharePatients = array();


// Fixed categories list: same for everyone
$skills = array(
    'competing_behaviours'   => 'Competing Behaviours',
    'habit_reversal'         => 'Habit Reversal Training',
    'anxiety_management'     => 'Anxiety Management',
    'pmr_training'           => 'Progressive Muscle Relaxation Training',
);


// Helper to turn key into label (guarded to avoid redeclare)
if (!function_exists('skill_label')) {
    function skill_label(?string $key, array $skills): string
    {
        if ($key === null || $key === '') {
            return '';
        }
        return $skills[$key] ?? $key;
    }
}


/*
   Upload base folder (filesystem)
*/
$uploadBase = __DIR__ . '/../../uploads/';
if (!is_dir($uploadBase)) {
    mkdir($uploadBase, 0777, true);
}


/**
 * Handle a file upload and return the stored value for media_url.
 * Only the filename is stored in DB; pages prepend "uploads/" when rendering.
 */
function handle_upload(string $fieldName, string $uploadBase): ?string
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $original = $_FILES[$fieldName]['name'];
    $tmpPath  = $_FILES[$fieldName]['tmp_name'];

    $ext      = pathinfo($original, PATHINFO_EXTENSION);
    $base     = pathinfo($original, PATHINFO_FILENAME);
    $safeBase = preg_replace('/[^a-zA-Z0-9-_]/', '_', $base);
    $filename = $safeBase . '_' . time() . ($ext ? '.' . $ext : '');

    $destPath = $uploadBase . $filename;
    if (!move_uploaded_file($tmpPath, $destPath)) {
        return null;
    }

    return $filename;
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


/* ---------- LOAD LINKED PATIENTS (for Share to) ---------- */
$linkedPatients = array();
if ($currentProfessionalId) {
    $sql = "SELECT up.User_ID, up.First_Name, up.Last_Name
            FROM patient_professional_link ppl
            JOIN user_profile up ON up.User_ID = ppl.Patient_ID
            WHERE ppl.Professional_ID = ?
              AND LOWER(up.Role) = 'patient'
            ORDER BY up.User_ID DESC, up.Last_Name, up.First_Name";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $currentProfessionalId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $linkedPatients[] = $row;
        }
        $stmt->close();
    }
}


/* ---------- HANDLE ACTIONS ---------- */


// helper: current page URL without query string (for PRG redirects)
function current_page_url_without_query(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $clean = strtok($uri, '?');
    return $clean ?: 'resourcehub_professional.php';
}


// DELETE via ?delete=ID
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    delete_resource($conn, $deleteId, $errors, $success);

    if (empty($errors) && !empty($success)) {
        $_SESSION['flash_success'] = $success;
        header('Location: ' . current_page_url_without_query());
        exit;
    }
}


// CREATE via POST  (PRG on success)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_type  = $_POST['item_type'] ?? '';
    $title      = trim($_POST['title'] ?? '');
    $subtitle   = trim($_POST['subtitle'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $sort_order = 0;

    // Category key (required when type=skill)
    $skill_key = isset($_POST['skill_key']) ? trim($_POST['skill_key']) : '';

    // Selected patients
    if (isset($_POST['share_patients']) && is_array($_POST['share_patients'])) {
        $selectedSharePatients = array_map('intval', $_POST['share_patients']);
    } else {
        $selectedSharePatients = array();
    }

    if (!empty($linkedPatients) && empty($selectedSharePatients)) {
        $errors[] = 'Please select at least one patient to share this resource with.';
    }

    if (!in_array($item_type, array('strategy', 'skill', 'article'), true)) {
        $errors[] = 'Invalid item type.';
    }

    // Validate category selection, and auto‑use label as title when appropriate
    if ($item_type === 'skill') {
        if ($skill_key === '') {
            $errors[] = 'Please choose which Category this file belongs to.';
        } elseif (!array_key_exists($skill_key, $skills)) {
            $errors[] = 'Invalid category selection.';
        } else {
            if ($title === '') {
                $title = $skills[$skill_key]; // default title from category
            }
        }
    } elseif ($skill_key !== '' && !array_key_exists($skill_key, $skills)) {
        $errors[] = 'Invalid category selection.';
    }

    // For non‑skill items, title is still required
    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    $media_url = handle_upload('media_file', $uploadBase);
    $image_url = null;
    $thumb_url = null;

    if (empty($errors)) {

        // ---------- 1) Try to reuse an existing resource (ignore media_url) ----------
        $existingId = 0;
        $sqlFind = "SELECT rh.id
                    FROM resource_hub rh
                    JOIN patient_resources pr ON pr.resource_id = rh.id
                    WHERE pr.sent_by = ?
                      AND rh.item_type = ?
                      AND rh.title = ?
                      AND IFNULL(pr.skill_key,'') = IFNULL(?, '')
                    ORDER BY rh.id DESC
                    LIMIT 1";
        if ($stmtFind = $conn->prepare($sqlFind)) {
            $stmtFind->bind_param(
                'isss',
                $currentProfessionalId,
                $item_type,
                $title,
                $skill_key
            );
            $stmtFind->execute();
            $resFind = $stmtFind->get_result();
            if ($rowF = $resFind->fetch_assoc()) {
                $existingId = (int)$rowF['id'];
            }
            $stmtFind->close();
        }

        if ($existingId > 0) {
            // Reuse existing row
            $newResourceId = $existingId;
        } else {
            // ---------- 2) Create a new resource ----------
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO resource_hub (item_type, title, subtitle, content, media_url, image_url, thumb_url, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $stmt,
                'sssssssi',
                $item_type, $title, $subtitle, $content, $media_url, $image_url, $thumb_url, $sort_order
            );
            if (mysqli_stmt_execute($stmt)) {
                $newResourceId = mysqli_insert_id($conn);
            } else {
                $errors[] = 'Database error: ' . mysqli_error($conn);
                $newResourceId = 0;
            }
            mysqli_stmt_close($stmt);
        }

        // ---------- 3) Insert patient links (avoid duplicates) ----------
        if ($currentProfessionalId && $newResourceId && !empty($selectedSharePatients) && empty($errors)) {
            $ins = $conn->prepare(
                "INSERT INTO patient_resources (patient_id, resource_id, sent_by, skill_key, sent_at)
                 SELECT ?, ?, ?, ?, NOW()
                 FROM DUAL
                 WHERE NOT EXISTS (
                     SELECT 1 FROM patient_resources
                     WHERE patient_id = ? AND resource_id = ? AND sent_by = ?
                 )"
            );
            $skillKeyVar = $skill_key !== '' ? $skill_key : '';

            foreach ($selectedSharePatients as $pid) {
                $pid = (int)$pid;
                if ($pid <= 0) continue;
                $ins->bind_param(
                    'iiisiii',
                    $pid,
                    $newResourceId,
                    $currentProfessionalId,
                    $skillKeyVar,
                    $pid,
                    $newResourceId,
                    $currentProfessionalId
                );
                $ins->execute();
            }
            $ins->close();
        }

        if (empty($errors)) {
            $_SESSION['flash_success'] = 'Resource saved successfully.';
            header('Location: ' . current_page_url_without_query());
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Resource Hub Admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <!-- Tailwind needed for navbar.php -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root { --creme:#FFF7E1; --coral:#F26647; --rosa:#F282A9; --verde:#005949; --preto:#231F20; --light-green:#E9F0E9; --border-soft:#F0E3CC; --radius-lg:20px; --radius-md:14px; }
        * { box-sizing: border-box; }
        body {
            margin:0;
            font-family: system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            background:var(--light-green);
            color:var(--preto);
            padding-left: 80px; /* sidebar width (w-20) */
        }
        a { text-decoration:none; color:inherit; }

        .shell { min-height:100vh; padding:32px 24px 40px; }
        .shell-inner { max-width:1200px; margin:0 auto; }

        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .top-title { font-size:28px; font-weight:700; color:var(--verde); }
        .top-subtitle { font-size:14px; color:#6b7280; margin-top:4px; }
        .top-left { display:flex; flex-direction:column; }

        .top-actions { display:flex; align-items:center; gap:10px; }

        .btn-logout {
            padding:10px 20px;
            border-radius:999px;
            font-size:14px;
            font-weight:600;
            cursor:pointer;
            border:1px solid var(--verde);
            background:#fff;
            color:var(--verde);
        }
        .btn-logout:hover { background:#e6f3ec; }

        .flash-errors,.flash-success { padding:10px 12px; border-radius:12px; margin-bottom:14px; font-size:13px; }
        .flash-errors { background:#FEE2E2; border-left:4px solid #DC2626; }
        .flash-success { background:#DCFCE7; border-left:4px solid #16A34A; }

        .card { background:#fff; border-radius:var(--radius-lg); border:1px solid var(--border-soft); padding:22px 20px 24px; box-shadow:0 10px 28px rgba(0,0,0,0.07); }

        form label { display:block; margin-top:12px; font-size:14px; font-weight:500; color:#374151; }

        input[type="text"], textarea, select {
            width:100%;
            margin-top:6px;
            padding:10px 11px;
            border-radius:12px;
            border:1px solid #D1D5DB;
            background:#F9FAFB;
            font-size:14px;
            font-family:inherit;
        }
        textarea { min-height:100px; resize:vertical; }
        input[type="file"] { margin-top:6px; font-size:13px; }
        .hint { font-size:12px; color:#6b7280; margin-top:4px; }

        .btn-primary {
            padding:11px 24px;
            border-radius:999px;
            border:none;
            cursor:pointer;
            background:var(--verde);
            color:#fff;
            font-size:15px;
            font-weight:600;
            box-shadow:0 7px 16px rgba(0,0,0,0.15);
        }
        .btn-primary:hover { background:#00453F; }

        .btn-secondary-link {
            display:inline-flex;
            padding:11px 24px;
            border-radius:999px;
            border:1px solid var(--verde);
            background:#ffffff;
            color:var(--verde);
            font-size:14px;
            font-weight:600;
            text-decoration:none;
        }
        .btn-secondary-link:hover { background:#e6f3ec; }

        .btn-library-link {
            display:inline-flex;
            padding:11px 24px;
            border-radius:999px;
            border:1px solid var(--coral);
            background:#FFF7E1;
            color:#F26647;
            font-size:14px;
            font-weight:600;
            text-decoration:none;
            box-shadow:0 7px 16px rgba(0,0,0,0.06);
        }
        .btn-library-link:hover { background:#FFE9C2; }

        .actions-row {
            display:flex;
            gap:15px;
            align-items:center;
            margin-top:24px;
            flex-wrap:wrap;
        }

        .share-header { display:flex; justify-content:space-between; align-items:center; margin-top:14px; margin-bottom:4px; font-size:14px; }

        .share-wrapper { margin-top:4px; }
        .share-search { margin-bottom:8px; }

        .share-search input {
            width:100%;
            padding:8px 10px;
            border-radius:999px;
            border:1px solid #D1D5DB;
            background:#F9FAFB;
            font-size:13px;
        }

        .share-list {
            max-height:220px;
            overflow:auto;
            border-radius:var(--radius-md);
            border:1px solid #E5E7EB;
            background:#F9FAFB;
            padding:6px 8px;
        }
        .share-row { display:flex; align-items:center; justify-content:space-between; padding:6px 4px; font-size:14px; }
        .share-row + .share-row { border-top:1px solid #E5E7EB; }
        .share-name { color:#374151; margin-right:8px; flex:1; }
        .patient-checkbox { flex-shrink:0; }

        .select-wrapper { position:relative; display:inline-block; width:100%; }
        .select-wrapper select {
            appearance:none;
            -webkit-appearance:none;
            -moz-appearance:none;
            width:100%;
            margin-top:6px;
            padding:10px 36px 10px 11px;
            border-radius:12px;
            border:1px solid #D1D5DB;
            background:#F9FAFB;
            font-size:14px;
            font-family:inherit;
            cursor:pointer;
        }
        .select-arrow {
            position:absolute;
            right:12px;
            top:50%;
            transform:translateY(-50%);
            pointer-events:none;
            font-size:14px;
            color:#6b7280;
        }

        @media (max-width: 768px) {
            body { padding-left: 0; }
            .shell { padding:20px 14px 28px; }
            .card { padding:18px 16px 22px; }
            .top-bar { flex-direction:column; align-items:flex-start; gap:12px; }
            .top-actions { align-self:stretch; justify-content:flex-end; }
            .top-title { font-size:24px; }
            form label { font-size:13px; }
            input[type="text"], textarea, select { font-size:13px; }
            .share-row { flex-wrap:nowrap; }
            .share-name { font-size:13px; }
            .select-wrapper select { font-size:13px; padding:9px 32px 9px 10px; }
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>

<div class="shell">
    <div class="shell-inner">

        <header class="top-bar">
            <div class="top-left">
                <div class="top-title">Resource Hub Admin</div>
                <div class="top-subtitle">Create strategies, categories, and articles to share with your patients.</div>
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
            <form method="post" enctype="multipart/form-data">
                <label>
                    Type
                    <div class="select-wrapper">
                        <select name="item_type" id="item_type" required>
                            <option value="strategy">Daily Strategy</option>
                            <option value="skill">Category</option>
                            <option value="article">Article / Guide</option>
                        </select>
                        <span class="select-arrow">▾</span>
                    </div>
                </label>

                <label id="category-label">
                    Category
                    <select name="skill_key" id="skill_key">
                        <option value="">Not linked to a specific category</option>
                        <?php foreach ($skills as $key => $label): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="hint">Required when Type is “Category”. If you leave Title empty, it will automatically use this Category name.</div>
                </label>

                <label>
                    Title
                    <input type="text" name="title">
                </label>

                <label>
                    Subtitle (optional)
                    <input type="text" name="subtitle" placeholder="e.g. Daily Strategy">
                </label>

                <label>
                    Content / Description (optional)
                    <textarea name="content"></textarea>
                    <div class="hint">You can also paste a URL here (e.g. YouTube link) if you are not uploading a file.</div>
                </label>

                <label>
                    File (any type: PDF, video, audio, image)
                    <input type="file" name="media_file">
                    <div class="hint">Patients will open this file directly from the Resource Hub.</div>
                </label>

                <div class="share-header">
                    <span>Share to</span>
                </div>

                <?php if (empty($linkedPatients)): ?>
                    <p class="hint">You have no linked patients yet, so this resource will not be shared automatically.</p>
                <?php else: ?>
                    <div class="share-wrapper">
                        <div class="share-search">
                            <input type="text" id="patientSearch" placeholder="Search patients by name">
                        </div>
                        <div class="share-list" id="shareList">
                            <?php foreach ($linkedPatients as $p): ?>
                                <?php $pid = (int)$p['User_ID']; ?>
                                <div class="share-row" data-name="<?php echo htmlspecialchars(strtolower($p['First_Name'] . ' ' . $p['Last_Name'])); ?>">
                                    <span class="share-name">
                                        <?php echo htmlspecialchars($p['First_Name'] . ' ' . $p['Last_Name']); ?>
                                    </span>
                                    <input type="checkbox" class="patient-checkbox" name="share_patients[]" value="<?php echo $pid; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="actions-row">
                    <button type="submit" class="btn-primary">Save resource</button>

                    <a href="resourcehub_existing.php" class="btn-secondary-link">
                        View existing resources
                    </a>

                    <a href="resourcehub_library.php" class="btn-library-link">
                        View resource library
                    </a>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var typeSelect     = document.getElementById('item_type');
    var categoryLabel  = document.getElementById('category-label');
    var categorySelect = document.getElementById('skill_key');

    if (typeSelect && categoryLabel && categorySelect) {
        function updateCategoryVisibility() {
            if (typeSelect.value === 'skill') {
                categoryLabel.style.display = '';
            } else {
                categoryLabel.style.display = 'none';
                categorySelect.value = '';
            }
        }
        updateCategoryVisibility();
        typeSelect.addEventListener('change', updateCategoryVisibility);
    }

    // Patient search filter
    var searchInput = document.getElementById('patientSearch');
    var rowsParent  = document.getElementById('shareList');

    if (searchInput && rowsParent) {
        var rows = Array.prototype.slice.call(rowsParent.getElementsByClassName('share-row'));

        searchInput.addEventListener('input', function () {
            var term = this.value.toLowerCase().trim();
            rows.forEach(function (row) {
                var name = row.getAttribute('data-name') || '';
                row.style.display = (term === '' || name.indexOf(term) !== -1) ? '' : 'none';
            });
        });
    }
});
</script>
</body>
</html>
