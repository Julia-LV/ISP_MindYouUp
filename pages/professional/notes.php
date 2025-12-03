<?php
// Professional notes page - List View
session_start();
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// Simple access control: only professionals can use this page
if (!$userId || strtolower($role) !== 'professional') {
    ?><!doctype html>
    <html><head><meta charset="utf-8"><title>Notes - Access denied</title></head><body style="font-family:Arial,Helvetica,sans-serif;padding:2rem;background:#FFF7E1;color:#102b23"><h1>Access denied</h1><p>You must be signed in as a professional to view this page.</p></body></html><?php
    exit;
}

require_once __DIR__ . '/../../config.php';

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

// Fetch notes for this professional (newest first)
$stmt = $conn->prepare("SELECT Note_ID, Note_Title, Note_Text FROM professional_notes WHERE Professional_ID = ? ORDER BY Note_ID DESC");
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $notes[] = $r; }
    $stmt->close();
}

// Handle delete via GET
if (!empty($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    $dstmt = $conn->prepare("DELETE FROM professional_notes WHERE Note_ID = ? AND Professional_ID = ?");
    if ($dstmt) { $dstmt->bind_param('ii', $del, $userId); $dstmt->execute(); $dstmt->close(); }
    header('Location: notes.php'); 
    exit;
}

$page_title = 'Notes';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- TailwindCSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Notes Page CSS -->
    <link href="../../CSS/notes_page.css" rel="stylesheet">
</head>
<body>
<?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
<?php include __DIR__ . '/../../components/header_component.php'; ?>

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
                </div>
            <?php else: ?>
                <?php foreach ($notes as $n): ?>
                    <?php
                    $note_id = $n['Note_ID'];
                    $note_title = $n['Note_Title'] ?: '(Untitled)';
                    ?>
                    <div class="note-card" data-note-id="<?php echo (int)$note_id ?>" onclick="window.location.href='note_edit.php?id=<?php echo urlencode($note_id) ?>'">
                        <div class="note-checkbox" onclick="event.stopPropagation(); toggleCheck(this, <?php echo (int)$note_id ?>)"></div>
                        <span class="note-title"><?php echo htmlspecialchars($note_title) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Floating Buttons -->
        <div class="fab-buttons">
            <button class="fab-delete" id="fabDelete" onclick="deleteSelectedNotes()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                    <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                </svg>
            </button>
            <a href="note_edit.php" class="fab-add">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<script>
let selectedNotes = [];

function toggleCheck(el, noteId) {
    el.classList.toggle('checked');
    el.closest('.note-card').classList.toggle('checked');
    
    if (el.classList.contains('checked')) {
        selectedNotes.push(noteId);
    } else {
        selectedNotes = selectedNotes.filter(id => id !== noteId);
    }
    
    // Show/hide delete button
    const fabDelete = document.getElementById('fabDelete');
    if (selectedNotes.length > 0) {
        fabDelete.classList.add('visible');
    } else {
        fabDelete.classList.remove('visible');
    }
}

function deleteSelectedNotes() {
    if (selectedNotes.length === 0) return;
    
    const message = selectedNotes.length === 1 
        ? 'Are you sure you want to delete this note?' 
        : 'Are you sure you want to delete ' + selectedNotes.length + ' notes?';
    
    if (confirm(message)) {
        // Delete first selected note (for multiple, would need backend change)
        window.location.href = 'notes.php?delete=' + selectedNotes[0];
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
