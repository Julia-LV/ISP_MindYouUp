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
            <!-- Professional details removed as requested -->
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
        
        <!-- Add Note Button (Green, inside main box) -->
        <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
            <?php 
                $label = 'Add New Note';
                $type = 'link';
                $href = 'note_edit.php';
                $variant = 'primary';
                $width = 'w-auto';
                include __DIR__ . '/../../components/button.php'; 
            ?>
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
