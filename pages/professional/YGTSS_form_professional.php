<?php
/*
 * YGTSS_form_professional.php
 * Professional view for patient YGTSS (Yale Global Tic Severity Scale) results
 * Single detailed view - all submissions shown with full details
 */

session_start();
require_once __DIR__ . '/../../config.php';

// Set timezone
date_default_timezone_set('Europe/Lisbon');

// --- Security Check ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: ../auth/login.php");
    exit;
}
if ($_SESSION["role"] != "Professional") {
    header("Location: ../patient/home_patient.php");
    exit;
}

$professional_id = $_SESSION["user_id"];

// Get current user info
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

// Get selected patient ID from query string
$selected_patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;

// Get list of patients linked to this professional
$patients = [];
$patientStmt = mysqli_prepare($conn, "
    SELECT DISTINCT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.User_Image, u.Birthday
    FROM user_profile u
    INNER JOIN patient_professional_link ppl ON u.User_ID = ppl.Patient_ID
    WHERE ppl.Professional_ID = ? AND ppl.Connection_Status = 'Accepted'
    ORDER BY u.Last_Name, u.First_Name
");
if ($patientStmt) {
    mysqli_stmt_bind_param($patientStmt, 'i', $professional_id);
    mysqli_stmt_execute($patientStmt);
    $patientRes = mysqli_stmt_get_result($patientStmt);
    while ($row = mysqli_fetch_assoc($patientRes)) {
        $patients[] = $row;
    }
    mysqli_stmt_close($patientStmt);
}

// Get YGTSS results for selected patient
$results = [];
$patientName = '';
if ($selected_patient_id) {
    // Get patient name
    foreach ($patients as $p) {
        if ($p['User_ID'] == $selected_patient_id) {
            $patientName = $p['First_Name'] . ' ' . $p['Last_Name'];
            break;
        }
    }
    
    $resultStmt = mysqli_prepare($conn, "
        SELECT y.*, u.First_Name, u.Last_Name, u.Email, u.Birthday
        FROM ygtss_results y
        INNER JOIN user_profile u ON y.Patient_ID = u.User_ID
        WHERE y.Patient_ID = ?
        ORDER BY y.Submission_Date DESC
    ");
    if ($resultStmt) {
        mysqli_stmt_bind_param($resultStmt, 'i', $selected_patient_id);
        mysqli_stmt_execute($resultStmt);
        $resultRes = mysqli_stmt_get_result($resultStmt);
        while ($row = mysqli_fetch_assoc($resultRes)) {
            $results[] = $row;
        }
        mysqli_stmt_close($resultStmt);
    }
}

// A1 Questions mapping for symptom display
$a1_questions_map = [
    'Eye Movements' => [
        'Simple – Blinking, strabismus, quick eye turning, eye rolling, or eye widening',
        'Complex – Expressions of surprise, mocking, odd looks, or looking to the side',
    ],
    'Nose, Mouth, Tongue, or Facial Movements' => [
        'Simple – Nose twitching, tongue biting, lip chewing/licking, pouting, teeth grinding',
        'Complex – Nostril flaring, smiling, funny facial expressions, tongue protrusion',
    ],
    'Head Movements' => [
        'Simple – Touching shoulders with chin or lifting chin',
        'Simple – Throwing head backward',
    ],
    'Shoulder Movements' => [
        'Simple – Shrugging one shoulder',
        'Simple – Shrugging both shoulders',
    ],
    'Arm and Hand Movements' => [
        'Simple – Arm flex/extend, nail biting, finger tapping, knuckle cracking',
        'Complex – Running hand through hair, touching objects/people, writing tics',
    ],
    'Foot, Leg, and Toe Movements' => [
        'Simple – Kicking, hopping, knee bending, ankle movements, stomping',
        'Complex – Step forward/back, deep knee bend, squatting',
    ],
    'Abdominal, Trunk, and Pelvic Movements' => [
        'Simple – Tensing abdomen or buttocks',
    ],
    'Other Simple Motor Tics' => [
        'Simple – Other simple motor tics',
    ],
    'Other Complex Motor Tics' => [
        'Complex – Compulsive behaviors (touching, hitting, arranging)',
        'Complex – Stimulus-dependent tics',
        'Complex – Obscene or rude gestures',
        'Complex – Unusual postures',
        'Complex – Bending or twisting body',
        'Complex – Turning or spinning (pirouettes)',
        'Complex – Sudden, impulsive behaviors',
        'Complex – Behaviors that may injure others',
        'Complex – Self-injurious behaviors',
        'Complex – Other motor tics',
    ],
    'Simple Vocal Tics' => [
        'Simple – Coughing',
        'Simple – Throat clearing',
        'Simple – Sniffing',
        'Simple – Whistling',
        'Simple – Animal or bird sounds',
    ],
    'Complex Vocal Tics' => [
        'Complex – Other simple vocal tics',
        'Complex – Syllable sounds',
        'Complex – Obscene or rude words/phrases (Coprolalia)',
        'Complex – Words (not obscene)',
        'Complex – Echolalia (repeating others)',
        'Complex – Palilalia (repeating self)',
        'Complex – Other speech problems',
        'Complex – Pattern or sequence of vocal tic behavior',
    ],
];

// Flatten questions array
$all_questions = [];
foreach ($a1_questions_map as $group => $questions) {
    foreach ($questions as $q) {
        $all_questions[] = ['group' => $group, 'question' => $q];
    }
}

// Function to parse symptoms
function parseSymptoms($a1_json, $all_questions) {
    $symptoms = json_decode($a1_json, true);
    $present_motor = [];
    $present_vocal = [];
    $past_motor = [];
    $past_vocal = [];
    
    if ($symptoms) {
        foreach ($symptoms as $idx => $sym) {
            if (isset($sym['answer']) && isset($all_questions[$idx])) {
                $q_info = $all_questions[$idx];
                $is_vocal = (strpos($q_info['group'], 'Vocal') !== false);
                
                if ($sym['answer'] === 'Present') {
                    $entry = [
                        'group' => $q_info['group'],
                        'question' => $q_info['question'],
                        'onset' => $sym['onset'] ?? ''
                    ];
                    if ($is_vocal) {
                        $present_vocal[] = $entry;
                    } else {
                        $present_motor[] = $entry;
                    }
                } elseif ($sym['answer'] === 'Past') {
                    $entry = [
                        'group' => $q_info['group'],
                        'question' => $q_info['question']
                    ];
                    if ($is_vocal) {
                        $past_vocal[] = $entry;
                    } else {
                        $past_motor[] = $entry;
                    }
                }
            }
        }
    }
    
    return [
        'present_motor' => $present_motor,
        'present_vocal' => $present_vocal,
        'past_motor' => $past_motor,
        'past_vocal' => $past_vocal
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YGTSS Results - Professional View</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../CSS/YGTSS_form.css?v=3">
    <link rel="stylesheet" href="../../CSS/YGTSS_form_professional.css?v=4">
</head>
<body>
<?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
<?php include_once __DIR__ . '/../../components/header_component.php'; ?>

<div class="main-content">
<div class="container-fluid mt-4 mb-5">
    <div class="row">
        <!-- Sidebar: Patient List -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Patients</h5>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($patients)): ?>
                        <p class="text-muted text-center py-3">No linked patients.</p>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): 
                            $age = '';
                            if (!empty($patient['Birthday'])) {
                                $birthDate = new DateTime($patient['Birthday']);
                                $today = new DateTime();
                                $age = $birthDate->diff($today)->y . ' years old';
                            }
                        ?>
                            <a href="?patient_id=<?= $patient['User_ID'] ?>" class="text-decoration-none">
                                <div class="patient-card card mb-2 <?= $selected_patient_id == $patient['User_ID'] ? 'selected' : '' ?>">
                                    <div class="card-body p-2 d-flex align-items-center">
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-size: 14px;">
                                            <?= strtoupper(substr($patient['First_Name'], 0, 1) . substr($patient['Last_Name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($patient['First_Name'] . ' ' . $patient['Last_Name']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= $age ?: 'Age not available' ?></small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <?php if (!$selected_patient_id): ?>
                <!-- No patient selected -->
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <div style="font-size: 4em; color: #ccc;">👤</div>
                        <h5 class="text-muted mt-3">Select a Patient</h5>
                        <p class="text-muted">Choose a patient from the list on the left to view their YGTSS results.</p>
                    </div>
                </div>
            <?php elseif (empty($results)): ?>
                <!-- Patient selected but no results -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">YGTSS - <?= htmlspecialchars($patientName) ?></h4>
                    </div>
                    <div class="card-body text-center py-5">
                        <div style="font-size: 4em; color: #ccc;">📋</div>
                        <h5 class="text-muted mt-3">No YGTSS Submissions</h5>
                        <p class="text-muted">This patient has not completed any YGTSS assessments yet.</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Display ALL results for this patient with FULL details -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">YGTSS - <?= htmlspecialchars($patientName) ?></h4>
                        <small class="text-muted"><?= count($results) ?> submission(s) found</small>
                    </div>
                </div>
                
                <?php foreach ($results as $index => $result): ?>
                    <?php $parsed = parseSymptoms($result['A1_Symptoms'] ?? '', $all_questions); ?>
                    
                    <div class="card mb-4" style="border: 2px solid #005949; border-radius: 12px;">
                        <!-- Submission Header -->
                        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #005949 0%, #008060 100%); color: white; border-radius: 10px 10px 0 0;">
                            <div>
                                <strong>Submission #<?= count($results) - $index ?></strong>
                                <span class="ms-3"><?= date('M d, Y \a\t H:i', strtotime($result['Submission_Date'])) ?></span>
                            </div>
                            <span class="badge bg-light text-dark">Severity: <?= $result['Global_Severity'] ?>/100</span>
                        </div>
                        
                        <div class="card-body p-4">
                            <!-- Summary Score Boxes -->
                            <div class="info-grid mb-4">
                                <div class="info-box">
                                    <div class="value"><?= $result['Motor_Tic_Total'] ?? ($result['Number_Motor'] + $result['Frequency_Motor'] + $result['Intensity_Motor'] + $result['Complexity_Motor'] + $result['Interference_Motor']) ?></div>
                                    <div class="label">Motor Tic Score</div>
                                </div>
                                <div class="info-box purple">
                                    <div class="value"><?= $result['Vocal_Tic_Total'] ?? ($result['Number_Vocal'] + $result['Frequency_Vocal'] + $result['Intensity_Vocal'] + $result['Complexity_Vocal'] + $result['Interference_Vocal']) ?></div>
                                    <div class="label">Vocal Tic Score</div>
                                </div>
                                <div class="info-box orange">
                                    <div class="value"><?= $result['Total_Tic_Score'] ?></div>
                                    <div class="label">Total Tic Score</div>
                                </div>
                                <div class="info-box" style="background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);">
                                    <div class="value"><?= $result['Overall_Impairment'] ?></div>
                                    <div class="label">Overall Impairment</div>
                                </div>
                                <div class="info-box" style="background: linear-gradient(135deg, #000000 0%, #434343 100%);">
                                    <div class="value"><?= $result['Global_Severity'] ?></div>
                                    <div class="label">Global Severity</div>
                                </div>
                            </div>

                            <!-- YGTSS Results Table (PDF Format) -->
                            <h5 class="text-primary text-center mb-3" style="font-weight: bold;">YALE GLOBAL TIC SEVERITY SCALE (YGTSS) RESULTS</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered results-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 22%"></th>
                                            <th>NUMBER</th>
                                            <th>FREQUENCY</th>
                                            <th>INTENSITY</th>
                                            <th>COMPLEXITY</th>
                                            <th>INTERFERENCE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="row-label">A. MOTOR TICS</td>
                                            <td class="score-cell"><?= $result['Number_Motor'] ?></td>
                                            <td class="score-cell"><?= $result['Frequency_Motor'] ?></td>
                                            <td class="score-cell"><?= $result['Intensity_Motor'] ?></td>
                                            <td class="score-cell"><?= $result['Complexity_Motor'] ?></td>
                                            <td class="score-cell"><?= $result['Interference_Motor'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="row-label">B. VOCAL TICS</td>
                                            <td class="score-cell"><?= $result['Number_Vocal'] ?></td>
                                            <td class="score-cell"><?= $result['Frequency_Vocal'] ?></td>
                                            <td class="score-cell"><?= $result['Intensity_Vocal'] ?></td>
                                            <td class="score-cell"><?= $result['Complexity_Vocal'] ?></td>
                                            <td class="score-cell"><?= $result['Interference_Vocal'] ?></td>
                                        </tr>
                                        <tr class="total-row">
                                            <td class="row-label">C. TOTAL FOR ALL TICS</td>
                                            <td class="score-cell"><?= $result['Number_Motor'] + $result['Number_Vocal'] ?></td>
                                            <td class="score-cell"><?= $result['Frequency_Motor'] + $result['Frequency_Vocal'] ?></td>
                                            <td class="score-cell"><?= $result['Intensity_Motor'] + $result['Intensity_Vocal'] ?></td>
                                            <td class="score-cell"><?= $result['Complexity_Motor'] + $result['Complexity_Vocal'] ?></td>
                                            <td class="score-cell"><?= $result['Interference_Motor'] + $result['Interference_Vocal'] ?></td>
                                        </tr>
                                        <tr class="grand-total-row">
                                            <td class="text-end"><strong>TOTAL</strong></td>
                                            <td colspan="5" class="score-cell text-center" style="font-size: 1.3em;"><?= $result['Total_Tic_Score'] ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary Section -->
                            <div class="mt-4 p-4" style="background: #f8f9fa; border-radius: 8px;">
                                <p style="font-size: 1.1em;" class="mb-2">
                                    <strong>Total Tics</strong> (NUMBER + FREQUENCY + INTENSITY + COMPLEXITY + INTERFERENCE) = 
                                    <span class="badge bg-primary" style="font-size: 1.2em; padding: 8px 16px;"><?= $result['Total_Tic_Score'] ?></span>
                                </p>
                                <p style="font-size: 1.1em;" class="mb-2">
                                    <strong>OVERALL IMPAIRMENT INDEX</strong> = 
                                    <span class="badge bg-warning text-dark" style="font-size: 1.2em; padding: 8px 16px;"><?= $result['Overall_Impairment'] ?></span>
                                </p>
                                <p style="font-size: 1.1em;" class="mb-0">
                                    <strong>GLOBAL SEVERITY</strong> (TOTAL TICS + Overall Impairment) = 
                                    <span class="badge bg-danger" style="font-size: 1.2em; padding: 8px 16px;"><?= $result['Global_Severity'] ?></span>
                                </p>
                            </div>

                            <!-- Additional Information -->
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <strong>Additional Information</strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <p><strong>Age of first tic:</strong><br><?= $result['First_Tic_Age'] ?? 'N/A' ?> years</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p><strong>Age when it started to bother:</strong><br><?= $result['Bother_Age'] ?? 'N/A' ?> years</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p><strong>Age when sought treatment:</strong><br><?= $result['Treatment_Age'] ?? 'N/A' ?> years</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Symptom Checklist Details -->
                            <?php if (!empty($result['A1_Symptoms'])): ?>
                            <h5 class="mt-4 mb-3">Symptom Checklist (A.1) - TIC SYMPTOM INVENTORY</h5>
                            
                            <!-- Multiple Tics Questions -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong>Additional Information</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Has multiple distinct or sequential tics at the same time?</strong><br>
                                            <span class="badge <?= $result['A1_Simultaneous_Tics'] === 'Yes' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                <?= $result['A1_Simultaneous_Tics'] === 'Yes' ? 'Yes' : 'No' ?>
                                            </span>
                                            <?php if (!empty($result['A1_Simultaneous_Desc'])): ?>
                                                <br><em class="text-muted small mt-1"><?= htmlspecialchars($result['A1_Simultaneous_Desc']) ?></em>
                                            <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Has more than one tic group at the same time?</strong><br>
                                            <span class="badge <?= $result['A1_Multiple_Groups'] === 'Yes' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                <?= $result['A1_Multiple_Groups'] === 'Yes' ? 'Yes' : 'No' ?>
                                            </span>
                                            <?php if (!empty($result['A1_Multiple_Groups_Desc'])): ?>
                                                <br><em class="text-muted small mt-1"><?= htmlspecialchars($result['A1_Multiple_Groups_Desc']) ?></em>
                                            <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Present Symptoms -->
                            <?php if (!empty($parsed['present_motor']) || !empty($parsed['present_vocal'])): ?>
                            <div class="card mb-3 border-danger">
                                <div class="card-header bg-danger text-white">
                                    <strong>🔴 PRESENT Symptoms (Current Week)</strong>
                                    <span class="badge bg-light text-dark ms-2"><?= count($parsed['present_motor']) + count($parsed['present_vocal']) ?> total</span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (!empty($parsed['present_motor'])): ?>
                                    <div class="p-2 bg-light border-bottom">
                                        <strong>Motor Tics (<?= count($parsed['present_motor']) ?>)</strong>
                                    </div>
                                    <div class="symptom-list">
                                        <?php 
                                        $current_group = '';
                                        foreach ($parsed['present_motor'] as $s): 
                                            if ($s['group'] !== $current_group):
                                                $current_group = $s['group'];
                                        ?>
                                            <div class="p-2 bg-light border-bottom" style="font-size: 0.85em;">
                                                <em><?= htmlspecialchars($current_group) ?></em>
                                            </div>
                                        <?php endif; ?>
                                            <div class="symptom-item symptom-present d-flex justify-content-between align-items-center">
                                                <span><?= htmlspecialchars($s['question']) ?></span>
                                                <?php if (!empty($s['onset'])): ?>
                                                    <span class="badge bg-danger">Onset: <?= htmlspecialchars($s['onset']) ?> years</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($parsed['present_vocal'])): ?>
                                    <div class="p-2 bg-light border-bottom border-top">
                                        <strong>Vocal/Phonic Tics (<?= count($parsed['present_vocal']) ?>)</strong>
                                    </div>
                                    <div class="symptom-list">
                                        <?php 
                                        $current_group = '';
                                        foreach ($parsed['present_vocal'] as $s): 
                                            if ($s['group'] !== $current_group):
                                                $current_group = $s['group'];
                                        ?>
                                            <div class="p-2 bg-light border-bottom" style="font-size: 0.85em;">
                                                <em><?= htmlspecialchars($current_group) ?></em>
                                            </div>
                                        <?php endif; ?>
                                            <div class="symptom-item symptom-present d-flex justify-content-between align-items-center">
                                                <span><?= htmlspecialchars($s['question']) ?></span>
                                                <?php if (!empty($s['onset'])): ?>
                                                    <span class="badge bg-danger">Onset: <?= htmlspecialchars($s['onset']) ?> years</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Past Symptoms -->
                            <?php if (!empty($parsed['past_motor']) || !empty($parsed['past_vocal'])): ?>
                            <div class="card mb-3 border-info">
                                <div class="card-header bg-info text-white">
                                    <strong>🟡 PAST Symptoms (No longer present)</strong>
                                    <span class="badge bg-light text-dark ms-2"><?= count($parsed['past_motor']) + count($parsed['past_vocal']) ?> total</span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (!empty($parsed['past_motor'])): ?>
                                    <div class="p-2 bg-light border-bottom">
                                        <strong>Motor Tics (<?= count($parsed['past_motor']) ?>)</strong>
                                    </div>
                                    <div class="symptom-list">
                                        <?php 
                                        $current_group = '';
                                        foreach ($parsed['past_motor'] as $s): 
                                            if ($s['group'] !== $current_group):
                                                $current_group = $s['group'];
                                        ?>
                                            <div class="p-2 bg-light border-bottom" style="font-size: 0.85em;">
                                                <em><?= htmlspecialchars($current_group) ?></em>
                                            </div>
                                        <?php endif; ?>
                                            <div class="symptom-item symptom-past">
                                                <?= htmlspecialchars($s['question']) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($parsed['past_vocal'])): ?>
                                    <div class="p-2 bg-light border-bottom border-top">
                                        <strong>Vocal/Phonic Tics (<?= count($parsed['past_vocal']) ?>)</strong>
                                    </div>
                                    <div class="symptom-list">
                                        <?php 
                                        $current_group = '';
                                        foreach ($parsed['past_vocal'] as $s): 
                                            if ($s['group'] !== $current_group):
                                                $current_group = $s['group'];
                                        ?>
                                            <div class="p-2 bg-light border-bottom" style="font-size: 0.85em;">
                                                <em><?= htmlspecialchars($current_group) ?></em>
                                            </div>
                                        <?php endif; ?>
                                            <div class="symptom-item symptom-past">
                                                <?= htmlspecialchars($s['question']) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (empty($parsed['present_motor']) && empty($parsed['present_vocal']) && empty($parsed['past_motor']) && empty($parsed['past_vocal'])): ?>
                            <div class="alert alert-secondary">
                                No symptoms were reported as Present or Past.
                            </div>
                            <?php endif; ?>
                            
                            <?php endif; ?>

                            <!-- Severity Interpretation -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <strong>Score Interpretation</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6>Individual Dimension Scores (0-5)</h6>
                                            <ul class="list-unstyled">
                                                <li><span class="badge bg-success">0</span> None</li>
                                                <li><span class="badge bg-info">1</span> Minimal</li>
                                                <li><span class="badge bg-primary">2</span> Mild</li>
                                                <li><span class="badge bg-warning text-dark">3</span> Moderate</li>
                                                <li><span class="badge bg-orange">4</span> Marked</li>
                                                <li><span class="badge bg-danger">5</span> Severe</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Overall Impairment (0-50)</h6>
                                            <ul class="list-unstyled">
                                                <li><span class="badge bg-success">0</span> None</li>
                                                <li><span class="badge bg-info">10</span> Minimal</li>
                                                <li><span class="badge bg-primary">20</span> Mild</li>
                                                <li><span class="badge bg-warning text-dark">30</span> Moderate</li>
                                                <li><span class="badge bg-orange">40</span> Marked</li>
                                                <li><span class="badge bg-danger">50</span> Severe</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Global Severity Range</h6>
                                            <p class="small text-muted">
                                                Total Tic Score: 0-50 (sum of all motor and vocal dimensions)<br>
                                                Global Severity: 0-100 (Total Tic Score + Overall Impairment)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
