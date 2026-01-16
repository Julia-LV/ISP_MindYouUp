<?php


session_start();
$config_path = '../../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    $conn = null;
}

// Set timezone to Europe/Lisbon to match local time
date_default_timezone_set('Europe/Lisbon');

// --- SECURITY CHECK ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != "Patient") {
    // header("Location: ../auth/login.php"); exit;
}
$patient_id = $_SESSION["user_id"] ?? 1;

// =================================================================================
// 1. AJAX HANDLER 
// =================================================================================
if (isset($_GET['ajax_fetch']) && isset($_GET['offset'])) {
    header('Content-Type: application/json');

    $offset = intval($_GET['offset']);
    $today_str = date('Y-m-d');

    $resp_labels = [];
    $resp_tics = [];
    $resp_intensity = [];
    $resp_stress = [];
    $resp_sleep = [];
    $resp_anxiety = [];

    if ($conn) {
        for ($i = 6; $i >= 0; $i--) {
            $days_ago = $offset + $i;
            $timestamp = strtotime("-$days_ago days");
            $date_db = date('Y-m-d', $timestamp);

            $day_name = date('D', $timestamp);
            $day_date = date('d M', $timestamp);
            $second_line = ($date_db === $today_str) ? "Today" : $day_date;
            $resp_labels[] = [$day_name, $second_line];

            // Tic Data
            $sql = "SELECT COUNT(*) as c, AVG(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ? AND Type_Description != 'No Tics Today'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $patient_id, $date_db);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $resp_tics[] = $res['c'] ?? 0;
            $resp_intensity[] = $res['i'] ? round($res['i'], 1) : 0;
            $stmt->close();

            // Mood Data
            $sql_e = "SELECT AVG(Stress) as s, AVG(Sleep) as sl, AVG(Anxiety) as a FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?";
            $stmt_e = $conn->prepare($sql_e);
            $stmt_e->bind_param("is", $patient_id, $date_db);
            $stmt_e->execute();
            $res_e = $stmt_e->get_result()->fetch_assoc();
            $resp_stress[] = $res_e['s'] ? round($res_e['s'], 1) : 0;
            $resp_sleep[] = $res_e['sl'] ? round($res_e['sl'], 1) : 0;
            $resp_anxiety[] = $res_e['a'] ? round($res_e['a'], 1) : 0;
            $stmt_e->close();
        }
    } else {
        // Mock Data Fallback
        for ($i = 6; $i >= 0; $i--) {
            $resp_labels[] = ['Mon', 'Date'];
            $resp_tics[] = 0;
            $resp_intensity[] = 0;
            $resp_stress[] = 0;
            $resp_sleep[] = 0;
            $resp_anxiety[] = 0;
        }
    }

    echo json_encode([
        'labels' => $resp_labels,
        'tics' => $resp_tics,
        'intensity' => $resp_intensity,
        'stress' => $resp_stress,
        'sleep' => $resp_sleep,
        'anxiety' => $resp_anxiety
    ]);
    exit;
}

// --- PAGE LOAD LOGIC ---
$patient_name = $_SESSION["First_Name"] ?? "Patient";
if (($patient_name === "Patient") && $conn) {
    $sql_name = "SELECT First_Name FROM user_profile WHERE User_ID = ?";
    if ($stmt = $conn->prepare($sql_name)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        if ($row = $stmt->get_result()->fetch_assoc()) {
            $patient_name = $row['First_Name'];
        }
        $stmt->close();
    }
}
$hour = date('G');
$greeting_text = ($hour < 12) ? "Good Morning" : (($hour < 18) ? "Good Afternoon" : "Good Evening");

// Today Stats
$today_tics = 0;
$avg_stress = 0;
if ($conn) {
    $sql_tics = "SELECT COUNT(*) as count FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = CURDATE() AND Type_Description != 'No Tics Today'";
    $stmt = $conn->prepare($sql_tics);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $today_tics = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    $sql_stress = "SELECT AVG(Stress) as avg_stress FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = CURDATE()";
    if ($stmt = $conn->prepare($sql_stress)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $avg_stress = $res['avg_stress'] !== null ? round($res['avg_stress'], 1) : 0;
        $stmt->close();
    }
}
$stress_icon = 'smile';
$stress_color_class = 'text-green-500 bg-green-50 border-green-400';
if ($avg_stress > 3 && $avg_stress <= 7) {
    $stress_icon = 'meh';
    $stress_color_class = 'text-orange-500 bg-orange-50 border-orange-400';
} elseif ($avg_stress > 7) {
    $stress_icon = 'frown';
    $stress_color_class = 'text-red-500 bg-red-50 border-red-400';
}

// --- MEDICATION FETCHING LOGIC ---
$med_name_display = "All caught up!";
$med_time_display = "";
$med_count = 0;
$has_more = false;

if ($conn) {
    // Select meds for this user that haven't been marked as taken today
    // Order by Reminder_DateTime so the earliest/overdue one shows up first
    $sql_med = "SELECT Medication_Name, Medication_Time 
                FROM track_medication 
                WHERE Patient_ID = ? AND Medication_Status = 0 
                ORDER BY Medication_Time ASC";

    if ($stmt_m = $conn->prepare($sql_med)) {
        $stmt_m->bind_param("i", $patient_id);
        $stmt_m->execute();
        $res_m = $stmt_m->get_result();

        $pending_meds = [];
        while ($row = $res_m->fetch_assoc()) {
            $pending_meds[] = $row;
        }
        $stmt_m->close();

        $med_count = count($pending_meds);

        if ($med_count > 0) {
            // Grab the first medication in the list
            $first_med = $pending_meds[0];
            $med_name_display = htmlspecialchars($first_med['Medication_Name']);

            // Format the time (e.g., 08:00 PM) if a time is set
            if (!empty($first_med['Medication_Time'])) {
                $med_time_display = date('h:i A', strtotime($first_med['Medication_Time']));
            } else {
                $med_time_display = "Today";
            }

            // If there is more than 1 pending med, trigger the 'more' flag
            if ($med_count > 1) {
                $has_more = true;
            }
        }
    }
}

// Initial Arrays (Current Week)
$dates = [];
$tic_counts = [];
$tic_intensities = [];
$stress_levels = [];
$sleep_quality = [];
$anxiety_levels = [];
$today_str = date('Y-m-d');

if ($conn) {
    for ($i = 6; $i >= 0; $i--) {
        $timestamp = strtotime("-$i days");
        $date_db = date('Y-m-d', $timestamp);

        $day_name = date('D', $timestamp);
        $day_date = date('d M', $timestamp);
        $second_line = ($date_db === $today_str) ? "Today" : $day_date;
        $dates[] = [$day_name, $second_line];

        $sql = "SELECT COUNT(*) as c, AVG(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ? AND Type_Description != 'No Tics Today'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $patient_id, $date_db);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $tic_counts[] = $res['c'] ?? 0;
        $tic_intensities[] = $res['i'] ? round($res['i'], 1) : 0;
        $stmt->close();

        $sql = "SELECT AVG(Stress) as s, AVG(Sleep) as sl, AVG(Anxiety) as a FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $patient_id, $date_db);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stress_levels[] = $res['s'] ? round($res['s'], 1) : 0;
        $sleep_quality[] = $res['sl'] ? round($res['sl'], 1) : 0;
        $anxiety_levels[] = $res['a'] ? round($res['a'], 1) : 0;
        $stmt->close();
    }

    // --- UPDATED AFFECTED AREAS LOGIC (CUMULATIVE) ---
    $muscle_labels = [];
    $muscle_data = [];

    $sql = "SELECT Muscle_Group, COUNT(*) as count 
            FROM tic_log 
            WHERE Patient_ID = ? AND Muscle_Group != '' AND Muscle_Group IS NOT NULL 
            GROUP BY Muscle_Group 
            ORDER BY count DESC 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $muscle_labels[] = $row['Muscle_Group'];
        $muscle_data[] = $row['count'];
    }
    $stmt->close();

    // --- UPDATED DAILY RHYTHM LOGIC  ---
    $hourly_activity = array_fill(0, 24, 0);

    $sql = "SELECT HOUR(Created_At) as tic_hour, COUNT(*) as count 
            FROM tic_log 
            WHERE Patient_ID = ? AND DATE(Created_At) = CURDATE() AND Type_Description != 'No Tics Today'
            GROUP BY HOUR(Created_At)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $hourly_activity[$row['tic_hour']] = $row['count'];
    }
    $stmt->close();
} else {
    // Fallback Mock Data
    $dates = [['M', 'D'], ['T', 'D'], ['W', 'D'], ['T', 'D'], ['F', 'D'], ['S', 'D'], ['S', 'D']];
    $tic_counts = [0, 0, 0, 0, 0, 0, 0];
    $muscle_labels = ['Eyes', 'Neck'];
    $muscle_data = [10, 5];
    $hourly_activity = array_fill(0, 24, 0);
}


// RECENT ACTIVITY LOGIC

$recent_activities = [];
if ($conn) {
    $temp = [];

    // 1. Get Recent Tics
    $sql_t = "SELECT 'tic' as entry_type, Type, Type_Description, Intensity, Created_At as time FROM tic_log WHERE Patient_ID = ? ORDER BY Created_At DESC LIMIT 5";
    if ($stmt = $conn->prepare($sql_t)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $ticType = isset($r['Type']) ? $r['Type'] : 'Motor';
            $r['display_title'] = ($ticType === 'Vocal') ? "Vocal Tic Entry" : "Motor Tic Entry";
            $r['display_desc'] = $r['Type_Description'] . " (Intensity: " . $r['Intensity'] . "/10)";
            $r['icon'] = 'activity';
            $r['bg_class'] = 'bg-teal-50 text-[#005949]';
            $temp[] = $r;
        }
        $stmt->close();
    }

    // 2. Get Recent Emotional Entries
    $sql_e = "SELECT 'mood' as entry_type, Emotion, Stress, Anxiety, Occurrence as time FROM emotional_diary WHERE Patient_ID = ? ORDER BY Occurrence DESC LIMIT 5";
    if ($stmt = $conn->prepare($sql_e)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $r['display_title'] = "Emotional Diary Entry";
            $r['display_desc'] = "Emotion: " . $r['Emotion'] . " | Stress: " . $r['Stress'] . " | Anxiety: " . $r['Anxiety'];
            $r['icon'] = 'smile';
            $r['bg_class'] = 'bg-orange-50 text-orange-500';
            $temp[] = $r;
        }
        $stmt->close();
    }

    usort($temp, function ($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    $recent_activities = array_slice($temp, 0, 5);
}

// --- DYNAMIC PATTERN & RECOMMENDATION LOGIC ---
$pattern_title = "Daily Insight";
$pattern_msg = "Keep logging your tics and emotions to discover personalized patterns!";
$suggested_resource_url = "resourcehub_patient.php"; // Default link
$button_text = "View Hub";

if ($conn && $patient_id) {
    // 1. Check for Stress-Tic Correlation Pattern
    // Threshold: Stress > 7 and Tics logged on those same days
    $sql_pattern = "SELECT COUNT(*) as high_stress_days 
                    FROM emotional_diary 
                    WHERE Patient_ID = ? AND Stress >= 7 AND DATE(Occurrence) >= DATE_SUB(NOW(), INTERVAL 7 DAY)";

    $stmt_p = $conn->prepare($sql_pattern);
    $stmt_p->bind_param("i", $patient_id);
    $stmt_p->execute();
    $high_stress_count = $stmt_p->get_result()->fetch_assoc()['high_stress_days'];
    $stmt_p->close();

    if ($high_stress_count >= 2) {
        $pattern_title = "Pattern Detected";
        $pattern_msg = "Your tics often spike when stress levels are high. Try a relaxation technique.";

        // 2. Fetch a matching resource from the Hub
        // Look for resources in 'anxiety_management' or 'pmr_training' assigned to this patient
        $sql_rec = "SELECT rh.title, rh.media_url, rh.category_type 
                    FROM patient_resource_assignments pr 
                    JOIN resource_hub rh ON pr.resource_id = rh.id 
                    WHERE pr.patient_id = ? AND rh.category_type IN ('anxiety_management', 'pmr_training')
                    LIMIT 1";

        $stmt_r = $conn->prepare($sql_rec);
        $stmt_r->bind_param("i", $patient_id);
        $stmt_r->execute();
        $res_rec = $stmt_r->get_result()->fetch_assoc();

        if ($res_rec) {
            $button_text = "Try " . htmlspecialchars($res_rec['title']);
            $suggested_resource_url = htmlspecialchars($res_rec['media_url']);
        } else {
            // If no specific resource assigned, just point to the Anxiety category
            $button_text = "Relaxation Exercises";
            $suggested_resource_url = "resourcehub_patient.php?cat=anxiety_management";
        }
        $stmt_r->close();
    }
}


$page_title = 'Dashboard';
include '../../components/header_component.php';
include '../../includes/navbar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<main class="flex-1 w-full p-6 md:p-2 overflow-y-auto bg-[#E9F0E9]">
    <div class="p-6 md:p-8 space-y-6 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold text-[#005949]"><?php echo $greeting_text . ", " . htmlspecialchars($patient_name); ?></h2>
                <p class="text-gray-600 mt-1">Here is your daily overview.</p>
            </div>
            <a href="ticlog_motor.php" class="bg-[#005949] hover:bg-[#004539] text-white px-5 py-3 rounded-lg font-semibold shadow-sm transition-all flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Log New Tic
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[#005949] flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Tics Today</p>
                    <span class="text-4xl font-bold text-gray-800"><?php echo $today_tics; ?></span>
                </div>
                <div class="bg-[#E9F0E9] p-3 rounded-xl text-[#005949]"><i data-lucide="activity" class="w-8 h-8"></i></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 <?php echo explode(' ', $stress_color_class)[2]; ?> flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Avg Stress Today</p>
                    <div class="flex items-baseline mt-2">
                        <span class="text-4xl font-bold text-gray-800"><?php echo $avg_stress; ?></span>
                        <span class="text-sm text-gray-400 ml-1">/ 10</span>
                    </div>
                </div>
                <div class="p-3 rounded-xl <?php echo $stress_color_class; ?>"><i data-lucide="<?php echo $stress_icon; ?>" class="w-8 h-8"></i></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-400 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Medication</p>

                    <?php if ($med_count > 0): ?>
                        <h3 class="text-lg font-bold text-gray-800 mt-2">
                            <?php echo $med_name_display; ?>
                            <?php if ($has_more): ?>
                                <span class="text-sm font-normal text-gray-500">
                                    & <a href="medication_tracking.php" class="text-blue-600 hover:underline hover:text-blue-800">more</a>
                                </span>
                            <?php endif; ?>
                        </h3>

                        <p class="text-xs text-blue-600 font-bold mt-1">
                            <?php echo $med_time_display; ?>
                        </p>
                    <?php else: ?>
                        <h3 class="text-lg font-bold text-gray-800 mt-2">All caught up!</h3>
                        <p class="text-xs text-gray-400 mt-1">No pending meds</p>
                    <?php endif; ?>
                </div>

                <a href="medication_tracking.php" class="bg-blue-50 p-3 rounded-xl text-blue-500 hover:bg-blue-100 transition">
                    <i data-lucide="pill" class="w-8 h-8"></i>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Tic Frequency</h3>
                    <div class="flex items-center gap-1">
                        <button id="btnPrevTics" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
                        <button id="btnNextTics" class="p-1 hover:bg-gray-100 rounded text-gray-500 disabled:opacity-30"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
                    </div>
                </div>
                <div class="relative h-64 w-full"><canvas id="ticFrequencyChart"></canvas></div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Stress vs. Tic Intensity</h3>
                    <div class="flex items-center gap-1">
                        <button id="btnPrevStress" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
                        <button id="btnNextStress" class="p-1 hover:bg-gray-100 rounded text-gray-500 disabled:opacity-30"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
                    </div>
                </div>
                <div class="relative h-64 w-full"><canvas id="correlationChart"></canvas></div>
                <div class="mt-4 flex items-center justify-center gap-4 text-xs text-gray-500">
                    <div class="flex items-center"><span class="w-3 h-1 bg-orange-400 mr-2"></span>Stress</div>
                    <div class="flex items-center"><span class="w-3 h-3 bg-[#2dd4bf] mr-2 rounded-sm"></span>Avg Tic Intensity</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Affected Areas (Cumulative)</h3>
                <div class="relative h-60 w-full flex justify-center"><canvas id="musclePieChart"></canvas></div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Daily Rhythm (Today)</h3>
                <div class="relative h-60 w-full"><canvas id="hourlyAreaChart"></canvas></div>
                <div class="mt-2 text-xs text-gray-400 text-center">Shows tic activity by hour for today.</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Sleep, Anxiety & Tics</h3>
                    <div class="flex items-center gap-1">
                        <button id="btnPrevSleep" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
                        <button id="btnNextSleep" class="p-1 hover:bg-gray-100 rounded text-gray-500 disabled:opacity-30"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
                    </div>
                </div>
                <div class="relative h-60 w-full"><canvas id="sleepDualChart"></canvas></div>
                <div class="mt-2 text-xs text-gray-400 text-center">
                    <span class="text-orange-400">● Sleep</span> <span class="text-[#F282A9] ml-2">● Anxiety</span> <span class="text-[#005949] ml-2">-- Tics</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Recent Activity</h3>

                </div>
                <div class="divide-y divide-gray-50">
                    <?php if (count($recent_activities) > 0): ?>
                        <?php foreach ($recent_activities as $act): ?>
                            <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center <?php echo $act['bg_class']; ?>">
                                    <i data-lucide="<?php echo $act['icon']; ?>" class="w-5 h-5"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($act['display_title']); ?>
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        <?php echo htmlspecialchars($act['display_desc']); ?>
                                    </p>
                                </div>
                                <span class="text-xs text-gray-400 font-medium">
                                    <div class="date-display" data-timestamp="<?php echo strtotime($act['time']); ?>"></div>
                                    <div class="time-display" data-timestamp="<?php echo strtotime($act['time']); ?>"></div>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-400 text-sm">No recent activity found.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-[#005949] rounded-lg p-6 text-white flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white opacity-10 rounded-full"></div>
                <div class="bg-white/20 w-fit p-2 rounded-lg mb-4">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-yellow-300"></i>
                </div>
                <h3 class="text-lg font-bold mb-3 leading-tight"><?php echo $pattern_title; ?></h3>
                <p class="text-emerald-100 text-sm leading-relaxed mb-6 line-clamp-3">
                    <?php echo $pattern_msg; ?>
                </p>
                <button class="mt-6 w-full py-3 bg-white text-[#005949] rounded-lg font-bold text-sm hover:bg-emerald-50 transition">View Exercises</button>
            </div>
        </div>
    </div>
</main>

<script>
    // Pass initial data to external JS file
    window.homePatientData = {
        dates: <?php echo json_encode($dates); ?>,
        tics: <?php echo json_encode($tic_counts); ?>,
        stress: <?php echo json_encode($stress_levels); ?>,
        intensity: <?php echo json_encode($tic_intensities); ?>,
        sleep: <?php echo json_encode($sleep_quality); ?>,
        anxiety: <?php echo json_encode($anxiety_levels); ?>,
        muscleLabels: <?php echo json_encode($muscle_labels); ?>,
        muscleData: <?php echo json_encode($muscle_data); ?>,
        hourlyData: <?php echo json_encode(array_values($hourly_activity)); ?>
    };
</script>
<script src="../../js/patient/home_patient.js"></script>