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

// Helper to turn key into label
function skill_label(?string $key, array $skills): string
{
    if ($key === null || $key === '') {
        return '';
    }
    return $skills[$key] ?? $key;
}

/*
   Upload base folder (filesystem), e.g.
   C:\xampp\htdocs\ISP_MindYouUp\uploads\
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
            FROM patient_profile pp
            JOIN user_profile up ON up.User_ID = pp.User_ID
            WHERE pp.Professional_ID = ?
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

// DELETE via ?delete=ID  (still supported if you keep delete links on other pages)
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

            if ($currentProfessionalId && $newResourceId && !empty($selectedSharePatients)) {
                $ins = $conn->prepare(
                    "INSERT INTO patient_resources (patient_id, resource_id, sent_by, skill_key, sent_at)
                     VALUES (?, ?, ?, ?, NOW())"
                );
                $pidVar       = 0;
                $resourceVar  = $newResourceId;
                $sentByVar    = $currentProfessionalId;
                $skillKeyVar  = $skill_key !== '' ? $skill_key : '';
                $ins->bind_param('iiis', $pidVar, $resourceVar, $sentByVar, $skillKeyVar);

                foreach ($selectedSharePatients as $pid) {
                    $pid = (int)$pid;
                    if ($pid <= 0) continue;
                    $pidVar = $pid;
                    $ins->execute();
                }
                $ins->close();
            }

            $_SESSION['flash_success'] = 'Resource saved successfully.';
            header('Location: ' . current_page_url_without_query());
            exit;
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Resource Hub Admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        :root { --creme:#FFF7E1; --coral:#F26647; --rosa:#F282A9; --verde:#005949; --preto:#231F20; --light-green:#E9F0E9; --border-soft:#F0E3CC; --radius-lg:20px; --radius-md:14px; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; background:var(--light-green); color:var(--preto); }
        a { text-decoration:none; color:inherit; }
        .shell { min-height:100vh; padding:28px 20px 36px; }
        .shell-inner { max-width:1100px; margin:0 auto; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
        .top-title { font-size:26px; font-weight:700; color:var(--verde); }
        .top-subtitle { font-size:13px; color:#6b7280; margin-top:4px; }
        .top-left { display:flex; flex-direction:column; }
        .top-actions { display:flex; align-items:center; gap:10px; }

        .btn-logout,
        .btn-back {
            padding:8px 18px;
            border-radius:999px;
            font-size:13px;
            font-weight:600;
            cursor:pointer;
        }
        .btn-logout {
            border:1px solid var(--verde);
            background:#fff;
            color:var(--verde);
        }
        .btn-logout:hover { background:#e6f3ec; }

        .btn-back {
            border:1px solid var(--verde);
            background:var(--verde);
            color:#ffffff;
        }
        .btn-back:hover { background:#00453F; }

        .flash-errors,.flash-success { padding:10px 12px; border-radius:12px; margin-bottom:14px; font-size:13px; }
        .flash-errors { background:#FEE2E2; border-left:4px solid #DC2626; }
        .flash-success { background:#DCFCE7; border-left:4px solid #16A34A; }

        /* full‑width single column */
        .layout { display:block; }

        .card { background:#fff; border-radius:var(--radius-lg); border:1px solid var(--border-soft); padding:20px 22px 22px; box-shadow:0 10px 28px rgba(0,0,0,0.07); }
        .card-header { display:flex; justify-content:space-between; align-items:baseline; margin-bottom:12px; }
        .card-title { font-size:18px; font-weight:600; color:var(--preto); }

        form label { display:block; margin-top:10px; font-size:13px; font-weight:500; color:#374151; }
        input[type="text"], textarea, select { width:100%; margin-top:4px; padding:8px 10px; border-radius:12px; border:1px solid #D1D5DB; background:#F9FAFB; font-size:13px; font-family:inherit; }
        textarea { min-height:90px; resize:vertical; }
        input[type="file"] { margin-top:6px; font-size:12px; }
        .hint { font-size:11px; color:#6b7280; margin-top:2px; }

        .btn-primary { margin-top:16px; padding:10px 22px; border-radius:999px; border:none; cursor:pointer; background:var(--verde); color:#fff; font-size:14px; font-weight:600; box-shadow:0 7px 16px rgba(0,0,0,0.15); }
        .btn-primary:hover { background:#00453F; }

        .btn-secondary-link {
            display:inline-flex;
            margin-top:16px;
            margin-left:10px;
            padding:10px 22px;
            border-radius:999px;
            border:1px solid var(--verde);
            background:#ffffff;
            color:var(--verde);
            font-size:14px;
            font-weight:600;
            text-decoration:none;
        }
        .btn-secondary-link:hover {
            background:#e6f3ec;
        }

        .share-header { display:flex; justify-content:space-between; align-items:center; margin-top:12px; margin-bottom:4px; font-size:13px; }

        .share-list { max-height:200px; overflow:auto; border-radius:var(--radius-md); border:1px solid #E5E7EB; background:#F9FAFB; padding:6px 8px; margin-top:4px; }
        .share-row { display:flex; align-items:center; justify-content:space-between; padding:4px 2px; font-size:13px; }
        .share-row + .share-row { border-top:1px solid #E5E7EB; }
        .share-name { color:#374151; }
    </style>
</head>
<body>
<div class="shell">
    <div class="shell-inner">

        <header class="top-bar">
            <div class="top-left">
                <div class="top-title">Resource Hub Admin</div>
                <div class="top-subtitle">Create strategies, categories, and articles to share with your patients.</div>
            </div>
            <div class="top-actions">
                <!-- <a href="../auth/logout.php">
                    <button type="button" class="btn-logout">Log out</button>
                </a> -->
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

        <div class="layout">
            <!-- FULL‑WIDTH: create / edit resource -->
            <section class="card">
                <div class="card-header"><h2 class="card-title">New resource</h2></div>

                <form method="post" enctype="multipart/form-data">
                    <label>
                        Type
                        <select name="item_type" id="item_type" required>
                            <option value="strategy">Daily Strategy</option>
                            <option value="skill">Category</option>
                            <option value="article">Article / Guide</option>
                        </select>
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

                    <label>
                        Category (for Categories tab)
                        <select name="skill_key" id="skill_key">
                            <option value="">Not linked to a specific category</option>
                            <?php foreach ($skills as $key => $label): ?>
                                <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="hint">Required when Type is “Category”. If you leave Title empty, it will automatically use this Category name.</div>
                    </label>

                    <!-- Share to patients -->
                    <div class="share-header">
                        <span>Share to</span>
                    </div>

                    <?php if (empty($linkedPatients)): ?>
                        <p class="hint">You have no linked patients yet, so this resource will not be shared automatically.</p>
                    <?php else: ?>
                        <div class="share-list">
                            <?php foreach ($linkedPatients as $p): ?>
                                <?php $pid = (int)$p['User_ID']; ?>
                                <div class="share-row">
                                    <span class="share-name">
                                        <?php echo htmlspecialchars($p['First_Name'] . ' ' . $p['Last_Name']); ?>
                                        (#<?php echo $pid; ?>)
                                    </span>
                                    <input type="checkbox" class="patient-checkbox" name="share_patients[]" value="<?php echo $pid; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-primary">Save resource</button>

                    <!-- Green link button to a separate existing-resources page -->
                    <a href="resourcehub_existing.php" class="btn-secondary-link">
                        View existing resources
                    </a>
                </form>
            </section>
        </div>
    </div>
</div>
</body>
</html>