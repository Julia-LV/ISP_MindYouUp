<?php
// Professional notes page
session_start();
// Do not modify auth pages; rely on session set by login flow
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// Simple access control: only professionals can use this page
if (!$userId || strtolower($role) !== 'professional') {
    // Show a friendly message and stop
    ?><!doctype html>
    <html><head><meta charset="utf-8"><title>Notes - Access denied</title></head><body style="font-family:Arial,Helvetica,sans-serif;padding:2rem;background:#FFF7E1;color:#102b23"><h1>Access denied</h1><p>You must be signed in as a professional to view this page.</p></body></html><?php
    exit;
}

// Data storage: per-professional JSON file to avoid touching DB schema here
$dataDir = __DIR__ . '/../../data';
if (!is_dir($dataDir)) @mkdir($dataDir, 0755, true);
$notesFile = $dataDir . '/professional_notes_' . (int)$userId . '.json';

$CURRENT_USER = null;
if (!empty($_SESSION['user_id']) && isset($conn)) {
    $uid = (int) $_SESSION['user_id'];
    $stmtUsr = mysqli_prepare($conn, "SELECT User_ID, First_Name, Last_Name, Email, Role FROM user_profile WHERE User_ID = ? LIMIT 1");
    if ($stmtUsr) {
        mysqli_stmt_bind_param($stmtUsr, 'i', $uid);
        mysqli_stmt_execute($stmtUsr);
        $resUsr = mysqli_stmt_get_result($stmtUsr);
        if ($resUsr && $rowu = mysqli_fetch_assoc($resUsr)) {
            $CURRENT_USER = $rowu;
        }
        mysqli_stmt_close($stmtUsr);
    }
}

$notes = [];
if (file_exists($notesFile)) {
    $raw = file_get_contents($notesFile);
    $notes = json_decode($raw, true) ?: [];
}

$message = '';

// Handle delete via GET
if (!empty($_GET['delete'])) {
    $del = (string)$_GET['delete'];
    $changed = false;
    foreach ($notes as $i => $n) {
        if ((string)$n['id'] === $del) { unset($notes[$i]); $changed = true; break; }
    }
    if ($changed) {
        file_put_contents($notesFile, json_encode(array_values($notes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = 'Note deleted.';
    }
    header('Location: notes.php'); exit;
}

// Handle add / edit via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $text = trim($_POST['text'] ?? '');
    $patient = trim($_POST['patient'] ?? '');
    $editId = trim($_POST['edit_id'] ?? '');

    if ($title === '' && $text === '') {
        $message = 'Please enter a title or note text.';
    } else {
        if ($editId !== '') {
            // update existing
            foreach ($notes as &$n) {
                if ((string)$n['id'] === $editId) {
                    $n['title'] = $title;
                    $n['text'] = $text;
                    $n['patient'] = $patient;
                    $n['updated'] = date('c');
                    break;
                }
            }
            unset($n);
            $message = 'Note updated.';
        } else {
            // create new
            $note = [
                'id' => uniqid('', true),
                'title' => $title,
                'text' => $text,
                'patient' => $patient,
                'created' => date('c'),
            ];
            array_unshift($notes, $note); // newest first
            $message = 'Note saved.';
        }
        file_put_contents($notesFile, json_encode(array_values($notes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        // redirect to avoid resubmit
        header('Location: notes.php?ok=1'); exit;
    }
}

// Helper: find note by id for edit
$editNote = null;
if (!empty($_GET['edit'])) {
    $eid = (string)$_GET['edit'];
    foreach ($notes as $n) if ((string)$n['id'] === $eid) { $editNote = $n; break; }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Professional notes</title>
    <style>
        :root{--bg-creme:#FFF7E1;--accent-orange:#F26647;--accent-green:#005949;--radius:10px}
        body{font-family:Arial,Helvetica,sans-serif;background:var(--bg-creme);margin:0;padding:20px;color:#0b2a24}
        .wrap{max-width:900px;margin:0 auto}
        h1{color:var(--accent-green);margin:0 0 12px}
        .grid{display:grid;grid-template-columns:1fr 360px;gap:18px}
        .card{background:#fff;padding:16px;border-radius:var(--radius);box-shadow:0 8px 24px rgba(0,0,0,0.06)}
        label{display:block;margin-bottom:6px;font-weight:600}
        input[type=text], textarea{width:100%;padding:10px;border:1px solid #e6e6e6;border-radius:8px;box-sizing:border-box}
        textarea{min-height:120px}
        .btn{display:inline-block;padding:10px 14px;background:linear-gradient(180deg,var(--accent-orange),#e6553e);color:#fff;border-radius:8px;text-decoration:none;border:0}
        .btn.ghost{background:transparent;color:var(--accent-green);border:1px solid rgba(0,0,0,0.06)}
        .note-item{border-bottom:1px solid #f0f0f0;padding:12px 0}
        .note-meta{color:#666;font-size:.9rem}
        .small{font-size:.9rem;color:#666}
        .actions a{margin-right:8px}
    </style>
</head>
<body>
    <div class="wrap">
        <a href="../common/index.php" class="small">&larr; Back</a>
        <h1>My professional notes</h1>
        <?php if (!empty($_GET['ok'])): ?><div style="margin:8px 0;padding:8px;background:#e6f9ee;border-left:4px solid var(--accent-green);border-radius:6px;color:#055">
            Action completed.
        </div><?php endif; ?>

<<<<<<< Updated upstream
<?php if (!empty($_GET['ok'])): ?>
    <div class="success-toast">Note saved successfully!</div>
<?php endif; ?>

<div class="main-content">
    <div class="notes-wrapper">
        <div class="notes-header">
            <h1>Notes</h1>
            <?php if (!empty($CURRENT_USER)): ?>
                <div class="user-card" style="margin-top:8px; display:flex; gap:10px; align-items:center;">
                    <div class="user-avatar" style="width:36px;height:36px;border-radius:8px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-weight:600;color:#444"><?= htmlspecialchars(substr($CURRENT_USER['First_Name'] ?? '',0,1)) ?></div>
                    <div class="user-info" style="font-size:0.92rem;">
                        <div style="font-weight:600"><?= htmlspecialchars(($CURRENT_USER['First_Name'] ?? '') . ' ' . ($CURRENT_USER['Last_Name'] ?? '')) ?></div>
                        <div style="font-size:0.82rem;color:#666"><?= htmlspecialchars($CURRENT_USER['Role'] ?? '') ?> &nbsp;|&nbsp; <?= htmlspecialchars($CURRENT_USER['Email'] ?? '') ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="notes-container">
            <?php if (empty($notes)): ?>
                <div class="empty-state">
                    <p>No notes yet.</p>
                    <p style="font-size:0.95rem;">Tap the + button to create your first note.</p>
=======
        <div class="grid">
            <div>
                <div class="card">
                    <form method="post">
                        <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($editNote['id'] ?? '') ?>">
                        <div style="margin-bottom:12px">
                            <label for="title">Title</label>
                            <input id="title" name="title" type="text" value="<?php echo htmlspecialchars($editNote['title'] ?? '') ?>">
                        </div>
                        <div style="margin-bottom:12px">
                            <label for="text">Note</label>
                            <textarea id="text" name="text"><?php echo htmlspecialchars($editNote['text'] ?? '') ?></textarea>
                        </div>
                        <div style="margin-bottom:12px">
                            <label for="patient">Patient ID (optional)</label>
                            <input id="patient" name="patient" type="text" value="<?php echo htmlspecialchars($editNote['patient'] ?? '') ?>">
                        </div>
                        <div style="display:flex;gap:8px;justify-content:flex-end">
                            <?php if ($editNote): ?><a class="btn ghost" href="notes.php">Cancel</a><?php endif; ?>
                            <button class="btn" type="submit"><?php echo $editNote ? 'Update note' : 'Save note' ?></button>
                        </div>
                    </form>
>>>>>>> Stashed changes
                </div>

                <div style="margin-top:18px">
                    <div class="card">
                        <h3 style="margin-top:0">Notes</h3>
                        <?php if (empty($notes)): ?>
                            <p class="small">No notes yet. Use the form to add one.</p>
                        <?php else: ?>
                            <?php foreach ($notes as $n): ?>
                                <div class="note-item">
                                    <div style="display:flex;justify-content:space-between;align-items:start">
                                        <div>
                                            <strong><?php echo htmlspecialchars($n['title'] ?: '(no title)') ?></strong>
                                            <div class="note-meta"><?php echo htmlspecialchars($n['patient'] ? 'Patient: '.$n['patient'] : 'General') ?> • <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($n['created']))) ?></div>
                                        </div>
                                        <div class="actions">
                                            <a class="small" href="?edit=<?php echo urlencode($n['id']) ?>">Edit</a>
                                            <a class="small" href="?delete=<?php echo urlencode($n['id']) ?>" onclick="return confirm('Delete this note?')">Delete</a>
                                        </div>
                                    </div>
                                    <?php if ($n['text']): ?><div style="margin-top:8px;color:#222"><?php echo nl2br(htmlspecialchars($n['text'])) ?></div><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <aside>
                <div class="card">
                    <h3 style="margin-top:0">Tips</h3>
                    <p class="small">Use notes to record session summaries, recommendations, or tasks for patients. Patient ID is optional.</p>
                    <hr>
                    <h4 style="margin:8px 0">DB integration (optional)</h4>
                    <p class="small">If you prefer to store notes in the database table <code>professional_notes</code>, you can replace the JSON save/load with prepared INSERT/UPDATE/DELETE statements. Example (commented):</p>
                    <pre style="background:#f6f6f6;padding:8px;border-radius:6px;font-size:.9rem;overflow:auto">// $sql = "INSERT INTO professional_notes (USE_USER_ID, PROFESSIONAL_ID, USER_ID, NOTE_TITLE, NOTE_TEXT) VALUES (?, ?, ?, ?, ?)";
// // $stmt = $conn->prepare($sql);
// // $stmt->bind_param('iiiss', $use_user_id, $professional_id, $patient_id, $title, $text);
// // $stmt->execute();</pre>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
