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
                $message = "Great! Recorded no tics for today.";
                $message_type = "success";
            } else {
                $message = "Error recording entry: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    // 2. Standard Entry
    else {
        // Construct Tic Description (Category + Specific)
        $cat = $_POST['tic_category'] ?? '';
        $spec = $_POST['specific_tic'] ?? '';
        $type_description = ($cat && $spec) ? "$cat: $spec" : "";

        // Get other values
        $muscle = !empty($_POST['muscle_select']) ? $_POST['muscle_select'] : null;
        $duration = $_POST['duration'] ?? '';
        $intensity = intval($_POST['intensity'] ?? 0);
        $pain = intval($_POST['stress'] ?? 0); // Reusing 'stress' logic for pain
        $notes = trim($_POST['notes'] ?? '');
        $pre_tic = $_POST['pre_tic'] ?? null;
        $self_reported = ($_POST['self_reported'] === 'patient') ? 1 : 0;

        // Validation
        if (empty($spec) || empty($duration)) {
            $message = "Please select the specific Tic and the Duration.";
        } else {
            $sql = "INSERT INTO tic_log (Patient_ID, Type_Description, Muscle_Group, Duration, Intensity, Pain_Level, Premonitory_Urge, Describe_Text, Self_Reported, Created_At) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("isssiissi", $patient_id, $type_description, $muscle, $duration, $intensity, $pain, $pre_tic, $notes, $self_reported);

                if ($stmt->execute()) {
                    $message = "Tic entry logged successfully!";
                    $message_type = "success";
                } else {
                    $message = "Something went wrong. " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "Database error: " . $conn->error;
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



        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">

            <?php if (!empty($message)): ?>
                <div class="p-4 rounded-md <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>" role="alert">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>


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
                    $min = 0; $max = 5; $val = 0;
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
                    include '../../components/button.php';
                    ?>
                </div>

                <div class="w-auto">
                    <?php
                    $label = 'Save Entry';
                    $type = 'submit';
                    $variant = 'primary';
                    $width = 'w-auto';
                    // We reset variables we don't need to ensure cleanliness
                    $href = null;
                    include '../../components/button.php';
                    ?>
                </div>

            </div>

        </form>
    </div>
</main>

</div>
</body>

</html>

<script>
    // 1. Data Source (From your textbook image)
    const ticData = {
        "Simple motor tics": ["Eye blinking", "Eye movements", "Nose movements", "Mouth movements", "Facial grimace", "Head jerks/movements", "Shoulder shrugs", "Arm movements", "Hand movements", "Abdominal tensing", "Leg/foot/toe movements"],
        "Complex motor tics": ["Eye movements", "Mouth movements", "Facial expressions", "Head gestures", "Shoulder movements", "Arm/Hand movements", "Writing tics", "Dystonic postures", "Bending/Gyrating", "Rotating", "Blocking", "Compulsive behaviors", "Copropraxia", "Self-abusive behavior"],
        "Simple vocal tics": ["Sounds/Noises (coughing, throat clearing, sniffing, animal noises)"],
        "Complex phonic symptoms": ["Syllables", "Words", "Coprolalia", "Echolalia", "Palilalia", "Blocking", "Disinhibited speech"]
    };

    const autoMap = {
        "Eye blinking": "Orbicularis oculi (eyes)",
        "Eye movements": "Orbicularis oculi (eyes)",
        "Nose movements": "Facial muscles",
        "Mouth movements": "Facial muscles",
        "Facial grimace": "Facial muscles",
        "Head jerks/movements": "Neck muscles",
        "Shoulder shrugs": "Shoulders / Upper trapezius",
        "Abdominal tensing": "Abdominal muscles",
        "Leg/foot/toe movements": "Legs / Feet"
    };

    // 2. Tab Switching Logic
    function switchTab(type) {
        // 1. Set the hidden input value so PHP knows what to save
        document.getElementById('active_context').value = type;

        // 2. Define our styling classes
        // Note: These must match what is in diary_tabs.php
        const activeClass = 'active font-semibold text-[#005949] border-b-2 border-[#005949]';
        const inactiveClass = 'font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent';

        // 3. Get the Button Elements
        // The component generates IDs based on the label: "Motor Tics" -> "tab-btn-motor"
        const btnMotor = document.getElementById('tab-btn-motor');
        const btnVocal = document.getElementById('tab-btn-vocal');

        // 4. Get the Content Panes (The forms)
        // Ensure your HTML divs still have these IDs: id="pane-motor" and id="pane-vocal"
        const paneMotor = document.getElementById('pane-motor');
        const paneVocal = document.getElementById('pane-vocal');

        // 5. The Switching Logic
        if (type === 'motor') {
            // Update Buttons
            btnMotor.className = "flex-1 py-3 text-center focus:outline-none transition-colors " + activeClass;
            btnVocal.className = "flex-1 py-3 text-center focus:outline-none transition-colors " + inactiveClass;

            // Show/Hide Panes
            paneMotor.style.display = 'block';
            paneVocal.style.display = 'none';
        } else {
            // Update Buttons
            btnVocal.className = "flex-1 py-3 text-center focus:outline-none transition-colors " + activeClass;
            btnMotor.className = "flex-1 py-3 text-center focus:outline-none transition-colors " + inactiveClass;

            // Show/Hide Panes
            paneVocal.style.display = 'block';
            paneMotor.style.display = 'none';
        }
    }

    // 3. Dynamic Dropdowns
    function setupDropdowns(catID, specID, isMotor) {
        const catSelect = document.getElementById(catID);
        const specSelect = document.getElementById(specID);
        const muscleSelect = document.getElementById('muscle_select');

        catSelect.addEventListener('change', function() {
            const val = this.value;
            specSelect.innerHTML = '<option value="">-- Select Tic --</option>';
            if (val && ticData[val]) {
                specSelect.disabled = false;
                ticData[val].forEach(tic => {
                    specSelect.add(new Option(tic, tic));
                });
            } else {
                specSelect.disabled = true;
            }
        });

        if (isMotor) {
            specSelect.addEventListener('change', function() {
                const t = this.value;
                if (autoMap[t]) muscleSelect.value = autoMap[t];
            });
        }
    }

    setupDropdowns('motor_cat', 'motor_spec', true);
    setupDropdowns('vocal_cat', 'vocal_spec', false);

    // 4. Update Slider Text (reusing logic from emotional diary)
    ['intensity', 'stress'].forEach(id => {
        const el = document.getElementById(id);
        const val = document.getElementById(id + '-value');
        if (el && val) {
            el.addEventListener('input', (e) => val.textContent = `Selected: ${e.target.value}`);
        }
    });

    // 5. Reporter Toggle
    function setReporter(role, btn) {
        document.getElementById('self_reported').value = role;

        // RESET ALL BUTTONS
        // We ensure we keep 'w-1/2' here so they stay 50% width
        document.querySelectorAll('.rep-btn').forEach(b => {
            b.className = 'rep-btn w-1/2 py-2 rounded text-gray-500 text-sm font-medium transition-all hover:text-gray-700';
        });

        // SET ACTIVE BUTTON
        // We also ensure 'w-1/2' is here
        btn.className = 'rep-btn w-1/2 py-2 rounded bg-white shadow-sm text-[#005949] text-sm font-bold transition-all';
    }

    // 6. Form Submit: Populate hidden fields based on active tab
    document.querySelector('form').addEventListener('submit', function(e) {
        if (e.submitter && e.submitter.name === 'no_tics') return;

        const context = document.getElementById('active_context').value;
        const finalCat = document.getElementById('final_tic_category');
        const finalSpec = document.getElementById('final_specific_tic');

        if (context === 'motor') {
            finalCat.value = document.getElementById('motor_cat').value;
            finalSpec.value = document.getElementById('motor_spec').value;
        } else {
            finalCat.value = document.getElementById('vocal_cat').value;
            finalSpec.value = document.getElementById('vocal_spec').value;
            document.getElementById('muscle_select').value = ""; // Clear muscle for vocal
        }
    });
</script>