<?php
/*
 * home_professional.php
 * * Doctor/Professional Dashboard
 * * Features: Filtered Patient List, Dynamic Messaging, and Optimized PDF Reporting
 */

session_start();

// --- CONFIG & DB CONNECTION ---
$config_path = '../../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    $conn = null;
}

date_default_timezone_set('Europe/Lisbon');

// --- SECURITY CHECK ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "Professional") {
    header("Location: ../auth/login.php");
    exit;
}

$doctor_id = $_SESSION["user_id"] ?? 0;

// =================================================================================
// 1. AJAX HANDLER (For Graphs and Modals)
// =================================================================================
if (isset($_GET['ajax_fetch']) && isset($_GET['patient_id'])) {
    header('Content-Type: application/json');
    $patient_id = intval($_GET['patient_id']);

    // --- HANDLE VIEW ALL REQUESTS ---
    if (isset($_GET['action'])) {
        $data = [];
        if ($_GET['action'] === 'fetch_all_tics') {
            $stmt = $conn->prepare("SELECT Created_At, Type, Type_Description, Intensity, Describe_Text FROM tic_log WHERE Patient_ID = ? ORDER BY Created_At DESC");
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['Formatted_Date'] = date('M d, Y H:i', strtotime($row['Created_At']));
                $data[] = $row;
            }
        } elseif ($_GET['action'] === 'fetch_all_emotions') {
            $stmt = $conn->prepare("SELECT Occurrence, Emotion, Stress, Anxiety, Sleep, Notes FROM emotional_diary WHERE Patient_ID = ? ORDER BY Occurrence DESC");
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['Formatted_Date'] = date('M d, Y H:i', strtotime($row['Occurrence']));
                $data[] = $row;
            }
        }
        echo json_encode(['data' => $data]);
        exit;
    }

    // --- HANDLE GRAPH DATA REQUESTS ---
    if (isset($_GET['offset'])) {
        $offset = intval($_GET['offset']);
        $graph_type = $_GET['graph'] ?? 'main';
        $today_str = date('Y-m-d');

        $start_timestamp = strtotime("-" . ($offset + 6) . " days");
        $end_timestamp   = strtotime("-" . $offset . " days");
        $start_date_db   = date('Y-m-d', $start_timestamp);
        $end_date_db     = date('Y-m-d', $end_timestamp);

        $labels = [];
        $range_text = date('M d', $start_timestamp) . " - " . date('M d', $end_timestamp);

        for ($i = 6; $i >= 0; $i--) {
            $ts = strtotime("-" . ($offset + $i) . " days");
            $db_date = date('Y-m-d', $ts);
            $day_name = date('D', $ts);
            $day_date = ($db_date === $today_str) ? "Today" : date('d M', $ts);
            $labels[] = [$day_name, $day_date];
        }

        $response = ['labels' => $labels, 'range' => $range_text, 'data' => []];

        if ($conn) {
            if ($graph_type === 'main') {
                $stress = [];
                $severity = [];
                for ($i = 6; $i >= 0; $i--) {
                    $d = date('Y-m-d', strtotime("-" . ($offset + $i) . " days"));
                    $stmt = $conn->prepare("SELECT MAX(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ? AND Type_Description != 'No Tics Today'");
                    $stmt->bind_param("is", $patient_id, $d);
                    $stmt->execute();
                    $severity[] = $stmt->get_result()->fetch_assoc()['i'] ?? 0;
                    $stmt = $conn->prepare("SELECT AVG(Stress) as s FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?");
                    $stmt->bind_param("is", $patient_id, $d);
                    $stmt->execute();
                    $stress[] = $stmt->get_result()->fetch_assoc()['s'] ?? 0;
                }
                $response['stress'] = $stress;
                $response['severity'] = $severity;
            } elseif ($graph_type === 'sleep') {
                $sleep = [];
                $frequency = [];
                for ($i = 6; $i >= 0; $i--) {
                    $d = date('Y-m-d', strtotime("-" . ($offset + $i) . " days"));
                    $stmt = $conn->prepare("SELECT AVG(Sleep) as s FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?");
                    $stmt->bind_param("is", $patient_id, $d);
                    $stmt->execute();
                    $sleep[] = round($stmt->get_result()->fetch_assoc()['s'] ?? 0, 1);
                    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ? AND Type_Description != 'No Tics Today'");
                    $stmt->bind_param("is", $patient_id, $d);
                    $stmt->execute();
                    $frequency[] = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
                }
                $response['data'] = ['sleep' => $sleep, 'frequency' => $frequency];
            } elseif ($graph_type === 'multi') {
                $stress = [];
                $anxiety = [];
                $sleep = [];
                $intensity = [];
                $frequency = [];
                for ($i = 6; $i >= 0; $i--) {
                    $d = date('Y-m-d', strtotime("-" . ($offset + $i) . " days"));
                    $stmt = $conn->prepare("SELECT AVG(Stress) as s, AVG(Anxiety) as a, AVG(Sleep) as sl FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?");
                    $stmt->bind_param("is", $patient_id, $d);
                    $stmt->execute();
                    $resE = $stmt->get_result()->fetch_assoc();
                    $stress[] = round($resE['s'] ?? 0, 1);
                    $anxiety[] = round($resE['a'] ?? 0, 1);
                    $sleep[] = round($resE['sl'] ?? 0, 1);
                    $stmt = $conn->prepare("SELECT COUNT(*) as c, MAX(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ? AND Type_Description != 'No Tics Today'");
                    $stmt->bind_param("is", $patient_id, $d);
                    $stmt->execute();
                    $resT = $stmt->get_result()->fetch_assoc();
                    $intensity[] = $resT['i'] ?? 0;
                    $frequency[] = (int)$resT['c'];
                }
                $response['data'] = ['stress' => $stress, 'anxiety' => $anxiety, 'sleep' => $sleep, 'intensity' => $intensity, 'frequency' => $frequency];
            } elseif ($graph_type === 'hourly') {
                $hourly = array_fill(0, 24, 0);
                $stmt = $conn->prepare("SELECT HOUR(Created_At) as h, COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) BETWEEN ? AND ? AND Type_Description != 'No Tics Today' GROUP BY h");
                $stmt->bind_param("iss", $patient_id, $start_date_db, $end_date_db);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $hourly[$row['h']] = $row['c'];
                }
                $response['data'] = $hourly;
            } elseif ($graph_type === 'moods') {
                $moods = [];
                $counts = [];
                $stmt = $conn->prepare("SELECT Emotion, COUNT(*) as c FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) BETWEEN ? AND ? GROUP BY Emotion ORDER BY c DESC LIMIT 5");
                $stmt->bind_param("iss", $patient_id, $start_date_db, $end_date_db);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $moods[] = ucfirst($row['Emotion']);
                    $counts[] = $row['c'];
                }
                $response['data'] = ['labels' => $moods, 'values' => $counts];
            } elseif ($graph_type === 'muscles') {
                $muscles = [];
                $counts = [];
                $stmt = $conn->prepare("SELECT Muscle_Group, COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) BETWEEN ? AND ? AND Muscle_Group IS NOT NULL AND Muscle_Group != '' GROUP BY Muscle_Group ORDER BY c DESC LIMIT 6");
                $stmt->bind_param("iss", $patient_id, $start_date_db, $end_date_db);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $muscles[] = $row['Muscle_Group'];
                    $counts[] = $row['c'];
                }
                $response['data'] = ['labels' => $muscles, 'values' => $counts];
            }
        }
        echo json_encode($response);
        exit;
    }
}

// --- 1. FETCH FILTERED PATIENT LIST ---
$patients = [];
if ($conn) {
    // UPDATED: Only fetch patients linked to this professional
    $stmt = $conn->prepare("
        SELECT up.User_ID, up.First_Name, up.Last_Name, l.Link_ID 
        FROM user_profile up
        JOIN patient_professional_link l ON up.User_ID = l.Patient_ID
        WHERE l.Professional_ID = ? AND up.Role = 'Patient'
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $patients[] = [
            'id' => $row['User_ID'],
            'name' => $row['First_Name'] . ' ' . $row['Last_Name'],
            'link_id' => $row['Link_ID']
        ];
    }
    $stmt->close();
}

// --- 2. HANDLE PATIENT SELECTION ---
$selected_patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : ($patients[0]['id'] ?? 0);
$selected_patient_name = "Unknown";
$selected_link_id = 0;

foreach ($patients as $p) {
    if ($p['id'] == $selected_patient_id) {
        $selected_patient_name = $p['name'];
        $selected_link_id = $p['link_id'];
        break;
    }
}

// --- 3. CALCULATE SUMMARY STATS (Dynamic) ---
$total_tics_30d = 0;
$total_tics_prev_30d = 0;
$avg_stress_30d = 0;
$adherence_percentage = 0;

if ($conn && $selected_patient_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND Created_At >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND Type_Description != 'No Tics Today'");
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $total_tics_30d = $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND Created_At >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND Created_At < DATE_SUB(NOW(), INTERVAL 30 DAY) AND Type_Description != 'No Tics Today'");
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $total_tics_prev_30d = $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT AVG(Stress) as s FROM emotional_diary WHERE Patient_ID = ? AND Occurrence >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $avg_stress_30d = $res['s'] ? round($res['s'], 1) : 0;
    $stmt->close();

    $sql_adherence = "
        SELECT COUNT(DISTINCT log_date) as active_days FROM (
            SELECT DATE(Created_At) as log_date FROM tic_log WHERE Patient_ID = ? AND Created_At >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            UNION
            SELECT DATE(Occurrence) as log_date FROM emotional_diary WHERE Patient_ID = ? AND Occurrence >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ) as combined_logs
    ";
    $stmt = $conn->prepare($sql_adherence);
    $stmt->bind_param("ii", $selected_patient_id, $selected_patient_id);
    $stmt->execute();
    $active_days = $stmt->get_result()->fetch_assoc()['active_days'];
    $adherence_percentage = round(($active_days / 30) * 100);
    $stmt->close();
}

$trend_percent = 0;
$trend_direction = 'neutral';
if ($total_tics_prev_30d > 0) {
    $diff = $total_tics_30d - $total_tics_prev_30d;
    $trend_percent = round(abs($diff / $total_tics_prev_30d) * 100);
    if ($diff > 0) $trend_direction = 'up';
    elseif ($diff < 0) $trend_direction = 'down';
} elseif ($total_tics_30d > 0) {
    $trend_percent = 100;
    $trend_direction = 'up';
}

// Graph 1 Data
$dates = [];
$tic_severity = [];
$stress_levels = [];
$today_str = date('Y-m-d');
if ($conn && $selected_patient_id) {
    for ($i = 6; $i >= 0; $i--) {
        $timestamp = strtotime("-$i days");
        $db_date = date('Y-m-d', $timestamp);
        $day_name = date('D', $timestamp);
        $day_date = ($db_date === $today_str) ? "Today" : date('d M', $timestamp);
        $dates[] = [$day_name, $day_date];

        $stmt = $conn->prepare("SELECT MAX(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ? AND Type_Description != 'No Tics Today'");
        $stmt->bind_param("is", $selected_patient_id, $db_date);
        $stmt->execute();
        $tic_severity[] = $stmt->get_result()->fetch_assoc()['i'] ?? 0;
        $stmt->close();

        $stmt = $conn->prepare("SELECT AVG(Stress) as s FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?");
        $stmt->bind_param("is", $selected_patient_id, $db_date);
        $stmt->execute();
        $stress_levels[] = $stmt->get_result()->fetch_assoc()['s'] ?? 0;
        $stmt->close();
    }
}

// Pie Chart Data
$motor_count = 0;
$vocal_count = 0;
if ($conn && $selected_patient_id) {
    $stmt = $conn->prepare("SELECT Type, COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND Type_Description != 'No Tics Today' GROUP BY Type");
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        if (stripos($row['Type'], 'Motor') !== false) $motor_count += $row['c'];
        else $vocal_count += $row['c'];
    }
    $stmt->close();
}

// Recent Tables Data
$recent_logs = [];
$recent_emotions = [];
if ($conn && $selected_patient_id) {
    $stmt = $conn->prepare("SELECT Created_At, Type, Type_Description, Intensity, Describe_Text FROM tic_log WHERE Patient_ID = ? ORDER BY Created_At DESC LIMIT 5");
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $recent_logs[] = $r;
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT Occurrence, Emotion, Stress, Anxiety, Sleep, Notes FROM emotional_diary WHERE Patient_ID = ? ORDER BY Occurrence DESC LIMIT 5");
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $recent_emotions[] = $r;
    }
    $stmt->close();
}

$page_title = "Professional Dashboard";
include '../../components/header_component.php';
include '../../includes/navbar.php';
?>

<style>
    /* PDF OPTIMIZATION STYLES
       Prevents overlapping and ensures charts don't break across pages 
    */
    .pdf-mode {
        width: 1024px !important;
        background: #E9F0E9 !important;
        padding: 40px !important;
    }

    .pdf-mode .bg-white {
        border: 1px solid #e5e7eb !important;
        box-shadow: none !important;
    }

    .pdf-card-fix {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .html2pdf__page-break {
        display: block;
        height: 20px;
        page-break-before: always;
    }

    .pdf-export-container {
        width: 790px !important;
        background: #E9F0E9 !important;
        padding: 20px !important;
        color: #1f2937 !important;
        display: block !important;
    }

    .pdf-export-container .grid {
        display: block !important;
        
        width: 100% !important;
    }

    .pdf-export-container .bg-white {
        margin-bottom: 15px !important;
        
        padding: 15px !important;
        border: 1px solid #ddd !important;
        height: auto !important;
        overflow: visible !important;
        
        display: block !important;
        page-break-inside: avoid !important;
       
    }

    
    .pdf-export-container .doughnut-wrapper {
        max-width: 250px !important;
        margin: 0 auto !important;
    }

    .pdf-export-container table {
        width: 100% !important;
        border-collapse: collapse !important;
        page-break-inside: auto !important;
    }

    .pdf-export-container tr {
        page-break-inside: avoid !important;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<main class="flex-1 w-full bg-[#E9F0E9] h-screen overflow-hidden flex flex-col">

    <div class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between shrink-0 shadow-sm z-20">
        <div class="flex items-center gap-4">
            <div class="bg-teal-100 p-2 rounded-lg text-[#005949]"><i data-lucide="users" class="w-5 h-5"></i></div>
            <div>
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide">Viewing Patient</h2>
                <form method="GET" action="" id="patientForm" class="relative">
                    <select name="patient_id" onchange="document.getElementById('patientForm').submit()" class="appearance-none bg-transparent text-xl font-bold text-gray-800 pr-8 cursor-pointer focus:outline-none hover:text-[#005949] transition-colors">
                        <?php foreach ($patients as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $selected_patient_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 absolute right-0 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </form>
            </div>
        </div>
        <div class="flex gap-3">
            <button onclick="exportPDF()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i> Export PDF
            </button>

            <?php if ($selected_link_id): ?>
                <a href="chat_professional.php?link_id=<?php echo $selected_link_id; ?>" class="px-4 py-2 text-sm font-medium text-white bg-[#005949] rounded-lg hover:bg-[#004539] shadow-sm flex items-center gap-2">
                    <i data-lucide="message-square" class="w-4 h-4"></i> Message
                </a>
            <?php else: ?>
                <button disabled class="px-4 py-2 text-sm font-medium text-white bg-gray-400 rounded-lg shadow-sm flex items-center gap-2 cursor-not-allowed">
                    <i data-lucide="message-square" class="w-4 h-4"></i> Message
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-8">

        <div id="report-container" class="max-w-7xl mx-auto space-y-6">

            <div id="pdf-header" class="hidden mb-6 border-b border-gray-300 pb-4">
                <h1 class="text-3xl font-bold text-[#005949]">Patient Analytics Report</h1>
                <div class="flex justify-between mt-2 text-gray-600">
                    <p><strong>Patient:</strong> <?php echo htmlspecialchars($selected_patient_name); ?></p>
                    <p><strong>Date Generated:</strong> <?php echo date('F j, Y'); ?></p>
                </div>
            </div>

            <div class="pdf-card-fix space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Tics (30 Days)</p>
                        <div class="mt-2 flex items-end gap-2">
                            <span class="text-3xl font-bold text-gray-800"><?php echo $total_tics_30d; ?></span>
                            <?php if ($trend_direction === 'up'): ?>
                                <span class="text-xs font-bold text-red-500 flex items-center mb-1 bg-red-50 px-1.5 py-0.5 rounded">
                                    <i data-lucide="arrow-up" class="w-3 h-3 mr-1"></i> <?php echo $trend_percent; ?>%
                                </span>
                            <?php elseif ($trend_direction === 'down'): ?>
                                <span class="text-xs font-bold text-green-600 flex items-center mb-1 bg-green-50 px-1.5 py-0.5 rounded">
                                    <i data-lucide="arrow-down" class="w-3 h-3 mr-1"></i> <?php echo $trend_percent; ?>%
                                </span>
                            <?php else: ?>
                                <span class="text-xs font-bold text-gray-400 mb-1 px-1.5 py-0.5">— 0%</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">*compared to previous 30 days</p>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Avg Stress Score</p>
                        <div class="mt-2 flex items-end gap-2"><span class="text-3xl font-bold text-gray-800"><?php echo $avg_stress_30d; ?></span><span class="text-xs text-gray-400 mb-1">/ 10</span></div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Dominant Type</p>
                        <div class="mt-2"><span class="text-2xl font-bold text-[#005949]"><?php echo ($motor_count > $vocal_count) ? 'Motor' : 'Vocal'; ?></span></div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Adherence</p>
                        <div class="mt-2 flex items-end gap-2">
                            <span class="text-3xl font-bold <?php echo ($adherence_percentage >= 75) ? 'text-green-600' : (($adherence_percentage >= 50) ? 'text-yellow-600' : 'text-red-500'); ?>">
                                <?php echo $adherence_percentage; ?>%
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">*Consistency (Log Freq)</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800">Trigger Analysis: Stress vs. Severity</h3>
                            <div class="flex items-center gap-2 print:hidden">
                                <button onclick="changeOffset('main', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
                                <button onclick="changeOffset('main', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
                            </div>
                        </div>
                        <div class="h-72 w-full"><canvas id="doctorComboChart"></canvas></div>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col">
                        <h3 class="font-bold text-gray-800 mb-4">Tic Classification</h3>
                        <div class="flex-1 flex items-center justify-center relative">
                            <canvas id="typeDoughnutChart"></canvas>
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="text-center"><span class="block text-2xl font-bold text-gray-800"><?php echo $motor_count + $vocal_count; ?></span><span class="text-xs text-gray-400">Total</span></div>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs">
                            <div class="bg-teal-50 p-2 rounded text-[#005949] font-semibold">Motor: <?php echo $motor_count; ?></div>
                            <div class="bg-[#FDE8EF] p-2 rounded text-[#F282A9] font-semibold">Vocal: <?php echo $vocal_count; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="html2pdf__page-break"></div>

            <div class="pdf-card-fix space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm lg:col-span-2">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="font-bold text-gray-800">Symptom Cluster Analysis</h3>
                                <p class="text-xs text-gray-400">Daily Breakdown</p>
                            </div>
                            <div class="flex items-center gap-2 print:hidden">
                                <button onclick="changeOffset('multi', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                                <button onclick="changeOffset('multi', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                            </div>
                        </div>
                        <div class="h-64 relative flex justify-center"><canvas id="chart-multi"></canvas></div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="font-bold text-gray-800">Sleep & Frequency</h3>
                                <p class="text-xs text-gray-400">Sleep Score vs Count</p>
                            </div>
                            <div class="flex items-center gap-2 print:hidden">
                                <button onclick="changeOffset('sleep', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                                <button onclick="changeOffset('sleep', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                            </div>
                        </div>
                        <div class="h-48 relative"><canvas id="chart-sleep"></canvas></div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="font-bold text-gray-800">Hourly Rhythm of the week</h3>
                                <p class="text-xs text-gray-400">Activity by Hour</p>
                            </div>
                            <div class="flex items-center gap-2 print:hidden">
                                <button onclick="changeOffset('hourly', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                                <span id="range-hourly" class="text-xs font-medium text-gray-400 w-24 text-center">...</span>
                                <button onclick="changeOffset('hourly', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                            </div>
                        </div>
                        <div class="h-48 relative"><canvas id="chart-hourly"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="html2pdf__page-break"></div>

            <div class="pdf-card-fix space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="font-bold text-gray-800">Mood Profile</h3>
                                <p class="text-xs text-gray-400">Top Reported in the week</p>
                            </div>
                            <div class="flex items-center gap-2 print:hidden">
                                <button onclick="changeOffset('moods', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                                <span id="range-moods" class="text-xs font-medium text-gray-400 w-24 text-center">...</span>
                                <button onclick="changeOffset('moods', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                            </div>
                        </div>
                        <div class="h-48 relative"><canvas id="chart-moods"></canvas></div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="font-bold text-gray-800">Somatic Burden</h3>
                                <p class="text-xs text-gray-400">Affected Muscles</p>
                            </div>
                            <div class="flex items-center gap-2 print:hidden">
                                <button onclick="changeOffset('muscles', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                                <span id="range-muscles" class="text-xs font-medium text-gray-400 w-24 text-center">...</span>
                                <button onclick="changeOffset('muscles', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                            </div>
                        </div>
                        <div class="h-48 relative flex justify-center"><canvas id="chart-muscles"></canvas></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                            <h3 class="font-bold text-gray-800">Recent Tic Logs</h3>
                            <button onclick="openModal('modal-tics')" class="text-xs font-semibold text-[#005949] hover:underline print:hidden">View All</button>
                        </div>
                        <div class="overflow-x-auto flex-1">
                            <table class="w-full text-sm text-left table-fixed">
                                <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 w-[25%]">Date</th>
                                        <th class="px-6 py-3 w-[45%]">Tic</th>
                                        <th class="px-6 py-3 w-[30%] text-center">Intensity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php if (count($recent_logs) > 0): ?>
                                        <?php foreach ($recent_logs as $log): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-3 text-gray-600 whitespace-nowrap"><?php echo date('M d, H:i', strtotime($log['Created_At'])); ?></td>
                                                <td class="px-6 py-3">
                                                    <div class="font-medium text-gray-800 truncate" title="<?php echo htmlspecialchars($log['Type_Description']); ?>"><?php echo htmlspecialchars($log['Type_Description']); ?></div>
                                                    <div class="text-xs text-gray-400 truncate"><?php echo htmlspecialchars($log['Type']); ?></div>
                                                </td>
                                                <td class="px-6 py-3 text-center"><span class="inline-block px-2 py-0.5 rounded text-xs font-bold <?php echo ($log['Intensity'] > 7) ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'; ?>"><?php echo $log['Intensity']; ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">No logs found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                            <h3 class="font-bold text-gray-800">Recent Emotions</h3>
                            <button onclick="openModal('modal-emotions')" class="text-xs font-semibold text-[#005949] hover:underline print:hidden">View All</button>
                        </div>
                        <div class="overflow-x-auto flex-1">
                            <table class="w-full text-sm text-left table-fixed">
                                <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 w-[25%]">Date</th>
                                        <th class="px-6 py-3 w-[45%]">Emotion</th>
                                        <th class="px-6 py-3 w-[30%] text-center">Levels (S/A/Sl)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php if (count($recent_emotions) > 0): ?>
                                        <?php foreach ($recent_emotions as $emo): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-3 text-gray-600 whitespace-nowrap"><?php echo date('M d, H:i', strtotime($emo['Occurrence'])); ?></td>
                                                <td class="px-6 py-3">
                                                    <div class="truncate max-w-full"><span class="px-2 py-1 rounded text-xs font-semibold bg-[#F7EBF0] text-[#F282A9]"><?php echo ucfirst($emo['Emotion']); ?></span></div>
                                                </td>
                                                <td class="px-6 py-3 text-xs text-gray-500 text-center"><span class="font-bold text-orange-500" title="Stress">St:<?php echo $emo['Stress']; ?></span> • <span class="font-bold text-blue-500" title="Anxiety">An:<?php echo $emo['Anxiety']; ?></span> • <span class="font-bold text-[#F282A9]" title="Sleep">Sl:<?php echo $emo['Sleep']; ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">No entries found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<div id="modal-tics" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl h-[80vh] flex flex-col">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">All Tic Logs</h3><button onclick="closeModal('modal-tics')" class="text-gray-500 hover:text-red-500"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-medium">
                    <tr>
                        <th class="p-3">Date</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Description</th>
                        <th class="p-3">Intensity</th>
                        <th class="p-3">Notes</th>
                    </tr>
                </thead>
                <tbody id="table-body-tics" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="5" class="p-4 text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="modal-emotions" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl h-[80vh] flex flex-col">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">All Emotional Entries</h3><button onclick="closeModal('modal-emotions')" class="text-gray-500 hover:text-red-500"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-medium">
                    <tr>
                        <th class="p-3">Date</th>
                        <th class="p-3">Emotion</th>
                        <th class="p-3">Stress</th>
                        <th class="p-3">Anxiety</th>
                        <th class="p-3">Sleep</th>
                        <th class="p-3">Notes</th>
                    </tr>
                </thead>
                <tbody id="table-body-emotions" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="6" class="p-4 text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Pass data to external JS file
    window.homeProfessionalData = {
        selectedPatientId: <?php echo $selected_patient_id; ?>,
        patientName: '<?php echo htmlspecialchars($selected_patient_name); ?>',
        labels: <?php echo json_encode($dates); ?>,
        severity: <?php echo json_encode($tic_severity); ?>,
        stress: <?php echo json_encode($stress_levels); ?>,
        motorCount: <?php echo $motor_count; ?>,
        vocalCount: <?php echo $vocal_count; ?>
    };
</script>
<script src="../../js/professional/home_professional.js"></script>