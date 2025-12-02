<?php
session_start();
if (empty($_SESSION['uid'])) {
    header('Location: login.php?needsLogin=1');
    exit;
}

require_once __DIR__ . '/config.php';
if (!isset($conn) || !$conn) { die('Database connection not initialized. Please check config.php.'); }

// Helper function to ensure the `created_at` column exists
function ensure_tic_log_created_at($conn) {
    $check = $conn->query("SHOW COLUMNS FROM tic_log LIKE 'created_at'");
    if ($check && $check->num_rows === 0) {
        $conn->query("ALTER TABLE tic_log ADD COLUMN created_at DATETIME NULL AFTER `Self-reported`");
    }
}
ensure_tic_log_created_at($conn);

function clean($v) { return trim((string)$v); }

// ----- CREATE -----
$create_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $patient_id = (int)($_POST['patient_id'] ?? 0);
    $type_desc  = clean($_POST['type_desc'] ?? '');
    $duration   = (int)($_POST['duration'] ?? 0);
    $intensity  = (int)($_POST['intensity'] ?? 0);
    $self_rep   = isset($_POST['self_reported']) ? 1 : 0;

    if ($patient_id <= 0 || $type_desc === '' || $duration < 0 || $intensity < 0 || $intensity > 10) {
        $create_msg = 'Please fill all fields correctly.';
    } else {
        $next_tic_id_res = mysqli_query($conn, "SELECT COALESCE(MAX(Tic_ID), 0) + 1 AS next_id FROM tic_log");
        $next_tic_id = $next_tic_id_res ? mysqli_fetch_assoc($next_tic_id_res)['next_id'] : 1;

        $sql = "INSERT INTO tic_log (`Tic_ID`,`Patient_ID`,`Type_Description`,`Duration`,`Intensity`,`Self-reported`, `created_at`)
                VALUES (?,?,?,?,?,?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iisiii', $next_tic_id, $patient_id, $type_desc, $duration, $intensity, $self_rep);
            if (mysqli_stmt_execute($stmt)) {
                $create_msg = 'Log created successfully.';
            } else {
                $create_msg = 'Error creating log: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $create_msg = 'Database error: ' . mysqli_error($conn);
        }
    }
}

// ----- DELETE -----
$delete_msg = '';
if (isset($_GET['delete'])) {
    $tic_id = (int)$_GET['delete'];
    $sql = "DELETE FROM tic_log WHERE Tic_ID=? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $tic_id);
        if (mysqli_stmt_execute($stmt)) {
            $delete_msg = 'Log deleted successfully.';
        } else {
            $delete_msg = 'Error deleting log: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
        header('Location: web_portal.php?msg=' . urlencode($delete_msg));
        exit;
    }
}
if (isset($_GET['msg'])) {
    $delete_msg = clean($_GET['msg']);
}

// ----- LIST -----
$sql = "SELECT * FROM tic_log ORDER BY created_at DESC, Tic_ID DESC LIMIT 20";
$res = mysqli_query($conn, $sql);
$list = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $list[] = $row;
    }
    mysqli_free_result($res);
}

// Temporary patient id for demo
$test_pid = 1;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>MYU Portal</title>
  <!-- use the shared stylesheet -->
  <link rel="stylesheet" href="web_portal.css" />
  <style>
    /* Only keep modal-specific styles here so everything else
       comes from web_portal.css */
    #delete-modal {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        display: none; /* hidden until JS opens it */
        align-items: center;
        justify-content: center;
        padding: 16px;
        z-index: 1000;
    }
    .btn-delete-open { margin: 0; }
  </style>
</head>
<body>
  <header class="app-header">
    <div class="container flex justify-between items-center">
        <span class="app-title">Mind You Up – Admin</span>
        <a class="btn" href="logout.php">Logout</a>
    </div>
  </header>

  <main class="container" role="main">
    <h1>Tic log management</h1>

    <!-- Log Creation Section -->
    <section class="card" aria-labelledby="create-heading">
      <h2 id="create-heading">Create new tic log (admin/caregiver)</h2>
      <?php if ($create_msg): ?>
        <div class="notice notice-info"><?= htmlspecialchars($create_msg) ?></div>
      <?php endif; ?>

      <form method="POST" action="web_portal.php">
        <input type="hidden" name="action" value="create" />
        <div class="form-grid">
          <label>
            Patient ID (test: 1)
            <input type="number" name="patient_id" value="<?= $test_pid ?>" required />
          </label>
          <label>
            Type
            <input type="text" name="type_desc" placeholder="motor or vocal" required />
          </label>
          <label>
            Duration (seconds)
            <input type="number" name="duration" value="1" min="0" required />
          </label>
          <label>
            Intensity (1–10)
            <input type="number" name="intensity" value="5" min="1" max="10" required />
          </label>
          <label class="checkbox">
            <input type="checkbox" name="self_reported" />
            Self-reported?
          </label>
          <div class="actions">
            <button type="submit" class="btn btn-primary">Submit log</button>
          </div>
        </div>
      </form>
    </section>

    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #f0d9d2;">

    <!-- Log Listing Section -->
    <section class="card" aria-labelledby="recent-heading">
      <h2 id="recent-heading">Recent tic logs</h2>
      <?php if ($delete_msg): ?>
        <div class="notice notice-info"><?= htmlspecialchars($delete_msg) ?></div>
      <?php endif; ?>

      <?php if (empty($list)): ?>
        <p>No logs found.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Patient</th>
                <th scope="col">Type</th>
                <th scope="col">Duration (s)</th>
                <th scope="col">Intensity</th>
                <th scope="col">Source</th>
                <th scope="col">Logged at</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($list as $row): ?>
                <tr>
                  <td><?= (int)$row['Tic_ID'] ?></td>
                  <td><?= (int)$row['Patient_ID'] ?></td>
                  <td><?= htmlspecialchars($row['Type_Description']) ?></td>
                  <td><?= (int)$row['Duration'] ?></td>
                  <td><?= (int)$row['Intensity'] ?></td>
                  <td><?= ((int)$row['Self-reported']) ? 'Self-reported' : 'Caregiver/admin' ?></td>
                  <td><?= date('Y-m-d H:i', strtotime($row['created_at'] ?? 'now')) ?></td>
                  <td>
                    <button class="btn btn-danger btn-delete-open" data-tic-id="<?= (int)$row['Tic_ID'] ?>">Delete</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <footer class="app-footer">
  <div class="container small-text app-footer-inner">
    <span>TicTracker – Mind You Up Portal</span>
    <span>For clinical use and ongoing support in tic disorders.</span>
    <span class="footer-links">
      <a href="/ISP_MindYouUp/Privacy-Policy.pdf"
         target="_blank"
         rel="noopener">
        Privacy Policy
      </a>
      <span aria-hidden="true">·</span>
      <a href="/ISP_MindYouUp/Terms-Conditions.pdf"
         target="_blank"
         rel="noopener">
        Terms &amp; Conditions
      </a>
    </span>
  </div>
</footer>


  <div id="delete-modal">
    <div class="card" style="max-width:400px; width:100%; text-align:center;">
        <h2>Confirm deletion</h2>
        <p>Are you sure you want to permanently delete this tic log entry?</p>
        <div class="actions" style="justify-content:center; margin-top: 15px;">
            <button class="btn" id="btn-delete-cancel">Cancel</button>
            <a class="btn btn-danger" id="btn-delete-confirm" href="#">Delete log</a>
        </div>
    </div>
  </div>

  <script>
    const deleteModal = document.getElementById('delete-modal');
    const deleteConfirmBtn = document.getElementById('btn-delete-confirm');
    const deleteCancelBtn = document.getElementById('btn-delete-cancel');
    const deleteOpenBtns = document.querySelectorAll('.btn-delete-open');

    deleteOpenBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const ticId = this.getAttribute('data-tic-id');
        if (ticId) {
          deleteConfirmBtn.href = '?delete=' + ticId;
          deleteModal.style.display = 'flex';
        }
      });
    });

    deleteCancelBtn.addEventListener('click', function() {
      deleteModal.style.display = 'none';
    });

    deleteModal.addEventListener('click', function(e) {
      if (e.target === deleteModal) {
        deleteModal.style.display = 'none';
      }
    });
  </script>
</body>
</html>
