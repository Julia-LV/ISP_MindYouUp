<?php
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

include_once __DIR__ . '/../../components/header_component.php';

$step = isset($_POST['step']) ? intval($_POST['step']) : 1;
$error_msg = '';

// Store answers between steps
session_start();
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
		for ($i = 0; $i < $total_a1; $i++) {
			if (!isset($a1_answers[$i]['answer']) || $a1_answers[$i]['answer'] === '') {
				$all_a1_answered = false;
				break;
			}
		}
		if ($all_a1_answered) {
			$_SESSION['ygtss_answers']['A1'] = $a1_answers;
			$_SESSION['ygtss_answers']['A1_simultaneous_tics'] = $_POST['A1_simultaneous_tics'] ?? '';
			$_SESSION['ygtss_answers']['A1_simultaneous_tics_desc'] = $_POST['A1_simultaneous_tics_desc'] ?? '';
			$_SESSION['ygtss_answers']['A1_multiple_groups'] = $_POST['A1_multiple_groups'] ?? '';
			$_SESSION['ygtss_answers']['A1_multiple_groups_desc'] = $_POST['A1_multiple_groups_desc'] ?? '';
			$step = 2;
		} else {
			$error_msg = 'Please answer all symptom checklist questions before proceeding.';
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
			if ($all_a2_answered) {
				$step = 3;
			} else {
				$error_msg = 'Please answer all tic severity questions before submitting.';
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
								<input class="form-check-input" type="radio" name="A1[<?= $qnum - 1 ?>][answer]" value="<?= $opt ?>" id="A1_<?= $qnum - 1 ?>_<?= $opt ?>"<?= (isset($prev['answer']) && $prev['answer'] === $opt) ? ' checked' : '' ?>>
								<label class="form-check-label" for="A1_<?= $qnum - 1 ?>_<?= $opt ?>">
									<?= $opt ?>
								</label>
							</div>
						<?php endforeach; ?>
						<input type="text" name="A1[<?= $qnum - 1 ?>][onset]" placeholder="Age of onset" class="form-control form-control-sm mt-1" style="max-width:150px;display:inline-block;" value="<?= isset($prev['onset']) ? htmlspecialchars($prev['onset']) : '' ?>" />
					</div>
					<?php $qnum++; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>

			<!-- Additional Yes/No Questions -->
			<?php $sim_tics_prev = $_SESSION['ygtss_answers']['A1_simultaneous_tics'] ?? ''; ?>
			<?php $sim_tics_desc_prev = $_SESSION['ygtss_answers']['A1_simultaneous_tics_desc'] ?? ''; ?>
			<div class="mb-3 mt-4">
				<label><strong>During the past week, have you had several distinct tics occurring simultaneously or sequentially at the same time?</strong></label><br>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="A1_simultaneous_tics" value="Yes" id="A1_simultaneous_tics_yes"<?= $sim_tics_prev === 'Yes' ? ' checked' : '' ?>>
					<label class="form-check-label" for="A1_simultaneous_tics_yes">Yes</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="A1_simultaneous_tics" value="No" id="A1_simultaneous_tics_no"<?= $sim_tics_prev === 'No' ? ' checked' : '' ?>>
					<label class="form-check-label" for="A1_simultaneous_tics_no">No</label>
				</div>
				<input type="text" name="A1_simultaneous_tics_desc" placeholder="If yes, describe" class="form-control form-control-sm mt-2" value="<?= htmlspecialchars($sim_tics_desc_prev) ?>" />
			</div>
			<?php $mult_groups_prev = $_SESSION['ygtss_answers']['A1_multiple_groups'] ?? ''; ?>
			<?php $mult_groups_desc_prev = $_SESSION['ygtss_answers']['A1_multiple_groups_desc'] ?? ''; ?>
			<div class="mb-3">
				<label><strong>Do you have more than one group of tics that occur together?</strong></label><br>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="A1_multiple_groups" value="Yes" id="A1_multiple_groups_yes"<?= $mult_groups_prev === 'Yes' ? ' checked' : '' ?>>
					<label class="form-check-label" for="A1_multiple_groups_yes">Yes</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="A1_multiple_groups" value="No" id="A1_multiple_groups_no"<?= $mult_groups_prev === 'No' ? ' checked' : '' ?>>
					<label class="form-check-label" for="A1_multiple_groups_no">No</label>
				</div>
				<input type="text" name="A1_multiple_groups_desc" placeholder="If yes, describe" class="form-control form-control-sm mt-2" value="<?= htmlspecialchars($mult_groups_desc_prev) ?>" />
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
				<label><strong>How old were you when your first tic appeared?</strong></label>
				<input type="number" name="final_first_tic_age" class="form-control form-control-sm mt-1" style="max-width:150px;" value="<?= isset($_SESSION['ygtss_answers']['final_first_tic_age']) ? htmlspecialchars($_SESSION['ygtss_answers']['final_first_tic_age']) : '' ?>" />
			</div>
			<div class="mb-3 ms-3">
				<label><strong>How old were you when the tics began to bother you?</strong></label>
				<input type="number" name="final_bother_age" class="form-control form-control-sm mt-1" style="max-width:150px;" value="<?= isset($_SESSION['ygtss_answers']['final_bother_age']) ? htmlspecialchars($_SESSION['ygtss_answers']['final_bother_age']) : '' ?>" />
			</div>
			<div class="mb-3 ms-3">
				<label><strong>At what age did you seek treatment?</strong></label>
				<input type="number" name="final_treatment_age" class="form-control form-control-sm mt-1" style="max-width:150px;" value="<?= isset($_SESSION['ygtss_answers']['final_treatment_age']) ? htmlspecialchars($_SESSION['ygtss_answers']['final_treatment_age']) : '' ?>" />
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
