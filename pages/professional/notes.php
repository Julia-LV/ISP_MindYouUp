<?php
// Professional notes page
session_start();
require_once __DIR__ . '/../../config.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// Simple access control: only professionals can use this page
if (!$userId || strtolower($role) !== 'professional') {
    ?><!doctype html>
    <html><head><meta charset="utf-8"><title>Notes - Access denied</title></head><body style="font-family:Arial,Helvetica,sans-serif;padding:2rem;background:#E9F0E9;color:#102b23"><h1>Access denied</h1><p>You must be signed in as a professional to view this page.</p></body></html><?php
    exit;
}

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

// Load notes from database
$notes = [];
if ($conn) {
    $stmt = $conn->prepare("SELECT Note_ID, Note_Title, Note_Text FROM professional_notes WHERE Professional_ID = ? ORDER BY Note_ID DESC");
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $notes[] = [
                'id' => $row['Note_ID'],
                'title' => $row['Note_Title'],
                'text' => $row['Note_Text'],
                'created' => date('Y-m-d H:i')
            ];
        }
        $stmt->close();
    }
}

$message = '';

// Handle delete via GET
if (!empty($_GET['delete']) && $conn) {
    $del = (int)$_GET['delete'];
    $delStmt = $conn->prepare("DELETE FROM professional_notes WHERE Note_ID = ? AND Professional_ID = ?");
    if ($delStmt) {
        $delStmt->bind_param('ii', $del, $userId);
        $delStmt->execute();
        $delStmt->close();
    }
    header('Location: notes.php?ok=1'); 
    exit;
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Notes Page CSS -->
    <link href="../../CSS/notes_page.css?v=2" rel="stylesheet">
</head>
<body>
<?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
<?php include __DIR__ . '/../../components/header_component.php'; ?>

<?php if (!empty($_GET['ok'])): ?>
    <div class="success-toast" style="position:fixed;top:100px;left:50%;transform:translateX(-50%);background:#005949;color:#fff;padding:12px 24px;border-radius:8px;z-index:1000;">Note saved successfully!</div>
<?php endif; ?>

<div class="main-content">
    <div class="notes-wrapper" style="position:relative;min-height:400px;">
        <div class="notes-header">
            <h1>Notes</h1>
        </div>
        
        <div class="notes-container" style="max-width:600px;padding-bottom:80px;">
            <?php if (empty($notes)): ?>
                <div class="empty-state" style="text-align:center;padding:40px;">
                    <p style="font-size:1.1rem;color:#666;">No notes yet.</p>
                    <p style="font-size:0.95rem;color:#888;">Click the + button to create your first note.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notes as $n): ?>
                    <div class="note-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 20px;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;">
                        <div style="flex:1;">
                            <strong style="font-size:1.05rem;color:#0b2a24;"><?php echo htmlspecialchars($n['title'] ?: '(no title)') ?></strong>
                            <div style="color:#888;font-size:.8rem;margin-top:4px;"><?php echo htmlspecialchars($n['created']) ?></div>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <?php 
                            $type = 'link'; $href = 'note_edit.php?id=' . urlencode($n['id']); $label = 'Edit'; $variant = 'primary'; $width = 'w-auto';
                            include __DIR__ . '/../../components/button.php';
                            ?>
                            <?php 
                            $type = 'link'; $href = '?delete=' . urlencode($n['id']); $label = 'Delete'; $variant = 'secondary'; $width = 'w-auto'; $onclick = "return confirm('Delete this note?')";
                            include __DIR__ . '/../../components/button.php';
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- New Note Button using button component -->
        <div style="position:absolute;bottom:20px;right:20px;">
            <?php 
                $type = 'link';
                $href = 'note_edit.php';
                $label = 'Add Note';
                $variant = 'primary'; // Green
                $width = 'w-auto';
                include __DIR__ . '/../../components/button.php';
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
