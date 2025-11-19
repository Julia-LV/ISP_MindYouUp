<?php
/*
 * new_emotional_diary.php
 *
 * This is the "assembler" page for your new diary.
 * It follows your "sandwich" layout:
 * 1. Full-width Mood card (component)
 * 2. 3-column grid (component)
 * 3. Full-width Journal card (component)
 *
 * It uses your NEW components and NEW CSS file.
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

$message = "";
$message_type = "error";

// --- Security Check ---
// We assume the user is logged in.
// We'll add the proper security check later.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: ../auth/login.php"); // Redirect to login if not logged in
    exit;
}
if ($_SESSION["role"] != "Patient") {
    header("Location: ../professional/home_professional.php"); // Redirect if not a patient
    exit;
}
$patient_id = $_SESSION["user_id"];

// --- AGE CHECK LOGIC ---
// We need to check the patient's age to decide which component to show.
$patient_age = 0; // Default
$sql_age = "SELECT Age FROM user_profile WHERE User_ID = ?";
if ($stmt_age = $conn->prepare($sql_age)) {
    $stmt_age->bind_param("i", $patient_id);
    if ($stmt_age->execute()) {
        $stmt_age->bind_result($db_age);
        if ($stmt_age->fetch()) {
            // Assign the fetched age directly
            $patient_age = $db_age; 
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
    
    // We get the 'Ocurrence' from your DB structure
    $ocurrence = date('Y-m-d H:i:s'); 

    // Validation
    if (empty($emotion)) {
        $message = "Please select how you are feeling.";
    } else {
        // DEBUG: Check what patient_id is
        error_log("DEBUG: patient_id = " . $patient_id . ", emotion = " . $emotion);
        // Handle empty sleep value (make it NULL for the database)
        if ($sleep === '') { $sleep = null; }

        // Your DB has 7 fields in this order
        $sql = "INSERT INTO emotional_diary (Patient_ID, Emotion, Ocurrence, Stress, Anxiety, Sleep, Notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // 'isiiiss' = integer, string, integer, integer, integer, string, string
            $stmt->bind_param("isiiiss", $patient_id,  $emotion, $ocurrence, $stress, $anxiety, $sleep, $notes);
            
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
// This includes our (hopefully fixed) sidebar and sets up the main content area
//include '../../components/new_patient_layout_start.php'; 
include '../../components/header_component.php';
include '../../includes/navbar.php';

// Get the current page's filename (e.g., new_emotional_diary.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>


<!-- 
  We load our NEW page-specific styles from css/new_diary.css
  The path is ../../ (up twice) to the root, then down into css/
-->
<link rel="stylesheet" href="../../css/new_emotional_diary.css">

<body class="h-full bg-gray-100">
            <!-- 
              This is the main content wrapper.
              - It has `w-full` (full-width)
              - It has NO PADDING.
              - Your new_emotional_diary.php page adds its own padding.
            -->
    <main class="flex-1 w-full p-6 md:p-2 overflow-y-auto bg-[#FFFDF5]">

<!-- 
  This is the main container for THIS PAGE.
  It has padding (`p-6` or `p-8`) to keep content off the edges.
  It uses `space-y-6` to stack the cards vertically.
-->
        <div class="p-6 md:p-8 space-y-6">

    
    
            <!-- Include the NEW tabs component -->
            <?php 
            $active_tab = 'Entry';
            include '../../components/diary_tabs.php'; 
            ?>

            <!-- Start the Form -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">

                <!-- Message Handling (Full Width) -->
                <?php if (!empty($message)): ?>
                    <div class="p-4 rounded-md <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>" role="alert">
                        <p><?php echo htmlspecialchars($message); ?></p>
                    </div>
                <?php endif; ?>

                <!-- 
                  LAYER 1: Mood Selector (Conditional)
                  - Age <= 16: Shows Faces
                  - Age > 16: Shows VAS Slider
                -->
                <?php 
                if ($patient_age > 0 && $patient_age <= 16) {
                    if (file_exists('../../components/new_mood_selector.php')) {
                        include '../../components/new_mood_selector.php'; 
                    } else {
                        echo "<div class='text-red-500'>Error: Mood Selector component missing</div>";
                    }
                } else {
                    // Default or Age > 16
                    if (file_exists('../../components/vas_mood_selector.php')) {
                        include '../../components/vas_mood_selector.php';
                    } else {
                        echo "<div class='text-red-500'>Error: VAS component missing</div>";
                    }
                }
                ?>


                <!-- 
                  SANDWICH LAYER 2: (Full Width)
                  3-COLUMN LAYOUT FOR METRICS
                -->
                <?php include '../../components/metrics_grid.php'; ?>


                <!-- 
                  SANDWICH LAYER 3: (Full Width)
                  Journal Entry Component
                -->
                <?php include '../../components/journal_card.php'; ?>
        
        
                <!-- Form Submit Buttons -->
                <div class="flex items-center justify-end space-x-4">
                    <!-- Cancel Button -->
                    <a href="home_patient.php" class="text-center py-2.5 px-6 rounded-md text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300">
                        Cancel
                    </a>
                    <!-- Save Log Button (Reusing Button Component) -->
                    <div>
                        <?php
                        // We make this button smaller (not w-full)
                        $button_text = 'Save Entry'; $button_type = 'submit'; $extra_classes = 'px-6'; 
                        include '../../components/button.php';
                        ?>
                    </div>
                </div>

            </form>
        </div> <!-- Closes the p-6 md:p-8 space-y-6 div -->
    </main> <!-- Closes the main flex-1 w-full div -->
</body>
</html>

<!-- 
  NEW JAVASCRIPT SECTION
  We need a *tiny* bit of JS to make the sliders show their value.
  As per our rule, this is page-specific, so we add it here.
  In the future, we can move this to 'js/new_diary.js'.
-->
<script>
    // Find the anxiety slider and its text
    const anxietySlider = document.getElementById('anxiety');
    const anxietyValue = document.getElementById('anxiety-value');
    // Find the stress slider and its text
    const stressSlider = document.getElementById('stress');
    const stressValue = document.getElementById('stress-value');

    // When the anxiety slider is moved, update its text
    if (anxietySlider) {
        anxietySlider.addEventListener('input', (event) => {
            anxietyValue.textContent = `Selected: ${event.target.value}`;
        });
    }
    
    // When the stress slider is moved, update its text
    if (stressSlider) {
        stressSlider.addEventListener('input', (event) => {
            stressValue.textContent = `Selected: ${event.target.value}`;
        });
    }
</script>
