<?php
// Start session FIRST before any output or includes
if (session_status() === PHP_SESSION_NONE) { 
	session_start(); 
}

// Questions for A1: Symptom Checklist (full, grouped, detailed, with age of onset)
$a1_questions = [
	'Eye Movements' => [
		'Simple – Do you experience involuntary blinking, strabismus, quick eye turning, eye rolling, or brief eye widening?',
		'Complex – Do you experience involuntary expressions of surprise, mocking, odd looks, or looking to the side as if hearing a sound?',
	],
	'Nose, Mouth, Tongue, or Facial Movements (grimaces)' => [
		'Simple – Do you experience involuntary nose twitching, tongue biting, lip chewing/licking, pouting, showing or grinding your teeth?',
		'Complex – Do you experience involuntary nostril flaring, smiling, funny facial expressions (grimaces), or tongue protrusion?',
	],
	'Head Movements' => [
		'Simple – Do you involuntarily touch your shoulders with your chin or lift your chin?',
		'Simple – Do you involuntarily throw your head backward?',
	],
	'Shoulder Movements' => [
		'Simple – Do you involuntarily shrug one shoulder?',
		'Simple – Do you involuntarily shrug both shoulders as if saying "I don\'t know"?',
	],
	'Arm and Hand Movements' => [
		'Simple – Do you involuntarily flex or extend your arms quickly, bite your nails, tap with your fingers, or crack your knuckles?',
		'Complex – Do you involuntarily run your hand through your hair, touch objects or people, pinch, count with your fingers, or have "writing tics" (e.g., rewriting letters/words repeatedly)?',
	],
	'Foot, Leg, and Toe Movements' => [
		'Simple – Do you involuntarily kick, hop, bend your knees, flex/extend your ankles, shake your legs, or stomp while walking?',
		'Complex – Do you involuntarily take one step forward and two back, deeply bend your knee, or squat?',
	],
	'Abdominal, Trunk, and Pelvic Movements' => [
		'Simple – Do you involuntarily tense your abdomen or buttocks?',
	],
	'Other Simple Tics' => [
		'Simple – Describe any other simple motor tics.',
	],
	'Other Complex Tics' => [
		'Complex – Do you have tics related to compulsive behaviors (touching, hitting, arranging, selecting, pairing, balancing)?',
		'Complex – Do you have stimulus-dependent tics?',
		'Complex – Do you make obscene or rude gestures (e.g., middle finger)?',
		'Complex – Do you have unusual postures?',
		'Complex – Do you bend or twist your body?',
		'Complex – Do you turn or spin (pirouettes)?',
		'Complex – Do you have sudden, impulsive behaviors? (Describe)',
		'Complex – Do you have behaviors that may injure others?',
		'Complex – Do you have self-injurious behaviors?',
		'Complex – Describe any other type of motor tic.',
	],
	'Simple Vocal Tics' => [
		'Simple – Coughing.',
		'Simple – Throat clearing.',
		'Simple – Sniffing.',
		'Simple – Whistling.',
		'Simple – Making animal or bird sounds.',
	],
	'Complex Vocal Tics' => [
		'Complex – List other simple vocal tics.',
		'Complex – List syllable sounds you involuntarily produce.',
		'Complex – Do you involuntarily say obscene or rude words or phrases?',
		'Complex – Do you involuntarily say words (not obscene)?',
		'Complex – Do you have echolalia (repeating what someone else says)?',
		'Complex – Do you have palilalia (repeating what you yourself say)?',
		'Complex – Describe any other speech problems.',
		'Complex – Describe any pattern or sequence of vocal tic behavior.',
	],
];

$a2_questions = [
	'1. NUMBER OF TICS' => [
		[
			'label' => '1.1 Number of Motor Tics',
			'name' => 'number_motor',
			'options' => [
				'0 – None',
				'1 – Single tic',
				'2 – Multiple distinct tics (2–5)',
				'3 – Multiple distinct tics (>5)',
				'4 – Multiple distinct tics with at least one orchestrated pattern of simultaneous or sequential tics, difficult to distinguish',
				'5 – Multiple distinct tics with several (>2) orchestrated paroxysms of simultaneous or sequential tics, difficult to distinguish',
			],
		],
		[
			'label' => '1.2 Number of Vocal/Phonic Tics',
			'name' => 'number_vocal',
			'options' => [
				'0 – None',
				'1 – Single tic',
				'2 – Multiple distinct tics (2–5)',
				'3 – Multiple distinct tics (>5)',
				'4 – Multiple distinct tics with at least one orchestrated pattern of simultaneous or sequential tics, difficult to distinguish',
				'5 – Multiple distinct tics with several (>2) orchestrated paroxysms of simultaneous or sequential tics, difficult to distinguish',
			],
		],
	],
	'2. FREQUENCY OF TICS' => [
		[
			'label' => '2.1 Motor Tic Frequency',
			'name' => 'freq_motor',
			'question' => 'During the last week, what was the longest period you were free of MOTOR tics? (Do not count sleep.)',
			'options' => [
				'0 – Always tic-free; no evidence of motor tics',
				'1 – Rarely; tics occur sporadically, not every day; tic-free periods last several days',
				'2 – Occasionally; tics are present almost daily; brief attacks occur but last only minutes; tic-free most of the day',
				'3 – Frequently; tics present daily; tic-free periods up to 3 hours',
				'4 – Almost always; tics present most hours of the day; tic-free intervals infrequent and last about half an hour',
				'5 – Always; tics present all the time; tic-free intervals rarely exceed 5–10 minutes',
			],
		],
		[
			'label' => '2.2 Vocal/Phonic Tic Frequency',
			'name' => 'freq_vocal',
			'question' => 'During the last week, what was the longest period you were free of VOCAL tics? (Do not count sleep.)',
			'options' => [
				'0 – Always tic-free; no evidence of vocal tics',
				'1 – Rarely; vocal tics occur sporadically; intervals last several days',
				'2 – Occasionally; almost daily; brief attacks last only minutes; tic-free most of the day',
				'3 – Frequently; present daily; tic-free periods up to 3 hours',
				'4 – Almost always; present most hours of the day; intervals are infrequent and last about half an hour',
				'5 – Always; present all the time; intervals rarely exceed 5–10 minutes',
			],
		],
	],
	'3. INTENSITY OF TICS' => [
		[
			'label' => '3.1 Motor Tic Intensity',
			'name' => 'intensity_motor',
			'question' => 'During the last week, how intense were your MOTOR tics?',
			'options' => [
				'0 – Absent',
				'1 – Minimal (barely noticeable)',
				'2 – Mild',
				'3 – Moderate',
				'4 – Marked',
				'5 – Severe (extreme force, risk of injury)',
			],
		],
		[
			'label' => '3.2 Vocal Tic Intensity',
			'name' => 'intensity_vocal',
			'question' => 'During the last week, how intense were your VOCAL tics?',
			'options' => [
				'0 – Absent',
				'1 – Minimal (barely audible)',
				'2 – Mild',
				'3 – Moderate',
				'4 – Marked',
				'5 – Severe (very loud/exaggerated)',
			],
		],
	],
	'4. COMPLEXITY' => [
		[
			'label' => '4.1 Motor Tic Complexity',
			'name' => 'complexity_motor',
			'question' => 'Rate the complexity of your MOTOR tics.',
			'options' => [
				'0 – None (all simple)',
				'1 – Borderline',
				'2 – Mild',
				'3 – Moderate',
				'4 – Marked',
				'5 – Severe',
			],
		],
		[
			'label' => '4.2 Vocal Tic Complexity',
			'name' => 'complexity_vocal',
			'question' => 'Rate the complexity of your VOCAL tics.',
			'options' => [
				'0 – None (all simple)',
				'1 – Borderline',
				'2 – Mild',
				'3 – Moderate',
				'4 – Marked',
				'5 – Severe',
			],
		],
	],
	'5. INTERFERENCE' => [
		[
			'label' => '5.1 Motor Tic Interference',
			'name' => 'interference_motor',
			'question' => 'During the last week, did MOTOR tics interfere with what you were doing?',
			'options' => [
				'0 – None',
				'1 – Minimal',
				'2 – Mild',
				'3 – Moderate',
				'4 – Marked',
				'5 – Severe',
			],
		],
		[
			'label' => '5.2 Vocal Tic Interference',
			'name' => 'interference_vocal',
			'question' => 'During the last week, did VOCAL tics interfere with what you were doing?',
			'options' => [
				'0 – None',
				'1 – Minimal',
				'2 – Mild',
				'3 – Moderate',
				'4 – Marked',
				'5 – Severe',
			],
		],
	],
	'6. OVERALL IMPAIRMENT' => [
		[
			'label' => '6. Overall Impairment',
			'name' => 'overall_impairment',
			'question' => 'What is the overall impairment caused by your tics?',
			'options' => [
				'0 – None',
				'10 – Minimal',
				'20 – Mild',
				'30 – Moderate',
				'40 – Marked',
				'50 – Severe',
			],
		],
	],
];

$step = isset($_POST['step']) ? intval($_POST['step']) : 1;
$error_msg = '';

// Database connection and current user
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

// Store answers between steps
if (!isset($_SESSION['ygtss_answers'])) {
	$_SESSION['ygtss_answers'] = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($step === 1) {
		// Validate all A1 radio questions answered
		$a1_answers = $_POST['A1'] ?? [];
		$total_a1 = 0;
		foreach ($a1_questions as $group => $questions) {
			$total_a1 += count($questions);
		}
		$all_a1_answered = true;
		$missing_onset = false;
		for ($i = 0; $i < $total_a1; $i++) {
			if (!isset($a1_answers[$i]['answer']) || $a1_answers[$i]['answer'] === '') {
				$all_a1_answered = false;
				break;
			}
			// Check onset is filled only when answer is "Present"
			if ($a1_answers[$i]['answer'] === 'Present' && (!isset($a1_answers[$i]['onset']) || trim($a1_answers[$i]['onset']) === '')) {
				$missing_onset = true;
			}
		}
		
		// Validate last two questions
		$sim_tics = $_POST['A1_simultaneous_tics'] ?? '';
		$mult_groups = $_POST['A1_multiple_groups'] ?? '';
		$sim_tics_desc = trim($_POST['A1_simultaneous_tics_desc'] ?? '');
		$mult_groups_desc = trim($_POST['A1_multiple_groups_desc'] ?? '');
		$last_questions_answered = ($sim_tics !== '' && $mult_groups !== '');
		
		// Check if descriptions are provided when answer is "Yes"
		$missing_sim_desc = ($sim_tics === 'Yes' && $sim_tics_desc === '');
		$missing_mult_desc = ($mult_groups === 'Yes' && $mult_groups_desc === '');
		
		if ($all_a1_answered && !$missing_onset && $last_questions_answered && !$missing_sim_desc && !$missing_mult_desc) {
			$_SESSION['ygtss_answers']['A1'] = $a1_answers;
			$_SESSION['ygtss_answers']['A1_simultaneous_tics'] = $sim_tics;
			$_SESSION['ygtss_answers']['A1_simultaneous_tics_desc'] = $sim_tics_desc;
			$_SESSION['ygtss_answers']['A1_multiple_groups'] = $mult_groups;
			$_SESSION['ygtss_answers']['A1_multiple_groups_desc'] = $mult_groups_desc;
			$step = 2;
		} else {
			// Save partial A1 answers so fields are pre-filled on return
			$_SESSION['ygtss_answers']['A1'] = $a1_answers;
			$_SESSION['ygtss_answers']['A1_simultaneous_tics'] = $sim_tics;
			$_SESSION['ygtss_answers']['A1_simultaneous_tics_desc'] = $sim_tics_desc;
			$_SESSION['ygtss_answers']['A1_multiple_groups'] = $mult_groups;
			$_SESSION['ygtss_answers']['A1_multiple_groups_desc'] = $mult_groups_desc;
			
			// Build error message
			if (!$all_a1_answered) {
				$error_msg = 'Please answer all symptom checklist questions before proceeding.';
			} elseif ($missing_onset) {
				$error_msg = 'Please provide the age of onset for all symptoms marked as "Present".';
			} elseif (!$last_questions_answered) {
				$error_msg = 'Please answer both questions about simultaneous tics and multiple tic groups.';
			} elseif ($missing_sim_desc) {
				$error_msg = 'Please describe your simultaneous/sequential tics since you answered "Yes".';
			} elseif ($missing_mult_desc) {
				$error_msg = 'Please describe your multiple tic groups since you answered "Yes".';
			}
			$step = 1;
		}
	} elseif ($step === 2) {
		// Save any answers provided
		$a2_answers = $_POST['A2'] ?? [];
		$_SESSION['ygtss_answers']['A2'] = $a2_answers;
		
		// Save final information
		$_SESSION['ygtss_answers']['final_first_tic_age'] = $_POST['final_first_tic_age'] ?? '';
		$_SESSION['ygtss_answers']['final_bother_age'] = $_POST['final_bother_age'] ?? '';
		$_SESSION['ygtss_answers']['final_treatment_age'] = $_POST['final_treatment_age'] ?? '';
		
		// If going back, allow without validation
		if (isset($_POST['back'])) {
			$step = 1;
		} else {
			// Validate all A2 radio questions answered only when submitting
			$a2_names = [];
			foreach ($a2_questions as $section => $questions) {
				foreach ($questions as $q) {
					$a2_names[] = $q['name'];
				}
			}
			$all_a2_answered = true;
			foreach ($a2_names as $name) {
				if (!isset($a2_answers[$name]) || $a2_answers[$name] === '') {
					$all_a2_answered = false;
					break;
				}
			}
			// Validate final information fields
			$final_first_tic = trim($_POST['final_first_tic_age'] ?? '');
			$final_bother = trim($_POST['final_bother_age'] ?? '');
			$final_treatment = trim($_POST['final_treatment_age'] ?? '');
			$final_info_complete = ($final_first_tic !== '' && $final_bother !== '' && $final_treatment !== '');
			
			// Validate age sequence: first tic <= bother <= treatment
			$age_sequence_valid = true;
			if ($final_info_complete) {
				$age1 = intval($final_first_tic);
				$age2 = intval($final_bother);
				$age3 = intval($final_treatment);
				if ($age1 > $age2 || $age2 > $age3) {
					$age_sequence_valid = false;
				}
			}
			
			if ($all_a2_answered && $final_info_complete && $age_sequence_valid) {
				// Save results to database
				$patient_id = $_SESSION['user_id'];
				
				// Extract numeric scores from A2 answers (first character is the score 0-5)
				$number_motor = isset($a2_answers['number_motor']) ? (int)substr($a2_answers['number_motor'], 0, 1) : 0;
				$number_vocal = isset($a2_answers['number_vocal']) ? (int)substr($a2_answers['number_vocal'], 0, 1) : 0;
				$freq_motor = isset($a2_answers['freq_motor']) ? (int)substr($a2_answers['freq_motor'], 0, 1) : 0;
				$freq_vocal = isset($a2_answers['freq_vocal']) ? (int)substr($a2_answers['freq_vocal'], 0, 1) : 0;
				$intensity_motor = isset($a2_answers['intensity_motor']) ? (int)substr($a2_answers['intensity_motor'], 0, 1) : 0;
				$intensity_vocal = isset($a2_answers['intensity_vocal']) ? (int)substr($a2_answers['intensity_vocal'], 0, 1) : 0;
				$complexity_motor = isset($a2_answers['complexity_motor']) ? (int)substr($a2_answers['complexity_motor'], 0, 1) : 0;
				$complexity_vocal = isset($a2_answers['complexity_vocal']) ? (int)substr($a2_answers['complexity_vocal'], 0, 1) : 0;
				$interference_motor = isset($a2_answers['interference_motor']) ? (int)substr($a2_answers['interference_motor'], 0, 1) : 0;
				$interference_vocal = isset($a2_answers['interference_vocal']) ? (int)substr($a2_answers['interference_vocal'], 0, 1) : 0;
				$overall_impairment = isset($a2_answers['overall_impairment']) ? (int)substr($a2_answers['overall_impairment'], 0, 2) : 0;
				
				// Calculate totals
				$motor_tic_total = $number_motor + $freq_motor + $intensity_motor + $complexity_motor + $interference_motor;
				$vocal_tic_total = $number_vocal + $freq_vocal + $intensity_vocal + $complexity_vocal + $interference_vocal;
				$total_tic_score = $motor_tic_total + $vocal_tic_total;
				$global_severity = $total_tic_score + $overall_impairment;
				
				// Prepare A1 symptoms data as JSON
				$a1_symptoms = json_encode($_SESSION['ygtss_answers']['A1'] ?? []);
				$a1_simultaneous = $_SESSION['ygtss_answers']['A1_simultaneous_tics'] ?? null;
				$a1_simultaneous_desc = $_SESSION['ygtss_answers']['A1_simultaneous_tics_desc'] ?? null;
				$a1_multiple = $_SESSION['ygtss_answers']['A1_multiple_groups'] ?? null;
				$a1_multiple_desc = $_SESSION['ygtss_answers']['A1_multiple_groups_desc'] ?? null;
				
				// Create YGTSS results table if not exists
				$createTableSql = "
				CREATE TABLE IF NOT EXISTS `ygtss_results` (
					`YGTSS_ID` INT AUTO_INCREMENT PRIMARY KEY,
					`Patient_ID` INT NOT NULL,
					`Submission_Date` DATETIME DEFAULT CURRENT_TIMESTAMP,
					`Number_Motor` INT DEFAULT 0,
					`Number_Vocal` INT DEFAULT 0,
					`Frequency_Motor` INT DEFAULT 0,
					`Frequency_Vocal` INT DEFAULT 0,
					`Intensity_Motor` INT DEFAULT 0,
					`Intensity_Vocal` INT DEFAULT 0,
					`Complexity_Motor` INT DEFAULT 0,
					`Complexity_Vocal` INT DEFAULT 0,
					`Interference_Motor` INT DEFAULT 0,
					`Interference_Vocal` INT DEFAULT 0,
					`Overall_Impairment` INT DEFAULT 0,
					`Motor_Tic_Total` INT DEFAULT 0,
					`Vocal_Tic_Total` INT DEFAULT 0,
					`Total_Tic_Score` INT DEFAULT 0,
					`Global_Severity` INT DEFAULT 0,
					`First_Tic_Age` INT DEFAULT NULL,
					`Bother_Age` INT DEFAULT NULL,
					`Treatment_Age` INT DEFAULT NULL,
					`A1_Symptoms` TEXT DEFAULT NULL,
					`A1_Simultaneous_Tics` ENUM('Yes', 'No') DEFAULT NULL,
					`A1_Simultaneous_Desc` TEXT DEFAULT NULL,
					`A1_Multiple_Groups` ENUM('Yes', 'No') DEFAULT NULL,
					`A1_Multiple_Groups_Desc` TEXT DEFAULT NULL,
					INDEX idx_patient (Patient_ID),
					INDEX idx_date (Submission_Date),
					FOREIGN KEY (Patient_ID) REFERENCES user_profile(User_ID) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
				";
				mysqli_query($conn, $createTableSql);
				
				// Insert the results
				$insertStmt = mysqli_prepare($conn, "
					INSERT INTO ygtss_results (
						Patient_ID, Number_Motor, Number_Vocal, Frequency_Motor, Frequency_Vocal,
						Intensity_Motor, Intensity_Vocal, Complexity_Motor, Complexity_Vocal,
						Interference_Motor, Interference_Vocal, Overall_Impairment,
						Motor_Tic_Total, Vocal_Tic_Total, Total_Tic_Score, Global_Severity,
						First_Tic_Age, Bother_Age, Treatment_Age,
						A1_Symptoms, A1_Simultaneous_Tics, A1_Simultaneous_Desc,
						A1_Multiple_Groups, A1_Multiple_Groups_Desc
					) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				");
				
				if ($insertStmt) {
					mysqli_stmt_bind_param($insertStmt, 'iiiiiiiiiiiiiiiiiiiissss',
						$patient_id,
						$number_motor, $number_vocal,
						$freq_motor, $freq_vocal,
						$intensity_motor, $intensity_vocal,
						$complexity_motor, $complexity_vocal,
						$interference_motor, $interference_vocal,
						$overall_impairment,
						$motor_tic_total, $vocal_tic_total, $total_tic_score, $global_severity,
						$final_first_tic, $final_bother, $final_treatment,
						$a1_symptoms, $a1_simultaneous, $a1_simultaneous_desc,
						$a1_multiple, $a1_multiple_desc
					);
					mysqli_stmt_execute($insertStmt);
					mysqli_stmt_close($insertStmt);
				}
				
				// Clear the session answers after successful submission
				unset($_SESSION['ygtss_answers']);
				
				// Redirect to dashboard with success message
				$_SESSION['ygtss_success'] = 'Your YGTSS assessment has been successfully submitted.';
				header("Location: home_patient.php");
				exit;
			} else {
				if (!$all_a2_answered) {
					$error_msg = 'Please answer all tic severity questions before submitting.';
				} elseif (!$final_info_complete) {
					$error_msg = 'Please fill in all the Final Information fields.';
				} elseif (!$age_sequence_valid) {
					$error_msg = 'The ages must be in sequential order: the age when your first tic appeared must be ≤ the age when tics began to bother you, which must be ≤ the age when you sought treatment.';
				}
				$step = 2;
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>YGTSS Form</title>
	<!-- Tailwind (for utility classes) -->
	<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Custom CSS for YGTSS form -->
	<link rel="stylesheet" href="../../CSS/YGTSS_form.css">
</head>
<body>
<?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
<?php include_once __DIR__ . '/../../components/header_component.php'; ?>
<div class="container mt-5 mb-5 ygtss-section">
	<h2 class="mb-4">Yale Global Tic Severity Scale (YGTSS) Form</h2>
	<!-- Developer credits -->
	<div class="mb-3 text-center">
		<div class="small text-muted">
			<strong>Developed by:</strong> LECKMAN, J.F.; RIDDLE, M.A.; HARDIN, M.T.; ORT, S.I.; SWARTZ, K.L.; STEVENSON, J.; COHEN, D.J.<br>
			DEPARTMENT OF PSYCHIATRY<br>
			YALE UNIVERSITY SCHOOL OF MEDICINE
		</div>
	</div>
	<?php if ($step === 1): ?>
		<?php if (!empty($error_msg)): ?>
			<div class="alert alert-danger mb-3"><?= htmlspecialchars($error_msg) ?></div>
		<?php endif; ?>
		<form method="post">
			<h4 class="mb-3">Part A1: Symptom Checklist</h4>
			<?php $qnum = 1; ?>
			<?php $a1_prev = $_SESSION['ygtss_answers']['A1'] ?? []; ?>
			<?php foreach ($a1_questions as $group => $questions): ?>
				<div class="mb-3">
					<strong><?= htmlspecialchars($group) ?></strong>
				</div>
				<?php foreach ($questions as $qtext): ?>
					<div class="mb-2 ms-3">
						<label><strong>Q<?= $qnum ?>.</strong> <?= htmlspecialchars($qtext) ?></label><br>
						<?php $prev = $a1_prev[$qnum-1] ?? []; ?>
						<?php foreach (["Never", "Past", "Present"] as $opt): ?>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="A1[<?= $qnum - 1 ?>][answer]" value="<?= $opt ?>" id="A1_<?= $qnum - 1 ?>_<?= $opt ?>"<?= (isset($prev['answer']) && $prev['answer'] === $opt) ? ' checked' : '' ?> required>
								<label class="form-check-label" for="A1_<?= $qnum - 1 ?>_<?= $opt ?>">
									<?= $opt ?>
								</label>
							</div>
						<?php endforeach; ?>
						<div class="mt-1">
							<label class="form-label small text-muted">Age of onset <span class="text-danger">*</span> (required if Present)</label>
							<input type="text" name="A1[<?= $qnum - 1 ?>][onset]" placeholder="Age of onset" class="form-control form-control-sm" style="max-width:150px;display:inline-block;" value="<?= isset($prev['onset']) ? htmlspecialchars($prev['onset']) : '' ?>" />
						</div>
					</div>
					<?php $qnum++; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>

			<!-- Additional Yes/No Questions -->
			<?php $sim_tics_prev = $_SESSION['ygtss_answers']['A1_simultaneous_tics'] ?? ''; ?>
			<?php $sim_tics_desc_prev = $_SESSION['ygtss_answers']['A1_simultaneous_tics_desc'] ?? ''; ?>
			<div class="mb-3 mt-4">
				<label><strong>During the past week, have you had several distinct tics occurring simultaneously or sequentially at the same time? <span class="text-danger">*</span></strong></label><br>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="A1_simultaneous_tics" value="Yes" id="A1_simultaneous_tics_yes"<?= $sim_tics_prev === 'Yes' ? ' checked' : '' ?> required>
					<label class="form-check-label" for="A1_simultaneous_tics_yes">Yes</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="A1_simultaneous_tics" value="No" id="A1_simultaneous_tics_no"<?= $sim_tics_prev === 'No' ? ' checked' : '' ?> required>
					<label class="form-check-label" for="A1_simultaneous_tics_no">No</label>
				</div>
				<div class="mt-2">
					<label class="form-label small text-muted">Description <span class="text-danger">*</span> (required if Yes)</label>
					<input type="text" name="A1_simultaneous_tics_desc" placeholder="Describe your tics" class="form-control form-control-sm" value="<?= htmlspecialchars($sim_tics_desc_prev) ?>" />
				</div>
			</div>
			<?php $mult_groups_prev = $_SESSION['ygtss_answers']['A1_multiple_groups'] ?? ''; ?>
			<?php $mult_groups_desc_prev = $_SESSION['ygtss_answers']['A1_multiple_groups_desc'] ?? ''; ?>
			<div class="mb-3">
				<label><strong>Do you have more than one group of tics that occur together? <span class="text-danger">*</span></strong></label><br>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="A1_multiple_groups" value="Yes" id="A1_multiple_groups_yes"<?= $mult_groups_prev === 'Yes' ? ' checked' : '' ?> required>
					<label class="form-check-label" for="A1_multiple_groups_yes">Yes</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="A1_multiple_groups" value="No" id="A1_multiple_groups_no"<?= $mult_groups_prev === 'No' ? ' checked' : '' ?> required>
					<label class="form-check-label" for="A1_multiple_groups_no">No</label>
				</div>
				<div class="mt-2">
					<label class="form-label small text-muted">Description <span class="text-danger">*</span> (required if Yes)</label>
					<input type="text" name="A1_multiple_groups_desc" placeholder="Describe your tic groups" class="form-control form-control-sm" value="<?= htmlspecialchars($mult_groups_desc_prev) ?>" />
				</div>
			</div>
			<input type="hidden" name="step" value="1">
			<button type="submit" class="btn btn-primary">Next</button>
		</form>
	<?php elseif ($step === 2): ?>
		<?php if (!empty($error_msg)): ?>
			<div class="alert alert-danger mb-3"><?= htmlspecialchars($error_msg) ?></div>
		<?php endif; ?>
		<form method="post">
			<h4 class="mb-3">Part A2: Tic Severity Scale</h4>
			<?php $a2num = 1; ?>
			<?php $a2_prev = isset($_SESSION['ygtss_answers']['A2']) ? $_SESSION['ygtss_answers']['A2'] : [];?>
			<?php foreach ($a2_questions as $section => $questions): ?>
				<div class="mb-3 mt-4">
					<strong><?= htmlspecialchars($section) ?></strong>
				</div>
				<?php foreach ($questions as $q): ?>
					<div class="mb-3 ms-3">
						<label><strong>Q<?= $a2num ?>.</strong> <?= htmlspecialchars($q['label']) ?></strong></label><br>
						<?php if (!empty($q['question'])): ?>
							<span><?= htmlspecialchars($q['question']) ?></span><br>
						<?php endif; ?>
						<?php foreach ($q['options'] as $opt): ?>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="A2[<?= $q['name'] ?>]" value="<?= htmlspecialchars($opt) ?>" id="A2_<?= $q['name'] ?>_<?= htmlspecialchars($opt) ?>"
								<?php echo (isset($a2_prev[$q['name']]) && trim($a2_prev[$q['name']]) === trim($opt)) ? 'checked' : ''; ?> >
								<label class="form-check-label" for="A2_<?= $q['name'] ?>_<?= htmlspecialchars($opt) ?>">
									<?= htmlspecialchars($opt) ?>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
					<?php $a2num++; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
			
			<!-- Final Information -->
			<div class="mb-3 mt-4">
				<strong>Final Information</strong>
			</div>
			<div class="mb-3 ms-3">
				<label><strong>How old were you when your first tic appeared? <span class="text-danger">*</span></strong></label>
				<input type="number" name="final_first_tic_age" class="form-control form-control-sm mt-1" style="max-width:150px;" value="<?= isset($_SESSION['ygtss_answers']['final_first_tic_age']) ? htmlspecialchars($_SESSION['ygtss_answers']['final_first_tic_age']) : '' ?>" required />
			</div>
			<div class="mb-3 ms-3">
				<label><strong>How old were you when the tics began to bother you? <span class="text-danger">*</span></strong></label>
				<input type="number" name="final_bother_age" class="form-control form-control-sm mt-1" style="max-width:150px;" value="<?= isset($_SESSION['ygtss_answers']['final_bother_age']) ? htmlspecialchars($_SESSION['ygtss_answers']['final_bother_age']) : '' ?>" required />
			</div>
			<div class="mb-3 ms-3">
				<label><strong>At what age did you seek treatment? <span class="text-danger">*</span></strong></label>
				<input type="number" name="final_treatment_age" class="form-control form-control-sm mt-1" style="max-width:150px;" value="<?= isset($_SESSION['ygtss_answers']['final_treatment_age']) ? htmlspecialchars($_SESSION['ygtss_answers']['final_treatment_age']) : '' ?>" required />
			</div>
			
			<input type="hidden" name="step" value="2">
			<button type="submit" name="back" value="1" class="btn btn-secondary me-2">Go Back</button>
			<button type="submit" class="btn btn-success">Submit</button>
		</form>
	<?php elseif ($step === 3): ?>
		<div class="alert alert-info p-4">
			<h4>Thank you for completing the YGTSS form!</h4>
			<p>Your responses have been recorded.</p>
			<h5>Summary:</h5>
			<strong>Part A1: Symptom Checklist</strong>
			<ul>
				<?php if (!empty($_SESSION['ygtss_answers']['A1'])): ?>
					<?php foreach ($_SESSION['ygtss_answers']['A1'] as $ans): ?>
						<li><?= is_array($ans) ? htmlspecialchars($ans['answer'] ?? '') . (isset($ans['onset']) && $ans['onset'] !== '' ? ' (Onset: ' . htmlspecialchars($ans['onset']) . ')' : '') : htmlspecialchars($ans) ?></li>
					<?php endforeach; ?>
				<?php else: ?>
					<li>No symptoms selected.</li>
				<?php endif; ?>
			</ul>
			<strong>Part A2: Tic Severity Scale</strong>
			<ul>
				<?php if (!empty($_SESSION['ygtss_answers']['A2'])): ?>
					<?php foreach ($_SESSION['ygtss_answers']['A2'] as $dim => $val): ?>
						<li><?= htmlspecialchars($dim) ?>: <?= htmlspecialchars($val) ?></li>
					<?php endforeach; ?>
				<?php else: ?>
					<li>No severity ratings provided.</li>
				<?php endif; ?>
			</ul>
			<strong>Final Information</strong>
			<ul>
				<li>Age when first tic appeared: <?= !empty($_SESSION['ygtss_answers']['final_first_tic_age']) ? htmlspecialchars($_SESSION['ygtss_answers']['final_first_tic_age']) : 'Not provided' ?></li>
				<li>Age when tics began to bother: <?= !empty($_SESSION['ygtss_answers']['final_bother_age']) ? htmlspecialchars($_SESSION['ygtss_answers']['final_bother_age']) : 'Not provided' ?></li>
				<li>Age when treatment was sought: <?= !empty($_SESSION['ygtss_answers']['final_treatment_age']) ? htmlspecialchars($_SESSION['ygtss_answers']['final_treatment_age']) : 'Not provided' ?></li>
			</ul>
		</div>
<!-- Bootstrap JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php endif; ?>
