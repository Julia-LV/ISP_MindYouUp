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

// Data storage: use database table `professional_notes` and include shared DB config
require_once __DIR__ . '/../../config.php';

$notes = [];
$message = '';

// fetch notes for this professional (newest first)
$stmt = $conn->prepare("SELECT Note_ID, Note_Title, Note_Text FROM professional_notes WHERE Professional_ID = ? ORDER BY Note_ID DESC");
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $notes[] = $r; }
    $stmt->close();
}

// Handle delete via GET (only delete notes belonging to this professional)
if (!empty($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    $dstmt = $conn->prepare("DELETE FROM professional_notes WHERE Note_ID = ? AND Professional_ID = ?");
    if ($dstmt) { $dstmt->bind_param('ii', $del, $userId); $dstmt->execute(); $dstmt->close(); }
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
        // Preserve optional patient ID by prefixing it into the stored text (no schema change)
        $store_text = $text;
        if ($patient !== '') {
            $patient_clean = str_replace(["\n","\r","]"], [' ',' ',''], $patient);
            $store_text = "[patient:" . $patient_clean . "]\n" . $text;
        }

        if ($editId !== '') {
            $eid = (int)$editId;
            $ustmt = $conn->prepare("UPDATE professional_notes SET Note_Title = ?, Note_Text = ? WHERE Note_ID = ? AND Professional_ID = ?");
            if ($ustmt) { $ustmt->bind_param('ssii', $title, $store_text, $eid, $userId); $ustmt->execute(); $ustmt->close(); }
            $message = 'Note updated.';
        } else {
            $istmt = $conn->prepare("INSERT INTO professional_notes (Professional_ID, Note_Title, Note_Text) VALUES (?, ?, ?)");
            if ($istmt) { $istmt->bind_param('iss', $userId, $title, $store_text); $istmt->execute(); $istmt->close(); }
            $message = 'Note saved.';
        }
        header('Location: notes.php?ok=1'); exit;
    }
}

// Helper: load a single note for edit (if requested)
$editNote = null;
if (!empty($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $est = $conn->prepare("SELECT Note_ID, Note_Title, Note_Text FROM professional_notes WHERE Note_ID = ? AND Professional_ID = ? LIMIT 1");
    if ($est) {
        $est->bind_param('ii', $eid, $userId);
        $est->execute();
        $res = $est->get_result();
        $editNote = $res->fetch_assoc() ?: null;
        $est->close();
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Professional notes</title>

    <!-- TailwindCSS CDN (needed for navbar utility classes) -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Page CSS (relative path) -->
    <link href="../../CSS/notes.css" rel="stylesheet">
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="wrap">
        <a href="../common/index.php" class="small">&larr; Back</a>
        <h1>My professional notes</h1>
        <?php if (!empty($_GET['ok'])): ?><div style="margin:8px 0;padding:8px;background:#e6f9ee;border-left:4px solid var(--accent-green);border-radius:6px;color:#055">
            Action completed.
        </div><?php endif; ?>

        <div class="grid" style="grid-template-columns:1fr">
            <div>
                <div class="card">
                    <?php
                    // Prepare form values from DB-loaded $editNote (if present)
                    $form_id = '';
                    $form_title = '';
                    $form_text = '';
                    $form_patient = '';
                    if (!empty($editNote)) {
                        $form_id = $editNote['Note_ID'];
                        $form_title = $editNote['Note_Title'];
                        $stored = $editNote['Note_Text'] ?? '';
                        if (preg_match('/^\[patient:(.*?)\]\s*(.*)$/s', $stored, $m)) {
                            $form_patient = $m[1];
                            $form_text = $m[2];
                        } else {
                            $form_text = $stored;
                        }
                    }
                    ?>
                    <form method="post">
                        <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($form_id) ?>">
                        <div style="margin-bottom:12px">
                            <label for="title">Title</label>
                            <input id="title" name="title" type="text" value="<?php echo htmlspecialchars($form_title) ?>">
                        </div>
                        <div style="margin-bottom:12px">
                            <label for="text">Note</label>
                            <textarea id="text" name="text"><?php echo htmlspecialchars($form_text) ?></textarea>
                        </div>
                        <div style="margin-bottom:12px">
                            <label for="patient">Patient ID (optional)</label>
                            <input id="patient" name="patient" type="text" value="<?php echo htmlspecialchars($form_patient) ?>">
                        </div>
                        <div style="display:flex;gap:8px;justify-content:flex-end">
                            <?php if ($form_id): ?><a class="btn ghost" href="notes.php">Cancel</a><?php endif; ?>
                            <button class="btn" type="submit"><?php echo $form_id ? 'Update note' : 'Save note' ?></button>
                        </div>
                    </form>
                </div>

                <div style="margin-top:18px">
                    <div class="card">
                        <h3 style="margin-top:0">Notes</h3>
                        <?php if (empty($notes)): ?>
                            <p class="small">No notes yet. Use the form to add one.</p>
                        <?php else: ?>
                            <?php foreach ($notes as $n): ?>
                                <?php
                                $display_text = $n['Note_Text'] ?? '';
                                $display_patient = '';
                                if (preg_match('/^\[patient:(.*?)\]\s*(.*)$/s', $display_text, $m)) {
                                    $display_patient = $m[1];
                                    $display_text = $m[2];
                                }
                                $note_id = $n['Note_ID'];
                                $note_title = $n['Note_Title'];
                                ?>
                                <div class="note-item">
                                    <div style="display:flex;justify-content:space-between;align-items:start">
                                        <div>
                                            <strong><?php echo htmlspecialchars($note_title ?: '(no title)') ?></strong>
                                            <div class="note-meta"><?php echo $display_patient ? htmlspecialchars('Patient: '.$display_patient) : 'General' ?> • <?php echo htmlspecialchars('ID: '.$note_id) ?></div>
                                        </div>
                                        <div class="actions">
                                            <a class="small" href="?edit=<?php echo urlencode($note_id) ?>">Edit</a>
                                            <a class="small" href="?delete=<?php echo urlencode($note_id) ?>" onclick="return confirm('Delete this note?')">Delete</a>
                                        </div>
                                    </div>
                                    <?php if ($display_text): ?><div style="margin-top:8px;color:#222"><?php echo nl2br(htmlspecialchars($display_text)) ?></div><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- aside removed: Tips and JSON instructions intentionally omitted (DB integrated) -->
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</html>
