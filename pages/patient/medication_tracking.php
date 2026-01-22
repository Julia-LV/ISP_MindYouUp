<?php
session_start();
require_once __DIR__ . '/../../config.php';

$userId = $_SESSION['user_id'] ?? null;
$isLoggedIn = (bool)$userId;

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

// No need to create or alter table, handled in DB schema

// Handle add medication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$isLoggedIn) {
        $message = 'Please log in to manage medications.';
    } else {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name'] ?? '');
            $reminderDate = $_POST['reminder_date'] ?? '';
            $reminderTime = $_POST['reminder_time'] ?? '';
            $medicationTime = null;
            if ($reminderDate && $reminderTime) {
                $medicationTime = $reminderDate . ' ' . $reminderTime . ':00';
            }

            if ($name === '') {
                $message = 'Medication name is required.';
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO track_medication (Patient_ID, Medication_Name, Medication_Time, Medication_Status) VALUES (?, ?, ?, 0)");
                mysqli_stmt_bind_param($stmt, 'iss', $userId, $name, $medicationTime);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                header("Location: " . basename(__FILE__));
                exit;
            }
        } elseif ($_POST['action'] === 'toggle_taken') {
            $medId = (int)$_POST['med_id'];
            $taken = (int)$_POST['taken'];
            $stmt = mysqli_prepare($conn, "UPDATE track_medication SET Medication_Status = ? WHERE Track_Medication_ID = ? AND Patient_ID = ?");
            mysqli_stmt_bind_param($stmt, 'iii', $taken, $medId, $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            exit; // AJAX response
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && $isLoggedIn) {
    $toDelete = (int)$_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM track_medication WHERE Track_Medication_ID = ? AND Patient_ID = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $toDelete, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: " . basename(__FILE__));
    exit;
}

// Fetch medications
$medsNotTaken = [];
$medsTaken = [];
if ($isLoggedIn) {
    $stmt = mysqli_prepare($conn, "SELECT Track_Medication_ID, Medication_Name, Medication_Time, Medication_Status FROM track_medication WHERE Patient_ID = ? ORDER BY Medication_Name ASC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        if ($row['Medication_Status']) {
            $medsTaken[] = $row;
        } else {
            $medsNotTaken[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Medications</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Custom CSS -->
    <link href="../../CSS/medication_tracking.css?v=9" rel="stylesheet">
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../../components/header_component.php'; ?>

    <div class="main-content">
        <!-- Page Header (outside box, full width) -->
        <div class="page-header">
            <h1 class="page-title">Track Medication</h1>
            <p class="page-subtitle">Manage your daily medications</p>
        </div>

        <div class="med-wrapper">
            <!-- Medication List -->
        <div class="med-list">
            <?php if (empty($medsNotTaken) && empty($medsTaken)): ?>
                <div class="empty-state">
                    <p>No medications yet.</p>
                    <p class="small">Tap the + button to add your first medication.</p>
                </div>
            <?php else: ?>
                <!-- Not taken medications -->
                <?php foreach ($medsNotTaken as $med): ?>
                    <div class="med-card" data-id="<?= (int)$med['Track_Medication_ID'] ?>" onclick="toggleSelectMed(this, <?= (int)$med['Track_Medication_ID'] ?>)">
                        <div class="med-select-checkbox">
                            <span class="select-checkmark"></span>
                        </div>
                        <div class="med-info">
                            <span class="med-name"><?= htmlspecialchars($med['Medication_Name']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Taken today section -->
                <?php if (!empty($medsTaken)): ?>
                    <div class="taken-section">
                        <button class="taken-toggle" onclick="toggleTakenSection()">
                            <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <span>Medication took today</span>
                        </button>
                        <div class="taken-list" id="takenList">
                            <?php foreach ($medsTaken as $med): ?>
                                <div class="med-card taken" data-id="<?= (int)$med['Track_Medication_ID'] ?>" onclick="toggleSelectMed(this, <?= (int)$med['Track_Medication_ID'] ?>)">
                                    <div class="med-select-checkbox">
                                        <span class="select-checkmark"></span>
                                    </div>
                                    <div class="med-info">
                                        <span class="med-name"><?= htmlspecialchars($med['Medication_Name']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Add Button - bottom right corner -->
            <div class="add-btn-container">
                <?php $label = 'Add Medication'; $type = 'button'; $variant = 'primary'; $width = 'w-auto'; $onclick = 'openAddModal()'; include __DIR__ . '/../../components/button.php'; ?>
            </div>

        <!-- Floating Delete Button -->
        <button class="fab-delete" id="fabDelete" onclick="deleteSelectedMeds()">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 448 512" fill="white">
                <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/>
            </svg>
        </button>
    </div>
</div>

    <!-- Add/Edit Modal (Combined) -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <form method="post" id="medForm">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <!-- Medication Name -->
                    <div class="field-group">
                        <label>Medication Name</label>
                        <input type="text" name="name" id="medName" class="med-input" placeholder="Enter medication name..." required>
                    </div>

                    <!-- Times a Day -->
                    <div class="field-group">
                        <label>Times a Day</label>
                        <input type="number" id="timesPerDay" name="times_per_day" class="form-control" min="1" max="10" value="1" placeholder="Enter number...">
                    </div>

                    <!-- Date -->
                    <div class="field-group">
                        <label>Date</label>
                        <input type="date" id="reminderDate" name="reminder_date" class="form-control">
                    </div>

                    <!-- Time -->
                    <div class="field-group">
                        <label>Time</label>
                        <input type="time" id="reminderTime" name="reminder_time" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <?php $label = 'Cancel'; $type = 'button'; $variant = 'secondary'; $width = 'w-auto'; $onclick = 'closeModal()'; include __DIR__ . '/../../components/button.php'; ?>
                    <?php $label = 'Done'; $type = 'submit'; $variant = 'primary'; $width = 'w-auto'; include __DIR__ . '/../../components/button.php'; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
    let selectedMeds = [];

    function toggleSelectMed(el, medId) {
        el.classList.toggle('selected');
        
        if (el.classList.contains('selected')) {
            if (!selectedMeds.includes(medId)) {
                selectedMeds.push(medId);
            }
        } else {
            selectedMeds = selectedMeds.filter(id => id !== medId);
        }
        
        // Show/hide delete button
        const fabDelete = document.getElementById('fabDelete');
        if (selectedMeds.length > 0) {
            fabDelete.classList.add('visible');
        } else {
            fabDelete.classList.remove('visible');
        }
    }

    function deleteSelectedMeds() {
        if (selectedMeds.length === 0) return;
        
        const message = selectedMeds.length === 1 
            ? 'Delete this medication?' 
            : 'Delete ' + selectedMeds.length + ' medications?';
        
        if (confirm(message)) {
            // Delete first selected (for multiple, would need backend change)
            window.location.href = '?delete=' + selectedMeds[0];
        }
    }

    function openAddModal() {
        // Reset form values
        document.getElementById('medName').value = '';
        document.getElementById('timesPerDay').value = '1';
        document.getElementById('modalOverlay').classList.add('active');
    }

    function closeModal() {
        document.getElementById('modalOverlay').classList.remove('active');
    }

    function toggleTaken(medId, taken) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=toggle_taken&med_id=' + medId + '&taken=' + (taken ? 1 : 0)
        }).then(() => location.reload());
    }

    function toggleTakenSection() {
        const list = document.getElementById('takenList');
        const chevron = document.querySelector('.taken-toggle .chevron');
        list.classList.toggle('collapsed');
        chevron.classList.toggle('rotated');
    }

    function openEditModal(medId, medName) {
        // For now, just open add modal with name filled
        document.getElementById('medName').value = medName;
        document.getElementById('modalOverlay').classList.add('active');
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
