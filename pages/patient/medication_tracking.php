<?php
session_start();
require_once __DIR__ . '/../../config.php';

$userId = $_SESSION['user_id'] ?? null;
$isLoggedIn = (bool)$userId;
// Allow viewing the page even when not logged in. Actions (add/delete) remain restricted to logged-in users.

/*
 // USER INFO (COMMENTED OUT)
 // Example: fetch richer current user profile when needed. All lines are commented.
 // $CURRENT_USER = null;
 // if ($userId) {
 //     $psql = "SELECT User_ID, First_Name, Last_Name, `E-mail`, `Role` FROM user_profile WHERE User_ID = ? LIMIT 1";
 //     if ($pstmt = $conn->prepare($psql)) {
 //         $pstmt->bind_param('i', $userId);
 //         $pstmt->execute();
 //         $pstmt->bind_result($uid,$fname,$lname,$uemail,$urole);
 //         if ($pstmt->fetch()) {
 //             $CURRENT_USER = ['id'=> (int)$uid, 'first'=>$fname, 'last'=>$lname, 'email'=>$uemail, 'role'=>$urole];
 //         }
 //         $pstmt->close();
 //     }
 // }
 */

$message = '';

// Ensure medications table exists
$createSql = "
CREATE TABLE IF NOT EXISTS medications (
  Medication_ID INT AUTO_INCREMENT PRIMARY KEY,
  User_ID INT NOT NULL,
  Name VARCHAR(255) NOT NULL,
  Dose VARCHAR(100) DEFAULT '',
  Time TIME NOT NULL,
  Days VARCHAR(30) DEFAULT 'Daily', -- CSV like Mon,Tue or Daily
  Notes TEXT,
  Created DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
mysqli_query($conn, $createSql);

// Handle add medication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!$isLoggedIn) {
        $message = 'Please log in to add medications.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $dose = trim($_POST['dose'] ?? '');
        $time = $_POST['time'] ?? '';
        $daysArr = $_POST['days'] ?? [];
        $notes = trim($_POST['notes'] ?? '');

        if ($name === '' || $time === '') {
            $message = 'Name and time are required.';
        } else {
            $days = empty($daysArr) ? 'Daily' : implode(',', array_map('trim', $daysArr));
            $stmt = mysqli_prepare($conn, "INSERT INTO medications (User_ID, Name, Dose, Time, Days, Notes) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isssss', $userId, $name, $dose, $time, $days, $notes);
            if (mysqli_stmt_execute($stmt)) {
                $message = 'Medication saved.';
            } else {
                $message = 'Failed to save medication.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Handle delete (only for logged in users)
if (isset($_GET['delete'])) {
    if ($isLoggedIn) {
        $toDelete = (int)$_GET['delete'];
        $stmt = mysqli_prepare($conn, "DELETE FROM medications WHERE Medication_ID = ? AND User_ID = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $toDelete, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: " . basename(__FILE__));
        exit;
    } else {
        $message = 'Please log in to delete medications.';
    }
}

// Fetch user's medications (only when logged in)
if ($isLoggedIn) {
    $stmt = mysqli_prepare($conn, "SELECT Medication_ID, Name, Dose, Time, Days, Notes, Created FROM medications WHERE User_ID = ? ORDER BY Time ASC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $meds = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} else {
    $meds = [];
}

function humanize_days($daysCsv) {
    if ($daysCsv === 'Daily') return 'Daily';
    return $daysCsv;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <!-- TailwindCSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Track Medications</title>
    <style>
        /* Brand colors */
        :root {
            --bg-creme: #FFF7E1; /* background */
            --accent-orange: #F26647; /* primary buttons */
            --accent-green:  #005949; /* headings */
            --muted: rgba(11,42,36,0.65);
            --radius: 8px;
        }

        body { font-family: Arial, sans-serif; background:var(--bg-creme); margin:0; padding:20px; color:var(--muted); }
        .container { max-width:900px; margin:20px auto; background:#fff; padding:20px; border-radius:var(--radius); box-shadow:0 2px 8px rgba(0,0,0,.05); }
        h1 { margin-top:0; color:var(--accent-green); }

        /* Vertical form layout: stack fields full width */
    form { display:block; }
    form > div { margin-bottom:12px; }
    form .full { width:100%; }
    label { display:block; font-size:.95rem; margin-bottom:6px; color:#333; }
    input[type="text"], input[type="time"], textarea { width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; box-sizing:border-box; }
    textarea { max-width:100%; resize:vertical; }
        .days { display:flex; gap:8px; flex-wrap:wrap; }
        .days label { display:flex; align-items:center; gap:6px; font-size:.9rem; }

        /* Button styles (primary = orange) */
        .btn {
            display:inline-flex; align-items:center; justify-content:center;
            padding:12px 16px; font-size:1rem; color:#fff; border-radius:8px; border:2px solid transparent;
            background: linear-gradient(180deg, var(--accent-orange), #e6553e);
            cursor:pointer; box-shadow:0 6px 14px rgba(0,0,0,0.08);
        }

        .btn:active { transform:translateY(1px); }

        .message { color: #155724; background:#d4edda; padding:8px; border-radius:6px; margin-bottom:12px; }
        table { width:100%; border-collapse:collapse; margin-top:16px; }
        th, td { padding:10px; border-bottom:1px solid #eee; text-align:left; vertical-align:top; }
        .small { font-size:.9rem; color:#666; }
        .actions a { color:#c0392b; text-decoration:none; margin-left:8px; }

        /* two-col helper kept for optional use; not used by default to keep strict vertical order */
        @media (min-width: 700px) {
            .two-col { display:flex; gap:12px; }
            .two-col > div { flex:1; }
        }
    </style>
</head>
<body>
    

    <div class="container">
    <h1>Medication tracker</h1>

        <?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

        <form method="post" class="add-med">
            <input type="hidden" name="action" value="add">
            <div>
                <label for="name">Medication name</label>
                <input id="name" name="name" type="text" required>
            </div>
            <div>
                <label for="dose">Dose / instructions</label>
                <input id="dose" name="dose" type="text" placeholder="e.g. 20 mg">
            </div>

            <div>
                <label>Days</label>
                <div class="days">
                    <label><input type="checkbox" name="days[]" value="Mon"> Mon</label>
                    <label><input type="checkbox" name="days[]" value="Tue"> Tue</label>
                    <label><input type="checkbox" name="days[]" value="Wed"> Wed</label>
                    <label><input type="checkbox" name="days[]" value="Thu"> Thu</label>
                    <label><input type="checkbox" name="days[]" value="Fri"> Fri</label>
                    <label><input type="checkbox" name="days[]" value="Sat"> Sat</label>
                    <label><input type="checkbox" name="days[]" value="Sun"> Sun</label>
                </div>
            </div>

            <div>
                <label for="time">Time to take</label>
                <input id="time" name="time" type="time" required>
            </div>

            <div>
                <label for="notes">Notes (optional)</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>

            <div style="text-align:right">
                <button class="btn" type="submit">Add medication</button>
            </div>
        </form>

        <h2>Your medications</h2>

        <?php if (empty($meds)): ?>
            <p class="small">No medications set. Use the form above to add one.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>When</th><th>Medication</th><th>Days</th><th>Notes</th><th class="small">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($meds as $m): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars(date('H:i', strtotime($m['Time']))) ?></strong><br>
                                <span class="small">Added <?= htmlspecialchars(date('Y-m-d', strtotime($m['Created']))) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($m['Name']) ?><br>
                                <span class="small"><?= htmlspecialchars($m['Dose']) ?></span>
                            </td>
                            <td><?= htmlspecialchars(humanize_days($m['Days'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($m['Notes'])) ?></td>
                            <td class="actions small">
                                <a href="?delete=<?= (int)$m['Medication_ID'] ?>" onclick="return confirm('Delete this medication?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>