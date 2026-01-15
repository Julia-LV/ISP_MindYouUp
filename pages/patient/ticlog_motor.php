<?php
/*
 * ticlog.php
 *
 * Adapted from new_emotional_diary.php to maintain consistency.
 * "Assembler" pattern:
 * 1. Tic Selector Card (Custom implementation of Mood Card)
 * 2. 3-column grid (Duration, Intensity, Pain)
 * 3. Full-width Journal card (Reused Component)
 */

// --- 1. PHP Logic ---
session_start();

// --- CONFIG LOAD CHECK ---
$config_path = '../../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("<strong>Error:</strong> Could not find config.php");
}

// --- DATABASE CONNECTION CHECK ---
if (!isset($conn)) {
    die("<strong>Database Error:</strong> Connection variable \$conn is missing.");
}

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Check for "No Tics" Shortcut
    if (isset($_POST['no_tics'])) {
        $sql = "INSERT INTO tic_log (Patient_ID, Type_Description, Duration, Intensity, Describe_Text, Self_Reported, Created_At) 
                VALUES (?, 'No Tics Today', 'No tics', 0, 'Patient reported no tics today.', 1, NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $patient_id);
            if ($stmt->execute()) {
                header("Location: ticlog_motor.php?status=success&msg=added");
                exit;
            } else {
                $message = "Error recording entry: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    // 2. Standard Entry
    else {
        // --- 1. CAPTURE DATA ---

        // Column 1: Type (Motor vs Vocal)
        // We get this from the hidden input 'active_context'
        $main_type = ucfirst($_POST['active_context'] ?? 'Motor');

        // Column 2: Category (Simple vs Complex)
        // We get this from the dropdown (e.g., "Simple motor tics")
        $category = $_POST['tic_category'] ?? '';

        // Column 3: Type_Description (Specific Tic)
        // We get this from the second dropdown (e.g., "Eye blinking")
        $specific_tic = $_POST['specific_tic'] ?? '';

        // Other Fields
        $muscle = !empty($_POST['muscle_select']) ? $_POST['muscle_select'] : null;
        $duration = $_POST['duration'] ?? '';
        $intensity = intval($_POST['intensity'] ?? 0);
        $pain = intval($_POST['stress'] ?? 0);
        $self_reported = ($_POST['self_reported'] === 'patient') ? 1 : 0;
        $pre_tic = $_POST['pre_tic'] ?? null;
        $notes = trim($_POST['notes'] ?? '');

        // --- 2. VALIDATION ---
        if (empty($category) || empty($specific_tic) || empty($duration)) {
            $message = "Please select the Tic Category, Specific Tic, and Duration.";
        } else {

            // --- 3. UPDATED SQL INSERT ---
            // Now mapping to: Type, Category, Type_Description
            $sql = "INSERT INTO tic_log 
                    (Patient_ID, Type, Category, Type_Description, Muscle_Group, Duration, Intensity, Pain_Level, Premonitory_Urge, Describe_Text, Self_Reported, Created_At) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            if ($stmt = $conn->prepare($sql)) {
                // --- 4. BIND PARAMETERS ---
                // i = Patient_ID
                // s = Type (Motor/Vocal)
                // s = Category (Simple/Complex)
                // s = Type_Description (Specific Tic)
                // s = Muscle_Group
                // s = Duration
                // i = Intensity
                // i = Pain_Level
                // s = Premonitory_Urge
                // s = Describe_Text
                // i = Self_Reported

                $stmt->bind_param(
                    "isssssiissi",
                    $patient_id,
                    $main_type,     // e.g. "Motor"
                    $category,      // e.g. "Simple motor tics"
                    $specific_tic,  // e.g. "Eye blinking"
                    $muscle,
                    $duration,
                    $intensity,
                    $pain,
                    $pre_tic,
                    $notes,
                    $self_reported
                );

                if ($stmt->execute()) {
                    $message = "Tic entry logged successfully!";
                    $message_type = "success";
                } else {
                    $message = "Database Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "Prepare Error: " . $conn->error;
            }
        }
    }
}
// --- END PHP LOGIC ---


// --- 2. Page Display ---
$page_title = 'Tic Log';

// We load the same header/navbar as Emotional Diary
include '../../components/header_component.php';
include '../../includes/navbar.php';
?>


<link rel="stylesheet" href="../../css/ticlog_motor.css">
<link rel="stylesheet" href="../../css/new_emotional_diary.css">

<!-- Main Content Wrapper -->
<main class="flex-1 w-full p-6 md:p-2 overflow-y-auto bg-[#E9F0E9]">

    <div class="p-6 md:p-8 space-y-6 max-w-7xl mx-auto">
        <div class="text-left">
            <h2 class="text-3xl font-bold text-[#005949] mb-2">
                <?php echo htmlspecialchars($page_title); ?>
            </h2>
        </div>

        <form method="POST" class="mb-6" id="form-no-tics">
            <input type="hidden" name="no_tics" value="true">
            <div class="bg-gradient-to-r from-emerald-50 to-white border border-emerald-100 p-4 rounded-lg shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-full text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-emerald-900">Good day so far?</h3>
                        <p class="text-sm text-emerald-700">If you haven't experienced any tics, log it quickly here.</p>
                    </div>
                </div>

                <button type="button" onclick="askConfirm('no_tics')" class="w-full md:w-auto px-6 py-2.5 bg-[#005949] hover:bg-[#004539] text-white font-bold rounded-md shadow-sm transition-all flex items-center justify-center gap-2">

                    <span>No Tic Today!</span>
                </button>
            </div>
        </form>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6" id="form-main">




            <div class="bg-white p-6 rounded-lg shadow-sm">
                <?php
                $tabs = [
                    'Motor Tics' => "switchTab('motor')",
                    'Vocal Tics' => "switchTab('vocal')"
                ];
                $active_tab = 'Motor Tics';
                $is_js = true; // Tell component to use buttons, not links
                include '../../components/diary_tabs.php';
                ?>



                <input type="hidden" name="active_context" id="active_context" value="motor">
                <input type="hidden" name="tic_category" id="final_tic_category">
                <input type="hidden" name="specific_tic" id="final_specific_tic">

                <div id="pane-motor" class="tab-pane active space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Complexity</label>
                            <select id="motor_cat" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#005949] focus:border-[#005949]">
                                <option value="">-- Select --</option>
                                <option value="Simple motor tics">Simple Motor</option>
                                <option value="Complex motor tics">Complex Motor</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Specific Tic</label>
                            <select id="motor_spec" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#005949] focus:border-[#005949]" disabled>
                                <option value="">-- Select Complexity First --</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Muscle Group (Optional)</label>
                            <select name="muscle_select" id="muscle_select" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#005949] focus:border-[#005949]">
                                <option value="">-- Select --</option>
                                <option>Orbicularis oculi (eyes)</option>
                                <option>Facial muscles</option>
                                <option>Neck muscles</option>
                                <option>Shoulders / Upper trapezius</option>
                                <option>Arms / Hands</option>
                                <option>Abdominal muscles</option>
                                <option>Legs / Feet</option>
                                <option>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="pane-vocal" class="tab-pane space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Complexity</label>
                            <select id="vocal_cat" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#005949] focus:border-[#005949]">
                                <option value="">-- Select --</option>
                                <option value="Simple vocal tics">Simple Vocal</option>
                                <option value="Complex phonic symptoms">Complex Phonic</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Specific Tic</label>
                            <select id="vocal_spec" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#005949] focus:border-[#005949]" disabled>
                                <option value="">-- Select Complexity First --</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Duration</label>
                    <select name="duration" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#005949] focus:border-[#005949]">
                        <option value="">-- Select --</option>
                        <option>Less than a minute</option>
                        <option>1 - 5 minutes</option>
                        <option>More than 5 minutes</option>
                        <option>Continuous / Flurry</option>
                    </select>
                </div>

                <?php
                $label = 'Intensity';
                $id = 'intensity';
                $name = 'intensity';
                // We reset vars to be safe
                include '../../components/slider_card.php';
                ?>

                <?php
                $label = 'Pain / Discomfort';
                // We map this to 'stress' or 'pain_meter' depending on your DB column
                $id = 'stress';
                $name = 'stress';
                $min = 0;
                $max = 10;
                $val = 0;
                include '../../components/slider_card.php';
                ?>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Did you feel it coming? (Premonitory Urge)
                    </label>
                    <div class="flex items-center  gap-20">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="pre_tic" value="yes" checked
                                class="w-5 h-5 text-[#005949] focus:ring-[#005949] border-gray-300">
                            <span class="ml-2 text-gray-700 font-medium">Yes</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="pre_tic" value="no"
                                class="w-5 h-5 text-[#005949] focus:ring-[#005949] border-gray-300">
                            <span class="ml-2 text-gray-700 font-medium">No</span>
                        </label>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col justify-between">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Reported By
                    </label>
                    <input type="hidden" name="self_reported" id="self_reported" value="patient">

                    <div class="flex bg-gray-100 p-1 rounded-md">
                        <button type="button" onclick="setReporter('patient', this)"
                            class="rep-btn w-1/2 py-2 rounded shadow-sm bg-white text-[#005949] text-sm font-bold transition-all">
                            Self
                        </button>
                        <button type="button" onclick="setReporter('caregiver', this)"
                            class="rep-btn w-1/2 py-2 rounded text-gray-500 text-sm font-medium transition-all hover:text-gray-700">
                            Caregiver
                        </button>
                    </div>
                </div>

            </div>


            <?php
            $journal_title = 'Describe the Tic Episode';
            $journal_placeholder = 'Describe the environment, triggers, or specific details about the tic...';
            $journal_rows = 4; // Make it a bit shorter for Tics

            include '../../components/journal_card.php';
            ?>

            <div class="flex items-center justify-end space-x-4 mt-8">

                <div class="w-auto">
                    <?php
                    $label = 'Cancel';
                    $type = 'link';
                    $href = 'home_patient.php';
                    $variant = 'secondary';
                    $width = 'w-auto';
                    $onclick = '';
                    include '../../components/button.php';
                    ?>
                </div>

                <div class="w-auto">
                    <?php
                    $label = 'Save Entry';
                    $type = 'button';
                    $variant = 'primary';
                    $width = 'w-auto';
                    // We reset variables we don't need to ensure cleanliness
                    $href = null;
                    $onclick = "askConfirm('main')";
                    include '../../components/button.php';
                    ?>
                </div>

            </div>

        </form>
    </div>
</main>

<?php include '../../components/modals.php'; ?>
</body>

</html>

<script src="../../js/patient/ticlog_motor.js"></script>

<?php if (!empty($message) && $message_type === 'success'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pass the PHP message to the JS function so you can see the Log ID
            openSuccess("Entry Recorded!", "<?php echo $message; ?>");
        });
    </script>
<?php endif; ?>