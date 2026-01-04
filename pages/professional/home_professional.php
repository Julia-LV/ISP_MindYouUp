<?php
/*
 * home_professional.php
 * * Doctor/Professional Dashboard
 * * Features: Independent Analytics Modules with Time-Travel Navigation
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
    // header("Location: ../auth/login.php"); exit;
}

$doctor_id = $_SESSION["user_id"] ?? 999; 

// =================================================================================
// 1. AJAX HANDLER
// =================================================================================
if (isset($_GET['ajax_fetch']) && isset($_GET['offset']) && isset($_GET['patient_id'])) {
    header('Content-Type: application/json');
    
    $offset = intval($_GET['offset']); 
    $patient_id = intval($_GET['patient_id']);
    $graph_type = $_GET['graph'] ?? 'main'; 
    $today_str = date('Y-m-d');
    
    // Calculate Date Range
    $start_timestamp = strtotime("-" . ($offset + 6) . " days");
    $end_timestamp   = strtotime("-" . $offset . " days");
    $start_date_db   = date('Y-m-d', $start_timestamp);
    $end_date_db     = date('Y-m-d', $end_timestamp);
    
    // Base Labels (Dates)
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
        // --- A. MAIN GRAPH (Stress vs Severity) ---
        if ($graph_type === 'main') {
            $stress = []; $severity = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-" . ($offset + $i) . " days"));
                
                $stmt = $conn->prepare("SELECT MAX(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ?");
                $stmt->bind_param("is", $patient_id, $d); $stmt->execute();
                $severity[] = $stmt->get_result()->fetch_assoc()['i'] ?? 0;
                
                $stmt = $conn->prepare("SELECT AVG(Stress) as s FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?");
                $stmt->bind_param("is", $patient_id, $d); $stmt->execute();
                $stress[] = $stmt->get_result()->fetch_assoc()['s'] ?? 0;
            }
            $response['stress'] = $stress;
            $response['severity'] = $severity;
        }

        // --- B. SLEEP vs TIC FREQUENCY (Single Axis) ---
        elseif ($graph_type === 'sleep') {
            $sleep = []; $frequency = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-" . ($offset + $i) . " days"));
                
                $stmt = $conn->prepare("SELECT AVG(Sleep) as s FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?");
                $stmt->bind_param("is", $patient_id, $d); $stmt->execute();
                $sleep[] = round($stmt->get_result()->fetch_assoc()['s'] ?? 0, 1);
                
                $stmt = $conn->prepare("SELECT COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ?");
                $stmt->bind_param("is", $patient_id, $d); $stmt->execute();
                $frequency[] = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
            }
            $response['data'] = ['sleep' => $sleep, 'frequency' => $frequency];
        }

        // --- C. MULTI-METRIC GROUPED COLUMN ---
        elseif ($graph_type === 'multi') {
            $stress = []; $anxiety = []; $sleep = []; $intensity = []; $frequency = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-" . ($offset + $i) . " days"));
                
                // Emotional
                $stmt = $conn->prepare("SELECT AVG(Stress) as s, AVG(Anxiety) as a, AVG(Sleep) as sl FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?");
                $stmt->bind_param("is", $patient_id, $d); $stmt->execute();
                $resE = $stmt->get_result()->fetch_assoc();
                $stress[] = round($resE['s'] ?? 0, 1);
                $anxiety[] = round($resE['a'] ?? 0, 1);
                $sleep[] = round($resE['sl'] ?? 0, 1);

                // Tics
                $stmt = $conn->prepare("SELECT COUNT(*) as c, MAX(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ?");
                $stmt->bind_param("is", $patient_id, $d); $stmt->execute();
                $resT = $stmt->get_result()->fetch_assoc();
                $intensity[] = $resT['i'] ?? 0;
                $frequency[] = (int)$resT['c']; 
            }

            $response['data'] = [
                'stress' => $stress,
                'anxiety' => $anxiety,
                'sleep' => $sleep,
                'intensity' => $intensity,
                'frequency' => $frequency
            ];
        }

        // --- D. 24-HOUR RHYTHM (Area) ---
        elseif ($graph_type === 'hourly') {
            $hourly = array_fill(0, 24, 0);
            $stmt = $conn->prepare("SELECT HOUR(Created_At) as h, COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) BETWEEN ? AND ? GROUP BY h");
            $stmt->bind_param("iss", $patient_id, $start_date_db, $end_date_db);
            $stmt->execute();
            $res = $stmt->get_result();
            while($row = $res->fetch_assoc()) {
                $hourly[$row['h']] = $row['c'];
            }
            $response['data'] = $hourly;
        }

        // --- E. MOODS (Pie Chart) ---
        elseif ($graph_type === 'moods') {
            $moods = []; $counts = [];
            $stmt = $conn->prepare("SELECT Emotion, COUNT(*) as c FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) BETWEEN ? AND ? GROUP BY Emotion ORDER BY c DESC LIMIT 5");
            $stmt->bind_param("iss", $patient_id, $start_date_db, $end_date_db);
            $stmt->execute();
            $res = $stmt->get_result();
            while($row = $res->fetch_assoc()) {
                $moods[] = ucfirst($row['Emotion']);
                $counts[] = $row['c'];
            }
            $response['data'] = ['labels' => $moods, 'values' => $counts];
        }

        // --- F. SOMATIC BURDEN ---
        elseif ($graph_type === 'muscles') {
            $muscles = []; $counts = [];
            $stmt = $conn->prepare("SELECT Muscle_Group, COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) BETWEEN ? AND ? AND Muscle_Group IS NOT NULL AND Muscle_Group != '' GROUP BY Muscle_Group ORDER BY c DESC LIMIT 6");
            $stmt->bind_param("iss", $patient_id, $start_date_db, $end_date_db);
            $stmt->execute();
            $res = $stmt->get_result();
            while($row = $res->fetch_assoc()) {
                $muscles[] = $row['Muscle_Group'];
                $counts[] = $row['c'];
            }
            $response['data'] = ['labels' => $muscles, 'values' => $counts];
        }
    }

    echo json_encode($response);
    exit;
} 

// --- 1. FETCH PATIENT LIST ---
$patients = [];
if ($conn) {
    $sql = "SELECT User_ID, First_Name, Last_Name FROM user_profile WHERE Role = 'Patient'"; 
    $result = $conn->query($sql);
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $patients[] = [
                'id' => $row['User_ID'],
                'name' => $row['First_Name'] . ' ' . $row['Last_Name']
            ];
        }
    }
} else {
    // Mock
    $patients = [ ['id' => 1, 'name' => 'Priya Sharma'], ['id' => 2, 'name' => 'Rahul Verma'] ];
}

// --- 2. HANDLE PATIENT SELECTION ---
$selected_patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : ($patients[0]['id'] ?? 0);
$selected_patient_name = "Unknown";
foreach ($patients as $p) {
    if ($p['id'] == $selected_patient_id) { $selected_patient_name = $p['name']; break; }
}

// --- 3. INITIAL ANALYTICS ---
$total_tics_30d = 0; $avg_stress_30d = 0;
if ($conn && $selected_patient_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM tic_log WHERE Patient_ID = ? AND Created_At >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $total_tics_30d = $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT AVG(Stress) as s FROM emotional_diary WHERE Patient_ID = ? AND Occurrence >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $avg_stress_30d = $res['s'] ? round($res['s'], 1) : 0;
    $stmt->close();
}

// Graph 1: Dates & Stress/Severity
$dates = []; $tic_severity = []; $stress_levels = [];
$today_str = date('Y-m-d');
if ($conn && $selected_patient_id) {
    for ($i = 6; $i >= 0; $i--) {
        $timestamp = strtotime("-$i days");
        $db_date = date('Y-m-d', $timestamp);
        $day_name = date('D', $timestamp); 
        $day_date = ($db_date === $today_str) ? "Today" : date('d M', $timestamp);
        $dates[] = [$day_name, $day_date];

        $stmt = $conn->prepare("SELECT MAX(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ?");
        $stmt->bind_param("is", $selected_patient_id, $db_date); $stmt->execute();
        $tic_severity[] = $stmt->get_result()->fetch_assoc()['i'] ?? 0; $stmt->close();

        $stmt = $conn->prepare("SELECT AVG(Stress) as s FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?");
        $stmt->bind_param("is", $selected_patient_id, $db_date); $stmt->execute();
        $stress_levels[] = $stmt->get_result()->fetch_assoc()['s'] ?? 0; $stmt->close();
    }
}

// Graph 2: Pie
$motor_count = 0; $vocal_count = 0;
if ($conn && $selected_patient_id) {
    $stmt = $conn->prepare("SELECT Type, COUNT(*) as c FROM tic_log WHERE Patient_ID = ? GROUP BY Type");
    $stmt->bind_param("i", $selected_patient_id); $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        if (stripos($row['Type'], 'Motor') !== false) $motor_count += $row['c']; else $vocal_count += $row['c'];
    }
    $stmt->close();
}

// Recent Logs
$recent_logs = [];
if ($conn && $selected_patient_id) {
    $sql = "SELECT Created_At, Type, Type_Description, Intensity, Describe_Text, Pain_Level FROM tic_log WHERE Patient_ID = ? ORDER BY Created_At DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_patient_id); $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) { $recent_logs[] = $row; }
    $stmt->close();
}

$page_title = "Professional Dashboard";
include '../../components/header_component.php'; 
include '../../includes/navbar.php'; 
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<main class="flex-1 w-full bg-gray-50 h-screen overflow-hidden flex flex-col">

    <div class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between shrink-0 shadow-sm z-20">
        <div class="flex items-center gap-4">
            <div class="bg-teal-100 p-2 rounded-lg text-[#005949]"><i data-lucide="users" class="w-5 h-5"></i></div>
            <div>
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide">Viewing Patient</h2>
                <form method="GET" action="" id="patientForm" class="relative">
                    <select name="patient_id" onchange="document.getElementById('patientForm').submit()" 
                            class="appearance-none bg-transparent text-xl font-bold text-gray-800 pr-8 cursor-pointer focus:outline-none hover:text-[#005949] transition-colors">
                        <?php foreach($patients as $p): ?>
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
            <button class="px-4 py-2 text-sm font-medium text-white bg-[#005949] rounded-lg hover:bg-[#004539] shadow-sm flex items-center gap-2">
                <i data-lucide="message-square" class="w-4 h-4"></i> Message
            </button>
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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Tics (30 Days)</p>
                    <div class="mt-2 flex items-end gap-2"><span class="text-3xl font-bold text-gray-800"><?php echo $total_tics_30d; ?></span></div>
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
                    <div class="mt-2"><span class="text-2xl font-bold text-blue-600">92%</span></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800">Trigger Analysis: Stress vs. Severity</h3>
                        <div class="flex items-center gap-2">
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
                        <div class="bg-blue-50 p-2 rounded text-blue-600 font-semibold">Vocal: <?php echo $vocal_count; ?></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 page-break-inside-avoid">
                
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-bold text-gray-800">Sleep & Frequency</h3>
                            <p class="text-xs text-gray-400">Sleep Score vs Count</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="changeOffset('sleep', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                            <button onclick="changeOffset('sleep', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    <div class="h-48 relative"><canvas id="chart-sleep"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm lg:col-span-2">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-bold text-gray-800">Symptom Cluster Analysis</h3>
                            <p class="text-xs text-gray-400">Daily Breakdown (Auto-Scaling)</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="changeOffset('multi', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                            <button onclick="changeOffset('multi', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    <div class="h-48 relative flex justify-center"><canvas id="chart-multi"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-bold text-gray-800">24-Hour Rhythm</h3>
                            <p class="text-xs text-gray-400">Activity by Hour</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="changeOffset('hourly', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                            <span id="range-hourly" class="text-xs font-medium text-gray-400 w-24 text-center">...</span>
                            <button onclick="changeOffset('hourly', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    <div class="h-48 relative"><canvas id="chart-hourly"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-bold text-gray-800">Mood Profile</h3>
                            <p class="text-xs text-gray-400">Reported Emotions (Top 5)</p>
                        </div>
                        <div class="flex items-center gap-2">
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
                            <p class="text-xs text-gray-400">Affected Muscle Groups</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="changeOffset('muscles', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                            <span id="range-muscles" class="text-xs font-medium text-gray-400 w-24 text-center">...</span>
                            <button onclick="changeOffset('muscles', -7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    <div class="h-48 relative flex justify-center"><canvas id="chart-muscles"></canvas></div>
                </div>

            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-800">Recent Tic Logs</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3">Date/Time</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Specific Tic</th>
                                <th class="px-6 py-3 text-center">Intensity</th>
                                <th class="px-6 py-3">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (count($recent_logs) > 0): ?>
                                <?php foreach($recent_logs as $log): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap"><?php echo date('M d, H:i', strtotime($log['Created_At'])); ?></td>
                                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold <?php echo (strpos($log['Type'], 'Motor') !== false) ? 'bg-teal-100 text-[#005949]' : 'bg-blue-100 text-blue-600'; ?>"><?php echo htmlspecialchars($log['Type']); ?></span></td>
                                        <td class="px-6 py-3 font-medium text-gray-800"><?php echo htmlspecialchars($log['Type_Description']); ?></td>
                                        <td class="px-6 py-3 text-center"><div class="inline-block px-2 py-0.5 rounded text-xs font-bold <?php echo ($log['Intensity'] > 7) ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'; ?>"><?php echo $log['Intensity']; ?>/10</div></td>
                                        <td class="px-6 py-3 text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($log['Describe_Text']); ?>"><?php echo htmlspecialchars($log['Describe_Text'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No logs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    lucide.createIcons();

    // Chart Data
    const initialLabels = <?php echo json_encode($dates); ?>;
    const initialSeverity = <?php echo json_encode($tic_severity); ?>;
    const initialStress = <?php echo json_encode($stress_levels); ?>;
    const motorCount = <?php echo $motor_count; ?>;
    const vocalCount = <?php echo $vocal_count; ?>;
    const selectedPatientId = <?php echo $selected_patient_id; ?>;

    // --- EXISTING CHARTS ---
    const ctxCombo = document.getElementById('doctorComboChart').getContext('2d');
    const comboChart = new Chart(ctxCombo, {
        type: 'bar',
        data: {
            labels: initialLabels,
            datasets: [
                { type: 'line', label: 'Avg Stress', data: initialStress, borderColor: '#fb923c', borderWidth: 2, borderDash: [5, 5], pointRadius: 4, tension: 0.3, yAxisID: 'y' },
                { type: 'bar', label: 'Max Intensity', data: initialSeverity, backgroundColor: '#005949', borderRadius: 4, barThickness: 24, yAxisID: 'y' }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { min: 0, max: 10, grid: { borderDash: [4, 4] } } } }
    });

    const ctxDoughnut = document.getElementById('typeDoughnutChart').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Motor', 'Vocal'],
            datasets: [{ data: [motorCount, vocalCount], backgroundColor: ['#005949', '#3b82f6'], borderWidth: 0, hoverOffset: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '75%' }
    });

    // --- NEW CHARTS INSTANTIATION ---
    
    // 1. SLEEP
    const chartSleep = new Chart(document.getElementById('chart-sleep'), {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, suggestedMax: 10 } }
        }
    });

    // 2. MULTI-METRIC
    const chartMulti = new Chart(document.getElementById('chart-multi'), {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: {size: 10} } } }
        }
    });

    // 3. HOURLY
    const chartHourly = new Chart(document.getElementById('chart-hourly'), {
        type: 'line',
        data: { labels: [...Array(24).keys()].map(h => h + ":00"), datasets: [] },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { x: { grid: {display: false} }, y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });

    // 4. MOODS (PIE)
    const chartMoods = new Chart(document.getElementById('chart-moods'), {
        type: 'pie',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: {size: 11} } } }
        }
    });

    // 5. MUSCLES
    const chartMuscles = new Chart(document.getElementById('chart-muscles'), {
        type: 'polarArea',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { r: { ticks: { display: false } } },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }
        }
    });

    // --- NAVIGATION LOGIC ---
    const offsets = { main: 0, sleep: 0, multi: 0, hourly: 0, moods: 0, muscles: 0 };

    async function loadGraph(type) {
        const offset = offsets[type];
        const rangeEl = document.getElementById(`range-${type}`);
        if(rangeEl) rangeEl.textContent = "...";

        try {
            const res = await fetch(`?ajax_fetch=1&graph=${type}&offset=${offset}&patient_id=${selectedPatientId}`);
            const data = await res.json();
            
            if(rangeEl && data.range) rangeEl.textContent = data.range;

            if (type === 'main') {
                comboChart.data.labels = data.labels;
                comboChart.data.datasets[0].data = data.stress;
                comboChart.data.datasets[1].data = data.severity;
                comboChart.update();
            }
            else if (type === 'sleep') {
                chartSleep.data.labels = data.labels;
                chartSleep.data.datasets = [
                    { label: 'Sleep Quality', data: data.data.sleep, borderColor: '#8B5CF6', backgroundColor: '#8B5CF6', tension: 0.4 },
                    { label: 'Tic Count', data: data.data.frequency, borderColor: '#10B981', borderDash: [5,5], tension: 0.4 }
                ];
                chartSleep.update();
            }
            else if (type === 'multi') {
                chartMulti.data.labels = data.labels;
                chartMulti.data.datasets = [
                    { label: 'Stress', data: data.data.stress, backgroundColor: '#fb923c' },
                    { label: 'Anxiety', data: data.data.anxiety, backgroundColor: '#60a5fa' },
                    { label: 'Sleep', data: data.data.sleep, backgroundColor: '#a78bfa' },
                    { label: 'Intensity', data: data.data.intensity, backgroundColor: '#f87171' },
                    { label: 'Tic Count', data: data.data.frequency, backgroundColor: '#34d399' }
                ];
                chartMulti.update();
            }
            else if (type === 'hourly') {
                chartHourly.data.datasets = [{
                    label: 'Tics', 
                    data: data.data, 
                    fill: true,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: '#3B82F6',
                    tension: 0.4 
                }];
                chartHourly.update();
            }
            else if (type === 'moods') {
                chartMoods.data.labels = data.data.labels;
                chartMoods.data.datasets = [{
                    data: data.data.values,
                    backgroundColor: ['#FCA5A5', '#FCD34D', '#6EE7B7', '#93C5FD', '#C4B5FD'],
                    borderWidth: 1
                }];
                chartMoods.update();
            }
            else if (type === 'muscles') {
                chartMuscles.data.labels = data.data.labels;
                chartMuscles.data.datasets = [{
                    data: data.data.values, backgroundColor: ['#005949', '#26A69A', '#4DB6AC', '#80CBC4', '#B2DFDB', '#E0F2F1']
                }];
                chartMuscles.update();
            }
        } catch (e) { console.error(`Error loading ${type}:`, e); }
    }

    function changeOffset(type, delta) {
        offsets[type] += delta;
        if (offsets[type] < 0) offsets[type] = 0;
        loadGraph(type);
    }

    ['sleep', 'multi', 'hourly', 'moods', 'muscles'].forEach(t => loadGraph(t));

    function exportPDF() {
        const element = document.getElementById('report-container');
        const header = document.getElementById('pdf-header');
        header.classList.remove('hidden');
        html2pdf().set({
            margin: [0.5, 0.5], filename: 'Report.pdf', image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 }, jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        }).from(element).save().then(() => header.classList.add('hidden'));
    }
</script>