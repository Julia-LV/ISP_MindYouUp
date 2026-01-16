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

  <!-- Tailwind Play CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> <!-- [web:186] -->
</head>

<body class="bg-[#fff7ea] text-[#384341] font-sans">
  <header class="sticky top-0 z-50 bg-[#0f684f] text-white shadow-[0_2px_8px_rgba(0,0,0,0.16)]">
    <div class="max-w-[1120px] mx-auto px-4 py-3 flex justify-between items-center gap-4">
      <span class="text-[1.1rem] font-bold tracking-wide">Mind You Up – Admin</span>
      <a href="logout.php"
         class="inline-flex items-center px-4 py-2 rounded-full border border-white/85 bg-white/15
                text-white font-semibold text-[0.9rem] no-underline hover:bg-white/25">
        Logout
      </a>
    </div>
  </header>

  <main class="max-w-[1120px] mx-auto px-4 py-8" role="main">
    <h1 class="text-[1.8rem] font-extrabold text-[#0a4936] mb-6">Tic log management</h1>

    <!-- Create -->
    <section class="bg-white border border-[#f0d9d2] rounded-2xl p-6 shadow-[0_6px_18px_rgba(0,0,0,0.05)]"
             aria-labelledby="create-heading">
      <h2 id="create-heading" class="text-[1.25rem] font-bold text-[#0f684f] mb-3">
        Create new tic log (admin/caregiver)
      </h2>

      <?php if ($create_msg): ?>
        <div class="mb-4 rounded-xl border border-[#ffd7b3] bg-[#fff0e0] px-4 py-3 text-[#384341]">
          <?= htmlspecialchars($create_msg) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="web_portal.php" class="mt-2">
        <input type="hidden" name="action" value="create" />

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 items-end">
          <label class="block">
            <span class="block text-[0.9rem] font-semibold text-[#0a4936]">Patient ID (test: 1)</span>
            <input type="number" name="patient_id" value="<?= $test_pid ?>" required
                   class="mt-1 w-full rounded-xl border border-[#f0d9d2] bg-white px-3 py-2
                          focus:outline-none focus:ring-2 focus:ring-[#0f684f]/40" />
          </label>

          <label class="block">
            <span class="block text-[0.9rem] font-semibold text-[#0a4936]">Type</span>
            <input type="text" name="type_desc" placeholder="motor or vocal" required
                   class="mt-1 w-full rounded-xl border border-[#f0d9d2] bg-white px-3 py-2
                          focus:outline-none focus:ring-2 focus:ring-[#0f684f]/40" />
          </label>

          <label class="block">
            <span class="block text-[0.9rem] font-semibold text-[#0a4936]">Duration (seconds)</span>
            <input type="number" name="duration" value="1" min="0" required
                   class="mt-1 w-full rounded-xl border border-[#f0d9d2] bg-white px-3 py-2
                          focus:outline-none focus:ring-2 focus:ring-[#0f684f]/40" />
          </label>

          <label class="block">
            <span class="block text-[0.9rem] font-semibold text-[#0a4936]">Intensity (1–10)</span>
            <input type="number" name="intensity" value="5" min="1" max="10" required
                   class="mt-1 w-full rounded-xl border border-[#f0d9d2] bg-white px-3 py-2
                          focus:outline-none focus:ring-2 focus:ring-[#0f684f]/40" />
          </label>

          <label class="inline-flex items-center gap-2 mt-1 md:col-span-1">
            <input type="checkbox" name="self_reported"
                   class="h-4 w-4 rounded border border-[#f0d9d2] text-[#0f684f] focus:ring-[#0f684f]/40" />
            <span class="text-[0.95rem] font-semibold text-[#0a4936]">Self-reported?</span>
          </label>

          <div class="lg:col-span-1 md:col-span-2">
            <button type="submit"
                    class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-full
                           bg-[#0f684f] text-white font-semibold shadow-[0_6px_16px_rgba(0,0,0,0.12)]
                           hover:bg-[#0a4936]">
              Submit log
            </button>
          </div>
        </div>
      </form>
    </section>

    <hr class="my-8 border-0 border-t border-[#f0d9d2]" />

    <!-- List -->
    <section class="bg-white border border-[#f0d9d2] rounded-2xl p-6 shadow-[0_6px_18px_rgba(0,0,0,0.05)]"
             aria-labelledby="recent-heading">
      <h2 id="recent-heading" class="text-[1.25rem] font-bold text-[#0f684f] mb-3">
        Recent tic logs
      </h2>

      <?php if ($delete_msg): ?>
        <div class="mb-4 rounded-xl border border-[#ffd7b3] bg-[#fff0e0] px-4 py-3 text-[#384341]">
          <?= htmlspecialchars($delete_msg) ?>
        </div>
      <?php endif; ?>

      <?php if (empty($list)): ?>
        <p class="text-[#5b6664]">No logs found.</p>
      <?php else: ?>
        <div class="overflow-x-auto rounded-xl border border-[#f0d9d2]">
          <table class="min-w-full border-collapse bg-white text-[0.95rem]">
            <thead class="bg-[#fff0e0] text-[#0a4936]">
              <tr>
                <th scope="col" class="text-left px-3 py-2 font-bold">ID</th>
                <th scope="col" class="text-left px-3 py-2 font-bold">Patient</th>
                <th scope="col" class="text-left px-3 py-2 font-bold">Type</th>
                <th scope="col" class="text-left px-3 py-2 font-bold">Duration (s)</th>
                <th scope="col" class="text-left px-3 py-2 font-bold">Intensity</th>
                <th scope="col" class="text-left px-3 py-2 font-bold">Source</th>
                <th scope="col" class="text-left px-3 py-2 font-bold">Logged at</th>
                <th scope="col" class="text-left px-3 py-2 font-bold">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-[#f0d9d2]">
              <?php foreach ($list as $row): ?>
                <tr class="hover:bg-[#fffdf7]">
                  <td class="px-3 py-2"><?= (int)$row['Tic_ID'] ?></td>
                  <td class="px-3 py-2"><?= (int)$row['Patient_ID'] ?></td>
                  <td class="px-3 py-2"><?= htmlspecialchars($row['Type_Description']) ?></td>
                  <td class="px-3 py-2"><?= (int)$row['Duration'] ?></td>
                  <td class="px-3 py-2"><?= (int)$row['Intensity'] ?></td>
                  <td class="px-3 py-2"><?= ((int)$row['Self-reported']) ? 'Self-reported' : 'Caregiver/admin' ?></td>
                  <td class="px-3 py-2"><?= date('Y-m-d H:i', strtotime($row['created_at'] ?? 'now')) ?></td>
                  <td class="px-3 py-2">
                    <a href="?delete=<?= (int)$row['Tic_ID'] ?>"
                       class="inline-flex items-center px-4 py-2 rounded-full bg-red-600 text-white font-semibold
                              hover:bg-red-700"
                       onclick="return confirm('Are you sure you want to permanently delete this tic log entry?');">
                      Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <footer class="mt-6 bg-[#0d5b43] text-white border-t-2 border-[#0a4936]">
    <div class="max-w-[1120px] mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-3 text-[0.95rem]">
      <span class="leading-snug">TicTracker – Mind You Up Portal</span>
      <span class="leading-snug">For clinical use and ongoing support in tic disorders.</span>

      <span class="flex flex-wrap items-center gap-2">
        <a href="/ISP_MindYouUp/Privacy-Policy.pdf" target="_blank" rel="noopener noreferrer"
           class="text-white underline hover:no-underline">
          Privacy Policy
        </a>
        <span aria-hidden="true">·</span>
        <a href="/ISP_MindYouUp/Terms-Conditions.pdf" target="_blank" rel="noopener noreferrer"
           class="text-white underline hover:no-underline">
          Terms &amp; Conditions
        </a>
      </span>
    </div>
  </footer>
</body>
</html>
