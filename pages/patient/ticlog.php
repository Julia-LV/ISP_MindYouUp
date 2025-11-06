<?php
session_start();
include('../../config.php');

// ✅ Enable detailed MySQL error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ✅ Only allow logged-in patients
if (!isset($_SESSION['User_ID']) || $_SESSION['Role'] !== 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $patient_id = $_SESSION['User_ID'];

    // ✅ Check if "No Tics Today" button was pressed
    if (isset($_POST['no_tics'])) {
        $sql = "INSERT INTO tic_log (Patient_ID, Type_Description, Muscle_Group, Duration, Intensity, Describe_Text, `Self-reported`)
                VALUES (?, 'No Tics Today', NULL, 0, 0, 'Patient reported no tics today.', 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $message = "<p style='color:green; font-weight:bold;'>✅ Recorded: No tics today!</p>";
    } 
    else {
        // ✅ Normal tic submission
        $muscle_group = $_POST['muscle_group'] ?? null;
        $duration = intval($_POST['duration']);
        $intensity = intval($_POST['intensity']);
        $describe_text = trim($_POST['describe_text']);
        $self_reported = isset($_POST['self_reported']) ? 1 : 0;
        $tic_type = $_POST['tic_type'] ?? 'motor';

        // ✅ Pick correct type description
        if ($tic_type === 'motor') {
            $type_description = trim($_POST['type_description_motor'] ?? '');
        } else {
            $type_description = trim($_POST['type_description_vocal'] ?? '');
        }

        $sql = "INSERT INTO tic_log (Patient_ID, Type_Description, Muscle_Group, Duration, Intensity, Describe_Text, `Self-reported`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issiisi",
            $patient_id, $type_description, $muscle_group,
            $duration, $intensity, $describe_text, $self_reported
        );
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = "<p style='color:green; font-weight:bold;'>✅ Tic successfully logged!</p>";
        } else {
            $message = "<p style='color:red;'>❌ No row was inserted. Check your database constraints.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tic Log - MindYouUp</title>
<link rel="stylesheet" href="../../style.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f2f6f9;
    }
    .container {
        max-width: 700px;
        margin: 40px auto;
        padding: 20px;
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }
    label { display:block; margin-top:10px; font-weight:600; }
    select, textarea, input, button {
        width:100%; padding:8px; margin-top:5px;
        border-radius:8px; border:1px solid #ccc;
    }
    button {
        background:#4CAF50; color:white; cursor:pointer;
        margin-top:15px; border:none;
        font-weight: bold;
    }
    button:hover { background:#45a049; }
    .tic-type-toggle {
        display:flex; justify-content:center; gap:10px; margin-bottom:20px;
    }
    .tic-type-toggle button {
        flex:1; padding:10px; border:none; border-radius:10px;
        background:#ddd; font-weight:bold; cursor:pointer;
        transition: all 0.2s ease;
    }
    .tic-type-toggle button.active {
        background:#4CAF50; color:white;
    }
    .slider-value { text-align:center; font-weight:bold; }
    .no-tics-btn {
        background:#777; color:white;
        width:100%; margin-top:25px; padding:10px;
        border-radius:10px; border:none;
        font-weight:bold;
    }
    .no-tics-btn:hover { background:#666; }
</style>

<script>
function switchTicType(type) {
    document.getElementById('tic_type').value = type;
    const motorList = document.getElementById('motor-list');
    const vocalList = document.getElementById('vocal-list');
    const muscleGroupContainer = document.getElementById('muscle-group-container');

    if (type === 'motor') {
        motorList.style.display = 'block';
        vocalList.style.display = 'none';
        muscleGroupContainer.style.display = 'block';
    } else {
        motorList.style.display = 'none';
        vocalList.style.display = 'block';
        muscleGroupContainer.style.display = 'none';
    }

    document.getElementById('btn-motor').classList.toggle('active', type === 'motor');
    document.getElementById('btn-vocal').classList.toggle('active', type === 'vocal');
}

function updateSliderValue(value) {
    document.getElementById("intensityValue").innerText = value;
}

// ✅ Keep select enabled so value is submitted
function handleMotorTicChange() {
    const ticSelect = document.getElementById('type_description_motor');
    const muscleGroupSelect = document.getElementById('muscle_group');
    const ticValue = ticSelect.value;

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

    if (autoMap[ticValue]) {
        muscleGroupSelect.value = autoMap[ticValue];
        muscleGroupSelect.setAttribute("data-locked", "true");
    } else {
        muscleGroupSelect.removeAttribute("data-locked");
        muscleGroupSelect.value = "";
    }
}
</script>
</head>

<body onload="switchTicType('motor')">
<div class="container">
    <h2>Tic Log</h2>
    <?= $message ?>

    <div class="tic-type-toggle">
        <button type="button" id="btn-motor" onclick="switchTicType('motor')">Motor Tic</button>
        <button type="button" id="btn-vocal" onclick="switchTicType('vocal')">Vocal Tic</button>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="tic_type" id="tic_type" value="motor">

        <!-- Motor Tic Options -->
        <div id="motor-list">
            <label for="type_description_motor">Type of Motor Tic</label>
            <select name="type_description_motor" id="type_description_motor" onchange="handleMotorTicChange()">
                <option value="">-- Select a Motor Tic --</option>
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
            </select>
        </div>

        <!-- Muscle Group Dropdown -->
        <div id="muscle-group-container">
            <label for="muscle_group">Muscle Group (optional)</label>
            <select id="muscle_group" name="muscle_group">
                <option value="">-- Optional: Select a Muscle Group --</option>
                <option value="Orbicularis oculi (eyes)">Orbicularis oculi (eyes)</option>
                <option value="Facial muscles">Facial muscles</option>
                <option value="Neck muscles">Neck muscles</option>
                <option value="Shoulders / Upper trapezius">Shoulders / Upper trapezius</option>
                <option value="Arms / Hands">Arms / Hands</option>
                <option value="Abdominal muscles">Abdominal muscles</option>
                <option value="Legs / Feet">Legs / Feet</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <!-- Vocal Tic Options -->
        <div id="vocal-list" style="display:none;">
            <label for="type_description_vocal">Type of Vocal Tic</label>
            <select name="type_description_vocal">
                <option value="">-- Select a Vocal Tic --</option>
                <option>Sounds, noises (coughing, throat clearing, sniffing, or animal noises)</option>
                <option>Complex phonic symptoms</option>
                <option>Syllables</option>
                <option>Words</option>
                <option>Coprolalia (obscene words)</option>
                <option>Echolalia (repeating others' words)</option>
                <option>Palilalia (repeating your own words)</option>
                <option>Blocking</option>
                <option>Disinhibited speech</option>
            </select>
        </div>

        <label for="duration">Duration (seconds)</label>
        <input type="number" name="duration" min="1" required>

        <label for="intensity">Intensity (0–10)</label>
        <input type="range" id="intensity" name="intensity" min="0" max="10" value="0" oninput="updateSliderValue(this.value)">
        <div class="slider-value">Current intensity: <span id="intensityValue">0</span></div>

        <label><input type="checkbox" name="self_reported"> Self-reported (checked = patient, unchecked = caregiver)</label>

        <label for="describe_text">Describe the tic</label>
        <textarea name="describe_text" rows="3" required></textarea>

        <button type="submit"> Save Log</button>
    </form>

    <!-- No Tics Today Button -->
    <form method="POST" action="">
        <button type="submit" name="no_tics" class="no-tics-btn"> No Tics Today</button>
    </form>
</div>
</body>
</html>
