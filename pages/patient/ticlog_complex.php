<?php
session_start();
include('../../config.php');

// Enable detailed MySQL error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Only allow logged-in patients
if (!isset($_SESSION['User_ID']) || $_SESSION['Role'] !== 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";

// Helper: validate and sanitize a string from POST
function safeStr($arr, $key) {
    return isset($arr[$key]) ? trim($arr[$key]) : "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $patient_id = $_SESSION['User_ID'];

    // "No tics today" shortcut
    if (isset($_POST['no_tics'])) {
        $sql = "INSERT INTO tic_log (Patient_ID, Type_Description, Muscle_Group, Duration, Intensity, Describe_Text, `Self-reported`)
                VALUES (?, 'No Tics Today', NULL, 'No tics', 0, 'Patient reported no tics today.', 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $message = "<p style='color:green; font-weight:bold;'>✅ Recorded: No tics today!</p>";
    }
    // Saving multiple tics: we expect a JSON array of tics in tics_json
    elseif (!empty($_POST['tics_json'])) {
        // Parse tics JSON
        $tics_json = $_POST['tics_json'];
        $tics = json_decode($tics_json, true);

        // Global fields that apply to all tics in this submission
        $intensity = isset($_POST['intensity']) ? intval($_POST['intensity']) : 0;
        $duration = isset($_POST['duration']) ? trim($_POST['duration']) : ''; // string label like "Less than 10 seconds"
        $self_reported = (isset($_POST['self_reported']) && $_POST['self_reported'] === 'patient') ? 1 : 0;

        if (!is_array($tics) || count($tics) === 0) {
            $message = "<p style='color:red;'>❌ No tics to save. Please add at least one tic before saving.</p>";
        } else {
            // Prepare insert statement once
            $sql = "INSERT INTO tic_log (Patient_ID, Type_Description, Muscle_Group, Duration, Intensity, Describe_Text, `Self-reported`)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $message = "<p style='color:red;'>❌ Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
            } else {
                $inserted = 0;
                foreach ($tics as $t) {
                    // each tic array should have: type, muscle, description
                    $type_description = isset($t['type']) ? trim($t['type']) : '';
                    $muscle_group = isset($t['muscle']) ? trim($t['muscle']) : null;
                    $describe_text = isset($t['desc']) ? trim($t['desc']) : '';

                    // If muscle_group is empty string, store NULL
                    $muscle_for_db = ($muscle_group === '') ? null : $muscle_group;

                    // bind parameters: i s s s i s i  (patient, type, muscle, duration, intensity, desc, self_reported)
                    // Note: duration is sent as a string label. Ensure your DB accepts it (VARCHAR). If not, we can change to numeric codes.
                    $stmt->bind_param("isssisi",
                        $patient_id,
                        $type_description,
                        $muscle_for_db,
                        $duration,
                        $intensity,
                        $describe_text,
                        $self_reported
                    );

                    if ($stmt->execute()) {
                        $inserted++;
                    } else {
                        // continue and report later
                    }
                }

                if ($inserted > 0) {
                    $message = "<p style='color:green; font-weight:bold;'>✅ Saved {$inserted} tic(s) successfully!</p>";
                } else {
                    $message = "<p style='color:red;'>❌ No tic was saved. Check database constraints.</p>";
                }

                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Complex Tic Log - MindYouUp</title>
<link rel="stylesheet" href="../../style.css">
<style>
/* container & layout (kept similar to your previous styles) */
body { font-family: Arial, sans-serif; background: #f2f6f9; }
.container { max-width: 820px; margin: 30px auto; padding: 18px; background: #fff; border-radius: 12px; box-shadow: 0 6px 22px rgba(0,0,0,0.06); }
h2 { text-align:center; color:#333; margin-bottom:14px; }

/* inputs */
.row { display:flex; gap:12px; align-items:flex-start; }
.col { flex:1; min-width:0; }
label { display:block; margin-top:8px; font-weight:600; }
select, textarea, input[type="text"], input[type="number"] { width:100%; padding:8px 10px; border-radius:8px; border:1px solid #d1d5db; box-sizing:border-box; }
textarea { min-height:68px; resize:vertical; }

/* slider */
.slider-value { text-align:center; margin-top:6px; font-weight:600; }

/* buttons */
.btn { padding:10px 14px; border-radius:10px; border:none; cursor:pointer; font-weight:600; }
.btn-add { background:#0ea5a4; color:white; }
.btn-save { background:#2dd4bf; color:#043; }
.btn-remove { background:#ef4444; color:white; padding:6px 8px; border-radius:8px; }

/* tic list cards (compact) */
.tic-list { margin-top:18px; display:flex; flex-direction:column; gap:10px; }
.tic-card { padding:10px 12px; border-radius:10px; background:#fafafa; border:1px solid #ececec; display:flex; justify-content:space-between; align-items:center; gap:12px; }
.tic-card .left { display:flex; gap:12px; align-items:center; }
.tic-card .meta { font-size:14px; color:#111827; }
.tic-card .meta small { color:#6b7280; display:block; font-weight:500; }

/* small utilities */
.inline { display:inline-block; vertical-align:middle; }
.actions { display:flex; gap:8px; align-items:center; }

/* Self/Caregiver toggle (B1 style: green selected, grey unselected) */
/* Placed as compact square buttons below intensity/duration */
.self-toggle { display:flex; gap:10px; margin:12px 0; justify-content:flex-start; }
.self-toggle .sel-btn {
    min-width:110px;
    padding:8px 10px;
    border-radius:8px;
    border:0;
    cursor:pointer;
    font-weight:700;
    background:#ddd; /* unselected gray */
    color:#111;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    transition: all 0.12s ease;
}
.self-toggle .sel-btn.active {
    background:#4CAF50; /* selected green */
    color:#fff;
}

/* responsive */
@media (max-width:720px) {
  .row { flex-direction:column; }
  .self-toggle .sel-btn { min-width:48%; }
}
</style>
</head>
<body>
<div class="container">
    <h2>Complex Tic Log</h2>

    <?= $message ?>

    <!-- Form to add tics into a client-side list, then submit all -->
    <form id="ticForm" method="POST" action="">
        <div class="row">
            <div class="col">
                <label for="type_select">Type of Tic</label>
                <select id="type_select" name="type_select" onchange="handleAutoMuscle()" required>
                    <option value="">-- Select a Tic --</option>
                    <optgroup label="Motor Tics">
                        <option>Eye blinking</option>
                        <option>Eye movements</option>
                        <option>Nose movements</option>
                        <option>Mouth movements</option>
                        <option>Facial grimace</option>
                        <option>Head jerks or movements</option>
                        <option>Shoulder shrugs</option>
                        <option>Arm movements</option>
                        <option>Hand movements</option>
                        <option>Abdominal tensing</option>
                        <option>Leg, foot, or toe movements</option>
                        <option>Writing tics</option>
                        <option>Dystonic or abnormal postures</option>
                        <option>Copropraxia (obscene gestures)</option>
                        <option>Self-abusive behavior</option>
                        <option>Tic-related compulsive behaviors</option>
                    </optgroup>
                    <optgroup label="Vocal Tics">
                        <option>Sounds, noises (coughing, throat clearing, sniffing, or animal noises)</option>
                        <option>Complex phonic symptoms</option>
                        <option>Syllables</option>
                        <option>Words</option>
                        <option>Coprolalia (obscene words)</option>
                        <option>Echolalia (repeating others' words)</option>
                        <option>Palilalia (repeating your own words)</option>
                        <option>Blocking</option>
                        <option>Disinhibited speech</option>
                    </optgroup>
                </select>
            </div>

            <div class="col">
                <label for="muscle_select">Muscle Group (optional)</label>
                <select id="muscle_select" name="muscle_select">
                    <option value="">-- Optional: Select a Muscle Group --</option>
                    <option>Orbicularis oculi (eyes)</option>
                    <option>Facial muscles</option>
                    <option>Neck muscles</option>
                    <option>Shoulders / Upper trapezius</option>
                    <option>Arms / Hands</option>
                    <option>Abdominal muscles</option>
                    <option>Legs / Feet</option>
                    <option>Laryngeal muscles</option>
                    <option>Other</option>
                </select>
            </div>
        </div>

        <!-- Intensity & Duration (global for all tics added) -->
        <div class="row" style="margin-top:10px;">
            <div style="flex:1;">
                <label for="intensity">Intensity (0–10)</label>
                <input type="range" id="intensity" name="intensity" min="0" max="10" value="0" oninput="updateIntensityValue(this.value)">
                <div class="slider-value">Current intensity: <span id="intensityValue">0</span></div>
            </div>

            <div style="width:220px;">
                <label for="duration">Duration</label>
                <select id="duration" name="duration" required>
                    <option value="">-- Select Duration --</option>
                    <option>Less than 10 seconds</option>
                    <option>Less than 30 seconds</option>
                    <option>Less than a minute</option>
                    <option>More than a minute</option>
                </select>
            </div>
        </div>

        <!-- SELF/CAREGIVER toggle: B1 style (green selected, grey unselected) -->
        <div class="self-toggle" role="group" aria-label="Who is reporting?">
            <button type="button" id="btn_self" class="sel-btn active" data-value="patient" onclick="selectSelfCare(this)">Self</button>
            <button type="button" id="btn_caregiver" class="sel-btn" data-value="caregiver" onclick="selectSelfCare(this)">Caregiver</button>
        </div>

        <!-- description -->
        <div style="margin-top:10px;">
            <label for="desc">Describe the tic (short)</label>
            <textarea id="desc" name="desc" placeholder="Optional short note..."></textarea>
        </div>

        <!-- add tic to list -->
        <div style="margin-top:10px; display:flex; gap:10px;">
            <button type="button" class="btn btn-add" onclick="addTic()">+ Add Tic</button>
            <div style="flex:1"></div>
            <!-- Save all tics (submits tics_json hidden field) -->
            <button type="button" class="btn btn-save" onclick="saveAll()">💾 Save All Tics</button>
        </div>

        <!-- hidden input that will hold JSON of added tics -->
        <input type="hidden" name="tics_json" id="tics_json" value="">
        <!-- hidden self_reported option (keeps same toggle behavior you had) -->
        <input type="hidden" name="self_reported" id="self_reported" value="patient">
    </form>

    <!-- list of added tics -->
    <div class="tic-list" id="ticList"></div>

    <!-- No tics today button -->
    <form method="POST" style="margin-top:12px;">
        <button type="submit" name="no_tics" class="btn no-tics-btn">📅 No Tics Today</button>
    </form>
</div>

<script>
// client-side tic list
let tics = [];

// auto map for muscle group
const autoMap = {
    "Eye blinking": "Orbicularis oculi (eyes)",
    "Eye movements": "Orbicularis oculi (eyes)",
    "Nose movements": "Facial muscles",
    "Mouth movements": "Facial muscles",
    "Facial grimace": "Facial muscles",
    "Head jerks or movements": "Neck muscles",
    "Shoulder shrugs": "Shoulders / Upper trapezius",
    "Arm movements": "Arms / Hands",
    "Hand movements": "Arms / Hands",
    "Abdominal tensing": "Abdominal muscles",
    "Leg, foot, or toe movements": "Legs / Feet"
};

const vocalList = [
    "Sounds, noises (coughing, throat clearing, sniffing, or animal noises)",
    "Complex phonic symptoms",
    "Syllables",
    "Words",
    "Coprolalia (obscene words)",
    "Echolalia (repeating others' words)",
    "Palilalia (repeating your own words)",
    "Blocking",
    "Disinhibited speech"
];

function handleAutoMuscle(){
    const type = document.getElementById('type_select').value;
    const muscleSelect = document.getElementById('muscle_select');

    if (vocalList.includes(type)) {
        // vocal → auto-select laryngeal muscles but keep visible
        muscleSelect.value = "Laryngeal muscles";
    } else if (autoMap[type]) {
        muscleSelect.value = autoMap[type];
    } else {
        muscleSelect.value = "";
    }
}

function updateIntensityValue(v){
    document.getElementById('intensityValue').innerText = v;
}

// add tic to client-side list
function addTic(){
    const type = document.getElementById('type_select').value.trim();
    const muscle = document.getElementById('muscle_select').value.trim();
    const desc = document.getElementById('desc').value.trim();

    if (!type) {
        alert("Please choose a tic type before adding.");
        return;
    }

    // push into array
    tics.push({ type: type, muscle: muscle, desc: desc });

    // render list
    renderList();

    // clear type and desc for next item (muscle left as is)
    document.getElementById('type_select').value = "";
    document.getElementById('desc').value = "";
}

// render the compact card list
function renderList(){
    const container = document.getElementById('ticList');
    container.innerHTML = '';
    if (tics.length === 0) {
        container.innerHTML = '<p style="color:#6b7280;">No tics added yet.</p>';
        return;
    }
    tics.forEach((t, idx) => {
        const card = document.createElement('div');
        card.className = 'tic-card';
        card.innerHTML = `
            <div class="left">
                <div class="meta">
                    <strong>${escapeHtml(t.type)}</strong>
                    <small>${t.muscle ? escapeHtml(t.muscle) : 'Muscle: —'}</small>
                    ${t.desc ? `<small style="margin-top:4px;">${escapeHtml(t.desc)}</small>` : ''}
                </div>
            </div>
            <div class="actions">
                <button class="btn-remove" onclick="removeTic(${idx})">Remove</button>
            </div>
        `;
        container.appendChild(card);
    });
}

function removeTic(i){
    tics.splice(i,1);
    renderList();
}

// Save all: populate hidden tics_json value and submit form via POST
function saveAll(){
    if (tics.length === 0) {
        alert("Add at least one tic before saving.");
        return;
    }

    // set hidden JSON
    document.getElementById('tics_json').value = JSON.stringify(tics);

    // set self_reported default from hidden field (unchanged), it's already present
    // set intensity/duration values are in visible inputs so they will be posted

    // submit the form (create a form element to POST cleanly)
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    // tics_json
    const inTics = document.createElement('input');
    inTics.name = 'tics_json';
    inTics.value = document.getElementById('tics_json').value;
    form.appendChild(inTics);

    // intensity
    const inIntensity = document.createElement('input');
    inIntensity.name = 'intensity';
    inIntensity.value = document.getElementById('intensity').value;
    form.appendChild(inIntensity);

    // duration
    const inDuration = document.createElement('input');
    inDuration.name = 'duration';
    inDuration.value = document.getElementById('duration').value;
    form.appendChild(inDuration);

    // self_reported
    const inSelf = document.createElement('input');
    inSelf.name = 'self_reported';
    inSelf.value = document.getElementById('self_reported').value;
    form.appendChild(inSelf);

    document.body.appendChild(form);
    form.submit();
}

// Self/Caregiver toggle logic (B1)
function selectSelfCare(btn) {
    // remove active from both
    document.getElementById('btn_self').classList.remove('active');
    document.getElementById('btn_caregiver').classList.remove('active');

    // add active to clicked
    btn.classList.add('active');

    // update hidden field
    const val = btn.getAttribute('data-value');
    document.getElementById('self_reported').value = val;
}

// small helper
function escapeHtml(s){
    if(!s) return '';
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// initialise: render empty list
renderList();
</script>
</body>
</html>
