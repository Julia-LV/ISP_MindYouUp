<?php
session_start();
require_once __DIR__ . '/../../config.php';

$userId = $_SESSION['user_id'] ?? null;
$isLoggedIn = (bool)$userId;

$message = '';

// Ensure medications table exists with updated structure
$createSql = "
CREATE TABLE IF NOT EXISTS medications (
  Medication_ID INT AUTO_INCREMENT PRIMARY KEY,
  User_ID INT NOT NULL,
  Name VARCHAR(255) NOT NULL,
  Reminder_DateTime DATETIME NULL,
  Repeat_Option VARCHAR(20) DEFAULT 'Never',
  Taken_Today TINYINT(1) DEFAULT 0,
  Created DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
mysqli_query($conn, $createSql);

// Check if columns exist, add if missing
$checkCol = mysqli_query($conn, "SHOW COLUMNS FROM medications LIKE 'Taken_Today'");
if (mysqli_num_rows($checkCol) == 0) {
    mysqli_query($conn, "ALTER TABLE medications ADD COLUMN Taken_Today TINYINT(1) DEFAULT 0");
}
$checkCol2 = mysqli_query($conn, "SHOW COLUMNS FROM medications LIKE 'Reminder_DateTime'");
if (mysqli_num_rows($checkCol2) == 0) {
    mysqli_query($conn, "ALTER TABLE medications ADD COLUMN Reminder_DateTime DATETIME NULL");
}
$checkCol3 = mysqli_query($conn, "SHOW COLUMNS FROM medications LIKE 'Repeat_Option'");
if (mysqli_num_rows($checkCol3) == 0) {
    mysqli_query($conn, "ALTER TABLE medications ADD COLUMN Repeat_Option VARCHAR(20) DEFAULT 'Never'");
}

// Handle add medication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$isLoggedIn) {
        $message = 'Please log in to manage medications.';
    } else {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name'] ?? '');
            $reminderDate = $_POST['reminder_date'] ?? '';
            $reminderTime = $_POST['reminder_time'] ?? '';
            $repeatOption = $_POST['repeat_option'] ?? 'Never';

            if ($name === '') {
                $message = 'Medication name is required.';
            } else {
                $reminderDateTime = null;
                if ($reminderDate && $reminderTime) {
                    $reminderDateTime = $reminderDate . ' ' . $reminderTime . ':00';
                }
                
                $stmt = mysqli_prepare($conn, "INSERT INTO medications (User_ID, Name, Reminder_DateTime, Repeat_Option) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'isss', $userId, $name, $reminderDateTime, $repeatOption);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                header("Location: " . basename(__FILE__));
                exit;
            }
        } elseif ($_POST['action'] === 'toggle_taken') {
            $medId = (int)$_POST['med_id'];
            $taken = (int)$_POST['taken'];
            $stmt = mysqli_prepare($conn, "UPDATE medications SET Taken_Today = ? WHERE Medication_ID = ? AND User_ID = ?");
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
    $stmt = mysqli_prepare($conn, "DELETE FROM medications WHERE Medication_ID = ? AND User_ID = ?");
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
    $stmt = mysqli_prepare($conn, "SELECT Medication_ID, Name, Reminder_DateTime, Repeat_Option, Taken_Today FROM medications WHERE User_ID = ? ORDER BY Reminder_DateTime ASC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        if ($row['Taken_Today']) {
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
    <!-- TailwindCSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="../../CSS/medication_tracking.css" rel="stylesheet">
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../../components/header_component.php'; ?>

    <div class="main-content">
        <div class="med-wrapper">
            <!-- Header -->
            <div class="med-header">
                <h1>Track Medicines</h1>
            </div>

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
                    <div class="med-card" data-id="<?= (int)$med['Medication_ID'] ?>" onclick="toggleSelectMed(this, <?= (int)$med['Medication_ID'] ?>)">
                        <div class="med-select-checkbox">
                            <span class="select-checkmark"></span>
                        </div>
                        <div class="med-info">
                            <span class="med-name"><?= htmlspecialchars($med['Name']) ?></span>
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
                                <div class="med-card taken" data-id="<?= (int)$med['Medication_ID'] ?>" onclick="toggleSelectMed(this, <?= (int)$med['Medication_ID'] ?>)">
                                    <div class="med-select-checkbox">
                                        <span class="select-checkmark"></span>
                                    </div>
                                    <div class="med-info">
                                        <span class="med-name"><?= htmlspecialchars($med['Name']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Floating Buttons -->
        <div class="fab-buttons">
            <button class="fab-delete" id="fabDelete" onclick="deleteSelectedMeds()">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 448 512" fill="white">
                    <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                </svg>
            </button>
            <button class="fab-add" onclick="openAddModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

    <!-- Add/Edit Modal -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <form method="post" id="medForm">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <input type="text" name="name" id="medName" class="med-input" placeholder="Medication name..." required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="reminder-btn" onclick="openReminderPicker()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        Set reminder
                    </button>
                    <button type="submit" class="done-btn">Done</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reminder Picker Modal -->
    <div class="reminder-overlay" id="reminderOverlay" onclick="closeReminderPicker()">
        <div class="reminder-content" onclick="event.stopPropagation()">
            <h3>Set reminder</h3>
            <p class="reminder-preview" id="reminderPreview">Select date and time</p>

            <div class="repeat-section">
                <label>Repeat</label>
                <select id="repeatOption" name="repeat_option" onchange="updateDateTimeFields()">
                    <option value="Never">Never</option>
                    <option value="Daily">Daily</option>
                    <option value="Weekly">Weekly</option>
                    <option value="Monthly">Monthly</option>
                </select>
            </div>

            <div class="datetime-picker" id="datetimePicker">
                <!-- Date field for Never option -->
                <div class="field-group" id="dateField">
                    <label>Date</label>
                    <input type="date" id="reminderDate" name="reminder_date" onchange="updateReminderPreview()">
                </div>
                
                <!-- Weekday field for Weekly option -->
                <div class="field-group" id="weekdayField" style="display:none;">
                    <label>Day of Week</label>
                    <select id="reminderWeekday" onchange="updateReminderPreview()">
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                        <option value="0">Sunday</option>
                    </select>
                </div>
                
                <!-- Month day field for Monthly option -->
                <div class="field-group" id="monthdayField" style="display:none;">
                    <label>Day of Month</label>
                    <select id="reminderMonthday" onchange="updateReminderPreview()">
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <!-- Time field always visible -->
                <div class="field-group" id="timeField">
                    <label>Time</label>
                    <input type="time" id="reminderTime" name="reminder_time" onchange="updateReminderPreview()">
                </div>
            </div>

            <div class="reminder-actions">
                <button type="button" class="cancel-btn" onclick="closeReminderPicker()">Cancel</button>
                <button type="button" class="ok-btn" onclick="confirmReminder()">OK</button>
            </div>
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
        document.getElementById('medName').value = '';
        document.getElementById('reminderDate').value = '';
        document.getElementById('reminderTime').value = '';
        document.getElementById('repeatOption').value = 'Never';
        document.getElementById('modalOverlay').classList.add('active');
    }

    function closeModal() {
        document.getElementById('modalOverlay').classList.remove('active');
    }

    function openReminderPicker() {
        // Set default time to now
        const now = new Date();
        const timeStr = now.toTimeString().slice(0, 5);
        
        if (!document.getElementById('reminderTime').value) {
            document.getElementById('reminderTime').value = timeStr;
        }
        
        // Reset to Never and update fields
        document.getElementById('repeatOption').value = 'Never';
        updateDateTimeFields();
        
        // Set default date to today for Never option
        if (!document.getElementById('reminderDate').value) {
            document.getElementById('reminderDate').value = now.toISOString().split('T')[0];
        }
        
        updateReminderPreview();
        document.getElementById('reminderOverlay').classList.add('active');
    }

    function closeReminderPicker() {
        document.getElementById('reminderOverlay').classList.remove('active');
    }

    function updateDateTimeFields() {
        const repeatOption = document.getElementById('repeatOption').value;
        const dateField = document.getElementById('dateField');
        const weekdayField = document.getElementById('weekdayField');
        const monthdayField = document.getElementById('monthdayField');
        
        // Hide all optional fields first
        dateField.style.display = 'none';
        weekdayField.style.display = 'none';
        monthdayField.style.display = 'none';
        
        // Show appropriate field based on repeat option
        switch (repeatOption) {
            case 'Never':
                dateField.style.display = 'block';
                break;
            case 'Daily':
                // Only time needed, no extra fields
                break;
            case 'Weekly':
                weekdayField.style.display = 'block';
                break;
            case 'Monthly':
                monthdayField.style.display = 'block';
                break;
        }
        
        updateReminderPreview();
    }

    function updateReminderPreview() {
        const repeatOption = document.getElementById('repeatOption').value;
        const time = document.getElementById('reminderTime').value;
        let previewText = 'Select time';
        
        if (!time) {
            document.getElementById('reminderPreview').textContent = previewText;
            return;
        }
        
        const timeFormatted = formatTime(time);
        
        switch (repeatOption) {
            case 'Never':
                const date = document.getElementById('reminderDate').value;
                if (date) {
                    const dt = new Date(date + 'T' + time);
                    const options = { weekday: 'short', month: 'short', day: 'numeric' };
                    previewText = dt.toLocaleDateString('en-US', options) + ' at ' + timeFormatted;
                }
                break;
            case 'Daily':
                previewText = 'Every day at ' + timeFormatted;
                break;
            case 'Weekly':
                const weekday = document.getElementById('reminderWeekday');
                const dayName = weekday.options[weekday.selectedIndex].text;
                previewText = 'Every ' + dayName + ' at ' + timeFormatted;
                break;
            case 'Monthly':
                const monthday = document.getElementById('reminderMonthday').value;
                previewText = 'Every month on day ' + monthday + ' at ' + timeFormatted;
                break;
        }
        
        document.getElementById('reminderPreview').textContent = previewText;
    }

    function formatTime(timeStr) {
        const [hours, minutes] = timeStr.split(':');
        const h = parseInt(hours);
        const ampm = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        return h12 + ':' + minutes + ' ' + ampm;
    }

    function confirmReminder() {
        const form = document.getElementById('medForm');
        const repeatOption = document.getElementById('repeatOption').value;
        
        // Add repeat option
        let repeatInput = form.querySelector('input[name="repeat_option"]');
        if (!repeatInput) {
            repeatInput = document.createElement('input');
            repeatInput.type = 'hidden';
            repeatInput.name = 'repeat_option';
            form.appendChild(repeatInput);
        }
        repeatInput.value = repeatOption;
        
        // Add time
        let timeInput = form.querySelector('input[name="reminder_time"]');
        if (!timeInput) {
            timeInput = document.createElement('input');
            timeInput.type = 'hidden';
            timeInput.name = 'reminder_time';
            form.appendChild(timeInput);
        }
        timeInput.value = document.getElementById('reminderTime').value;
        
        // Add date based on repeat option
        let dateInput = form.querySelector('input[name="reminder_date"]');
        if (!dateInput) {
            dateInput = document.createElement('input');
            dateInput.type = 'hidden';
            dateInput.name = 'reminder_date';
            form.appendChild(dateInput);
        }
        
        switch (repeatOption) {
            case 'Never':
                dateInput.value = document.getElementById('reminderDate').value;
                break;
            case 'Daily':
                // For daily, use today's date as starting point
                dateInput.value = new Date().toISOString().split('T')[0];
                break;
            case 'Weekly':
                // Store weekday value (0-6) in a special field
                let weekdayInput = form.querySelector('input[name="reminder_weekday"]');
                if (!weekdayInput) {
                    weekdayInput = document.createElement('input');
                    weekdayInput.type = 'hidden';
                    weekdayInput.name = 'reminder_weekday';
                    form.appendChild(weekdayInput);
                }
                weekdayInput.value = document.getElementById('reminderWeekday').value;
                dateInput.value = new Date().toISOString().split('T')[0];
                break;
            case 'Monthly':
                // Store month day in a special field
                let monthdayInput = form.querySelector('input[name="reminder_monthday"]');
                if (!monthdayInput) {
                    monthdayInput = document.createElement('input');
                    monthdayInput.type = 'hidden';
                    monthdayInput.name = 'reminder_monthday';
                    form.appendChild(monthdayInput);
                }
                monthdayInput.value = document.getElementById('reminderMonthday').value;
                dateInput.value = new Date().toISOString().split('T')[0];
                break;
        }
        
        closeReminderPicker();
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
