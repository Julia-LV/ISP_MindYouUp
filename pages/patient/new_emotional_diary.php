<?php
/*
 * new_emotional_diary.php
 */

// --- 1. PHP Logic ---
session_start();

// --- CONFIG LOAD CHECK ---
$config_path = '../../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("<strong>Error:</strong> Could not find config.php at $config_path");
}

// --- DATABASE CONNECTION CHECK ---
if (!isset($conn)) {
    die("<strong>Database Error:</strong> Connection variable \$conn is missing.");
}

date_default_timezone_set('Europe/Lisbon');

$message = "";
$message_type = "error";

// --- Security Check ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: ../auth/login.php"); 
    exit;
}
if ($_SESSION["role"] != "Patient") {
    header("Location: ../professional/home_professional.php");
    exit;
}
$patient_id = $_SESSION["user_id"];

// --- AGE CHECK LOGIC (UPDATED FOR DATE OF BIRTH) ---
// We calculate age dynamically from Date_Birth
$patient_age = 0; // Default

// CHANGED: Select Date_Birth instead of Age
$sql_age = "SELECT Birthday FROM user_profile WHERE User_ID = ?";
if ($stmt_age = $conn->prepare($sql_age)) {
    $stmt_age->bind_param("i", $patient_id);
    if ($stmt_age->execute()) {
        $stmt_age->bind_result($db_dob);
        if ($stmt_age->fetch()) {
            // Calculate Age from DOB
            if ($db_dob) {
                try {
                    $dob_date = new DateTime($db_dob);
                    $now = new DateTime();
                    $interval = $now->diff($dob_date);
                    $patient_age = $interval->y; // This gives the age in years
                } catch (Exception $e) {
                    // Fallback if date parsing fails
                    $patient_age = 20; 
                }
            }
        }
    }
    $stmt_age->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../../config.php';

    // Get all the data from the form
    $emotion = $_POST['emotion'] ?? '';
    $sleep = $_POST['sleep'] ?? null;
    $anxiety = $_POST['anxiety'] ?? 0;
    $stress = $_POST['stress'] ?? 0;
    $notes = trim($_POST['notes'] ?? '');
    $occurrence = date('Y-m-d H:i:s');

    // Validation
    if (empty($emotion)) {
        $message = "Please select how you are feeling.";
    } else {
        if ($sleep === '') {
            $sleep = null;
        }

        $sql = "INSERT INTO emotional_diary (Patient_ID, Emotion, Occurrence, Stress, Anxiety, Sleep, Notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issiiis", $patient_id,  $emotion, $occurrence, $stress, $anxiety, $sleep, $notes);

            if ($stmt->execute()) {
                $message = "Your diary has been logged successfully!";
                $message_type = "success";
            } else {
                $message = "Something went wrong. Please try again. " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Something went wrong with the database. " . $conn->error;
        }
    }
    $conn->close();
}
// --- END PHP LOGIC ---


// --- 2. Page Display ---
$page_title = 'Emotional Diary';

include '../../components/header_component.php';

// Get the current page's filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<?php include '../../includes/navbar.php'; ?>

<div class="w-full p-6 md:p-2 overflow-y-auto bg-[#E9F0E9]">

    <div class="p-6 md:p-8 space-y-6 max-w-7xl mx-auto">
        <div class="text-left">
            <h2 class="text-3xl font-bold text-[#005949] mb-2">
                <?php echo htmlspecialchars($page_title); ?>
            </h2>
        </div>

        <?php
        $tabs = [
            'Entry'   => 'new_emotional_diary.php',
            'Visuals' => 'emotional_diary_visuals.php'
        ];
        $active_tab = 'Entry';
        $is_js = false; 
        include '../../components/diary_tabs.php';
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6" id="emotional-form">

            <?php
            // Logic uses the calculated $patient_age
            if ($patient_age > 0 && $patient_age <= 16) {
                if (file_exists('../../components/new_mood_selector.php')) {
                    include '../../components/new_mood_selector.php';
                }
            } else {
                if (file_exists('../../components/vas_mood_selector.php')) {
                    include '../../components/vas_mood_selector.php';
                }
            }
            ?>

            <?php if (file_exists('../../components/metrics_grid.php')) include '../../components/metrics_grid.php'; ?>

            <?php if (file_exists('../../components/journal_card.php')) include '../../components/journal_card.php'; ?>

            <div class="flex items-center justify-end space-x-4 mt-8">

                <div class="w-auto">
                    <?php
                    $label = 'Cancel';
                    $type = 'link';
                    $href = 'home_patient.php';
                    $variant = 'secondary';
                    $width = 'w-auto';
                    include '../../components/button.php';
                    ?>
                </div>

                <div class="w-auto">
                    <?php
                    $label = 'Save Entry';
                    $type = 'button';
                    $variant = 'primary';
                    $width = 'w-auto';
                    $href = null;
                    $onclick = "askConfirm()";
                    include '../../components/button.php';
                    ?>
                </div>

        </form>
    </div>
</div>

</div>
<?php include '../../components/modals.php'; ?>
</body>
</html>

<script>
    function askConfirm() {
        let isMoodSelected = false;
        const radios = document.querySelectorAll('input[name="emotion"][type="radio"]');

        if (radios.length > 0) {
            const checkedRadio = document.querySelector('input[name="emotion"]:checked');
            if (checkedRadio) isMoodSelected = true;
        } 
        else {
            const inputField = document.querySelector('input[name="emotion"]');
            if (inputField && inputField.value.trim() !== "") isMoodSelected = true;
        }

        if (!isMoodSelected) {
            alert("Please select a mood before saving.");
            return; 
        }

        openConfirm(
            "Log Mood",                   
            "Are you sure you want to save this emotional entry?", 
            "Yes, Save Mood"              
        );
    }

    document.getElementById('globalConfirmBtn').addEventListener('click', function() {
        document.getElementById('emotional-form').submit();
    });

    const anxietySlider = document.getElementById('anxiety');
    const anxietyValue = document.getElementById('anxiety-value');
    const stressSlider = document.getElementById('stress');
    const stressValue = document.getElementById('stress-value');

    if (anxietySlider) {
        anxietySlider.addEventListener('input', (event) => {
            anxietyValue.textContent = `Selected: ${event.target.value}`;
        });
    }

    if (stressSlider) {
        stressSlider.addEventListener('input', (event) => {
            stressValue.textContent = `Selected: ${event.target.value}`;
        });
    }
</script>

<?php if (!empty($message) && $message_type === 'success'): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openSuccess("Mood Logged!", "Your emotional diary entry has been saved successfully.");
    });
</script>
<?php endif; ?>