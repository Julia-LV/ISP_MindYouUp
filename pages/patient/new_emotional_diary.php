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
        // Handle empty sleep value (make it NULL for the database)
        if ($sleep === '') { $sleep = null; }

        // Your DB has 7 fields in this order
        $sql = "INSERT INTO emotional_diary (Patient_ID, Ocurrence, Stress, Anxiety, Sleep, Notes, Emotion) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // 'isiiiss' = integer, string, integer, integer, integer, string, string
            $stmt->bind_param("isiiiss", $patient_id, $ocurrence, $stress, $anxiety, $sleep, $notes, $emotion);
            
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
include '../../components/new_patient_layout_start.php'; 
?>

<!-- 
  We load our NEW page-specific styles from css/new_diary.css
  The path is ../../ (up twice) to the root, then down into css/
-->
<link rel="stylesheet" href="../../css/new_emotional_diary.css">


<!-- 
  This is the main container for THIS PAGE.
  It has padding (`p-6` or `p-8`) to keep content off the edges.
  It uses `space-y-6` to stack the cards vertically.
-->
<div class="p-6 md:p-8 space-y-6">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-[#F26647] text-center">Emotional Diary</h1>
    </div>
    
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
          SANDWICH LAYER 1: (Full Width)
          Mood Selector Component
        -->
        <?php include '../../components/new_mood_selector.php'; ?>


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
</div>

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


<?php 
// This closes the <body> and <html> tags
include '../../components/new_patient_layout_end.php'; 
?>