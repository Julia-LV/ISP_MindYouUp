<?php
session_start();
require_once __DIR__ . '/../../config.php';


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

$currentProfessionalId = $_SESSION['user_id'] ?? 0;


// ---------- SETUP ----------
$errors = [];
$success = '';
$selectedSharePatients = [];

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
 *
 * We only store the FILENAME (e.g. "myfile_1234.pdf").
 * When reading, the patient/professional pages prepend "uploads/".
 */
function handle_upload(string $fieldName, string $uploadBase): ?string
{
    if (
        !isset($_FILES[$fieldName]) ||
        $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK
    ) {
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

    // Only the filename goes into DB
    return $filename;
}

/**
 * Delete a resource + its files.
 */
function delete_resource(mysqli $conn, int $id, array &$errors, string &$success): void
{
    if ($id <= 0) {
        $errors[] = 'Invalid resource ID.';
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT media_url, image_url, thumb_url FROM resource_hub WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        return;
    }

    // Delete files if they exist (handles "filename" or "uploads/filename")
    foreach (['media_url','image_url','thumb_url'] as $field) {
        if (!empty($row[$field])) {
            $stored = trim($row[$field]);

            if (str_starts_with($stored, 'uploads/')) {
                $rel = $stored;
            } else {
                $rel = 'uploads/' . ltrim($stored, '/');
            }

            $fsPath = __DIR__ . '/../../' . $rel;
            if (file_exists($fsPath) && is_file($fsPath)) {
                @unlink($fsPath);
            }
        }
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM resource_hub WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        $success = 'Resource deleted successfully.';
    } else {
        $errors[] = 'Database error when deleting: ' . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}


/* ---------- LOAD LINKED PATIENTS (for Share to) ---------- */
$linkedPatients = [];
if ($currentProfessionalId) {
    $sql = "SELECT up.User_ID, up.First_Name, up.Last_Name
            FROM patient_profile pp
            JOIN user_profile up ON up.User_ID = pp.User_ID
            WHERE pp.Professional_ID = ?
              AND LOWER(up.Role) = 'patient'
            ORDER BY up.Last_Name, up.First_Name";
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

// DELETE via ?delete=ID
if (isset($_GET['delete'])) {
    delete_resource($conn, (int)$_GET['delete'], $errors, $success);
}

// CREATE via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_type  = $_POST['item_type'] ?? '';
    $title      = trim($_POST['title'] ?? '');
    $subtitle   = trim($_POST['subtitle'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $sort_order = 0; // fixed default

    // Selected patients to share with
    $selectedSharePatients = array_map('intval', $_POST['share_patients'] ?? []);

    // Require at least one patient if there are linked patients
    if (!empty($linkedPatients) && empty($selectedSharePatients)) {
        $errors[] = 'Please select at least one patient to share this resource with.';
    }

    if (!in_array($item_type, ['strategy','skill','article'], true)) {
        $errors[] = 'Invalid item type.';
    }
    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    $media_url = handle_upload('media_file', $uploadBase); // single file field for all types
    $image_url = null;  // no image_file input anymore
    $thumb_url = null;  // thumbnails removed

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO resource_hub (item_type, title, subtitle, content, media_url, image_url, thumb_url, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'sssssssi',
            $item_type,
            $title,
            $subtitle,
            $content,
            $media_url,
            $image_url,
            $thumb_url,
            $sort_order
        );
        if (mysqli_stmt_execute($stmt)) {
            $newResourceId = mysqli_insert_id($conn);
            $success = 'Resource saved successfully.';

            // If patients were selected, create share rows in patient_resources
            if ($currentProfessionalId && $newResourceId && !empty($selectedSharePatients)) {
                $ins = $conn->prepare(
                    "INSERT INTO patient_resources (patient_id, resource_id, sent_by, sent_at)
                     VALUES (?, ?, ?, NOW())"
                );
                foreach ($selectedSharePatients as $pid) {
                    $pid = (int)$pid;
                    if ($pid <= 0) continue;
                    $ins->bind_param('iii', $pid, $newResourceId, $currentProfessionalId);
                    $ins->execute();
                }
                $ins->close();
            }
            // Clear selection after successful save
            $selectedSharePatients = [];
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

/* ---------- LOAD EXISTING ITEMS FOR THIS PROFESSIONAL ONLY ---------- */
$items = [];
if ($currentProfessionalId) {
    $sql = "SELECT DISTINCT rh.*
            FROM resource_hub rh
            JOIN patient_resources pr ON pr.resource_id = rh.id
            WHERE pr.sent_by = ?
            ORDER BY rh.item_type, rh.sort_order, rh.id";
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
    <title>Resource Hub Admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        :root {
            --creme: #FFF7E1;
            --coral: #F26647;
            --rosa: #F282A9;
            --verde: #005949;
            --preto: #231F20;
            --light-green: #E9F0E9;
            --border-soft: #F0E3CC;
            --radius-lg: 20px;
            --radius-md: 14px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--light-green);
            color: var(--preto);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .shell {
            min-height: 100vh;
            padding: 28px 20px 36px;
        }

        .shell-inner {
            max-width: 1100px;
            margin: 0 auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .top-title {
            font-size: 26px;
            font-weight: 700;
            color: var(--verde);
        }

        .top-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .top-left {
            display: flex;
            flex-direction: column;
        }

        .top-actions {
            display: flex;
            align-items: center;
        }

        .btn-logout {
            padding: 8px 18px;
            border-radius: 999px;
            border: 1px solid var(--verde);
            background: #ffffff;
            color: var(--verde);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-logout:hover {
            background: #e6f3ec;
        }

        .flash-errors,
        .flash-success {
            padding: 10px 12px;
            border-radius: 12px;
            margin-bottom: 14px;
            font-size: 13px;
        }

        .flash-errors {
            background: #FEE2E2;
            border-left: 4px solid #DC2626;
        }

        .flash-success {
            background: #DCFCE7;
            border-left: 4px solid #16A34A;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(0, 1.3fr);
            gap: 18px;
            align-items: flex-start;
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-soft);
            padding: 20px 22px 22px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.07);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 12px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--preto);
        }

        .card-tag {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--coral);
        }

        form label {
            display: block;
            margin-top: 10px;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            margin-top: 4px;
            padding: 8px 10px;
            border-radius: 12px;
            border: 1px solid #D1D5DB;
            background: #F9FAFB;
            font-size: 13px;
            font-family: inherit;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        input[type="file"] {
            margin-top: 6px;
            font-size: 12px;
        }

        .hint {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        .btn-primary {
            margin-top: 16px;
            padding: 10px 22px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            background: var(--verde);
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 7px 16px rgba(0, 0, 0, 0.15);
        }

        .btn-primary:hover {
            background: #00453F;
        }

        .share-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .share-toggle {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .share-list {
            max-height: 200px;
            overflow: auto;
            border-radius: var(--radius-md);
            border: 1px solid #E5E7EB;
            background: #F9FAFB;
            padding: 6px 8px;
            margin-top: 4px;
        }

        .share-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 2px;
            font-size: 13px;
        }

        .share-row + .share-row {
            border-top: 1px solid #E5E7EB;
        }

        .share-name {
            color: #374151;
        }

        .table-wrapper {
            margin-top: 4px;
            border-radius: var(--radius-lg);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        th,
        td {
            padding: 7px 8px;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
        }

        th {
            background: #E2F3EB;
            color: var(--verde);
            font-weight: 600;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .type-badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 11px;
            background: #E0F2FE;
            color: #1D4ED8;
        }

        .actions a {
            font-size: 12px;
            color: #B91C1C;
            font-weight: 600;
        }

        .no-resources {
            font-size: 13px;
            color: #6B7280;
            padding-top: 4px;
        }
    </style>
</head>
<body>
<div class="shell">
    <div class="shell-inner">

        <header class="top-bar">
            <div class="top-left">
                <div class="top-title">Resource Hub Admin</div>
                <div class="top-subtitle">Create strategies, skills, and articles to share with your patients.</div>
            </div>
            <div class="top-actions">
                <a href="../auth/logout.php">
                    <button type="button" class="btn-logout">Log out</button>
                </a>
            </div>
        </header>

        <?php if ($errors): ?>
            <div class="flash-errors">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="layout">
            <!-- LEFT: create / edit resource -->
            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">New resource</h2>
                    <span class="card-tag">Create &amp; share</span>
                </div>

                <form method="post" enctype="multipart/form-data">
                    <label>
                        Type
                        <select name="item_type" required>
                            <option value="strategy">Daily Strategy</option>
                            <option value="skill">Skill</option>
                            <option value="article">Article / Guide</option>
                        </select>
                    </label>

                    <label>
                        Title
                        <input type="text" name="title" required>
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

                    <!-- Share to patients -->
                    <div class="share-header">
                        <span>Share to</span>
                        <label class="share-toggle">
                            <input type="checkbox" id="select_all_patients">
                            <span>Select all</span>
                        </label>
                    </div>

                    <?php if (empty($linkedPatients)): ?>
                        <p class="hint">
                            You have no linked patients yet, so this resource will not be shared automatically.
                        </p>
                    <?php else: ?>
                        <div class="share-list">
                            <?php foreach ($linkedPatients as $p): ?>
                                <?php $pid = (int)$p['User_ID']; ?>
                                <div class="share-row">
                                    <span class="share-name">
                                        <?php echo htmlspecialchars($p['First_Name'] . ' ' . $p['Last_Name']); ?>
                                        (#<?php echo $pid; ?>)
                                    </span>
                                    <input
                                        type="checkbox"
                                        class="patient-checkbox"
                                        name="share_patients[]"
                                        value="<?php echo $pid; ?>"
                                        <?php echo in_array($pid, $selectedSharePatients, true) ? 'checked' : ''; ?>
                                    >
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-primary">Save resource</button>
                </form>
            </section>

            <!-- RIGHT: existing resources list -->
            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Existing resources</h2>
                    <span class="card-tag">Overview</span>
                </div>

                <div class="table-wrapper">
                    <?php if ($items): ?>
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Media</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo (int)$item['id']; ?></td>
                                    <td><span class="type-badge"><?php echo htmlspecialchars($item['item_type']); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo htmlspecialchars($item['media_url'] ?? ''); ?></td>
                                    <td class="actions">
                                        <a href="?delete=<?php echo (int)$item['id']; ?>"
                                           onclick="return confirm('Delete this resource (and its files)?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-resources">No resources yet. Once you send items to patients, they will appear here.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const master = document.getElementById('select_all_patients');
    if (!master) return;
    master.addEventListener('change', function () {
        const boxes = document.querySelectorAll('.patient-checkbox');
        boxes.forEach(function (b) {
            b.checked = master.checked;
        });
    });
});
</script>
</body>
</html>