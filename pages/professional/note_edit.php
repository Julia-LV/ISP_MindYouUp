<?php

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

$message = '';
$editNote = null;
$form_id = '';
$form_title = '';
$form_text = '';
$form_patient = '';


if (!empty($_GET['id'])) {
    $eid = (int)$_GET['id'];
    $est = $conn->prepare("SELECT Note_ID, Note_Title, Note_Text FROM professional_notes WHERE Note_ID = ? AND Professional_ID = ? LIMIT 1");
    if ($est) {
        $est->bind_param('ii', $eid, $userId);
        $est->execute();
        $res = $est->get_result();
        $editNote = $res->fetch_assoc() ?: null;
        $est->close();
    }
    
    if ($editNote) {
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
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $text = trim($_POST['text'] ?? '');
    $patient = trim($_POST['patient'] ?? '');
    $editId = trim($_POST['edit_id'] ?? '');

    if ($title === '' && $text === '') {
        $message = 'Please enter a title or note text.';
    } else {
        $store_text = $text;
        if ($patient !== '') {
            $patient_clean = str_replace(["\n","\r","]"], [' ',' ',''], $patient);
            $store_text = "[patient:" . $patient_clean . "]\n" . $text;
        }

        if ($editId !== '') {
            $eid = (int)$editId;
            $ustmt = $conn->prepare("UPDATE professional_notes SET Note_Title = ?, Note_Text = ? WHERE Note_ID = ? AND Professional_ID = ?");
            if ($ustmt) { $ustmt->bind_param('ssii', $title, $store_text, $eid, $userId); $ustmt->execute(); $ustmt->close(); }
        } else {
            $istmt = $conn->prepare("INSERT INTO professional_notes (Professional_ID, Note_Title, Note_Text) VALUES (?, ?, ?)");
            if ($istmt) { $istmt->bind_param('iss', $userId, $title, $store_text); $istmt->execute(); $istmt->close(); }
        }
        header('Location: notes.php?ok=1'); 
        exit;
    }
}

$page_title = $form_id ? 'Edit Note' : 'New Note';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    
    <link href="../../CSS/notes_page.css?v=2" rel="stylesheet">
</head>
<body>
<?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
<?php include __DIR__ . '/../../components/header_component.php'; ?>

    <div class="main-content">
        <div class="notes-wrapper">
            <div class="notes-header">
                <h1><?php echo $form_id ? 'Edit Note' : 'New Note' ?></h1>
            </div>
            
            <?php if ($message): ?>
                <div class="error-msg" style="background:#fee;border-left:4px solid #c00;padding:12px;border-radius:8px;margin-bottom:16px;"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <div class="form-card" style="max-width:500px;margin:0 auto;">
                <form method="post">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($form_id) ?>">
                    
                    <div style="margin-bottom:16px;">
                        <label for="title" style="display:block;margin-bottom:6px;font-weight:600;">Title</label>
                        <input id="title" name="title" type="text" placeholder="Note title..." value="<?php echo htmlspecialchars($form_title) ?>" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                    </div>
                    
                    <div style="margin-bottom:20px;">
                        <label for="text" style="display:block;margin-bottom:6px;font-weight:600;">Note</label>
                        <textarea id="text" name="text" placeholder="Write your note here..." style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;min-height:150px;resize:vertical;"><?php echo htmlspecialchars($form_text) ?></textarea>
                    </div>
                    
                    <button type="submit" style="width:100%;padding:14px;background:#005949;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;"><?php echo $form_id ? 'Update Note' : 'Save Note' ?></button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
