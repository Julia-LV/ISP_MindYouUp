<?php
/*
 * emotional_diary_visuals.php
 * SIMPLIFIED: Displays Emotion Text directly from Database
 */

session_start();
$config_path = '../../config.php';
if (file_exists($config_path)) { require_once $config_path; } else { $conn = null; }

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
if (isset($_GET['ajax_fetch']) && isset($_GET['type']) && isset($_GET['offset'])) {
    header('Content-Type: application/json');
    
    $type = $_GET['type']; 
    $offset = intval($_GET['offset']);
    $today_str = date('Y-m-d');
    
    $response = [];
    
    if ($conn) {
        // --- TREND & SLEEP (Numeric Charts - Keep Math here) ---
        if ($type === 'trend' || $type === 'sleep') {
            $labels = []; $data1 = []; $data2 = [];
            for ($i = 4; $i >= 0; $i--) {
                $days_ago = $offset + $i;
                $timestamp = strtotime("-$days_ago days");
                $date_db = date('Y-m-d', $timestamp);
                
                $labels[] = [date('D', $timestamp), ($date_db === $today_str) ? "Today" : date('d M', $timestamp)];
                
                $sql = "SELECT AVG(Stress) as s, AVG(Anxiety) as a, AVG(Sleep) as sl FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $patient_id, $date_db);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                
                if ($type === 'trend') {
                    $data1[] = $res['s'] ? round($res['s'], 1) : 0;
                    $data2[] = $res['a'] ? round($res['a'], 1) : 0;
                } else {
                    $data1[] = $res['sl'] ? round($res['sl'], 1) : 0;
                }
                $stmt->close();
            }
            $response['labels'] = $labels;
            $response['data1'] = $data1;
            if(!empty($data2)) $response['data2'] = $data2;
        }

        // --- MOOD (Now purely text-based counting) ---
        if ($type === 'mood') {
            $end_days_ago = $offset;
            $start_days_ago = $offset + 6;
            $date_start = date('Y-m-d', strtotime("-$start_days_ago days"));
            $date_end = date('Y-m-d', strtotime("-$end_days_ago days"));
            
            $mood_labels = [];
            $mood_data = [];
            
            // Just select the text directly
            $sql = "SELECT Emotion, COUNT(*) as count 
                    FROM emotional_diary 
                    WHERE Patient_ID = ? AND DATE(Occurrence) BETWEEN ? AND ?
                    GROUP BY Emotion ORDER BY count DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $patient_id, $date_start, $date_end);
            $stmt->execute();
            $res = $stmt->get_result();
            
            while($row = $res->fetch_assoc()){
                // No translation needed. Just ucfirst to be safe.
                $mood_labels[] = ucfirst($row['Emotion']);
                $mood_data[] = $row['count'];
            }
            $stmt->close();
            
            $response['labels'] = $mood_labels;
            $response['data'] = $mood_data;
            $response['date_range'] = date('d M', strtotime($date_start)) . " - " . date('d M', strtotime($date_end));
        }
    }
    echo json_encode($response);
    exit;
}

// =================================================================================
// 2. INITIAL PAGE LOAD
// =================================================================================

$page_title = 'Emotional Diary';
include '../../components/header_component.php';
include '../../includes/navbar.php';

// Initial Trend/Sleep Data
function getInitialData($conn, $patient_id) {
    $today_str = date('Y-m-d');
    $d = []; $s = []; $a = []; $sl = [];
    for ($i = 4; $i >= 0; $i--) {
        $ts = strtotime("-$i days");
        $date_db = date('Y-m-d', $ts);
        $d[] = [date('D', $ts), ($date_db === $today_str ? "Today" : date('d M', $ts))];
        $sql = "SELECT AVG(Stress) as s, AVG(Anxiety) as a, AVG(Sleep) as sl FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?";
        if ($conn) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $patient_id, $date_db);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $s[] = $res['s'] ? round($res['s'], 1) : 0;
            $a[] = $res['a'] ? round($res['a'], 1) : 0;
            $sl[] = $res['sl'] ? round($res['sl'], 1) : 0;
        } else { $s[]=0; $a[]=0; $sl[]=0; }
    }
    return ['dates'=>$d, 'stress'=>$s, 'anxiety'=>$a, 'sleep'=>$sl];
}
$initData = getInitialData($conn, $patient_id);

// Initial Mood Fetch (Direct Text)
$initMoodLabels = []; $initMoodData = [];
if ($conn) {
    $sql = "SELECT Emotion, COUNT(*) as count FROM emotional_diary WHERE Patient_ID = ? AND Occurrence >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY Emotion ORDER BY count DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) {
        $initMoodLabels[] = ucfirst($row['Emotion']);
        $initMoodData[] = $row['count'];
    }
}

// Recent Entries
$recent_entries = [];
if ($conn) {
    $sql = "SELECT Emotion, Stress, Anxiety, Sleep, Notes, Occurrence FROM emotional_diary WHERE Patient_ID = ? ORDER BY Occurrence DESC LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $recent_entries[] = $row;
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<div class="w-full p-6 md:p-2 overflow-y-auto bg-[#E9F0E9]">
    <div class="p-6 md:p-8 space-y-6 max-w-7xl mx-auto">
        
        <div class="text-left">
            <h2 class="text-3xl font-bold text-[#005949] mb-2"><?php echo htmlspecialchars($page_title); ?></h2>
        </div>

        <?php
        $tabs = [
            'Entry'   => 'new_emotional_diary.php',
            'Visuals' => 'emotional_diary_visuals.php'
        ];
        $active_tab = 'Visuals';
        $is_js = false; 
        include '../../components/diary_tabs.php';
        ?>

        <?php 
            $k_s = array_sum($initData['stress']) / count(array_filter($initData['stress'], function($x){ return $x > 0; }) ?: [1]);
            $k_a = array_sum($initData['anxiety']) / count(array_filter($initData['anxiety'], function($x){ return $x > 0; }) ?: [1]);
            $k_sl = array_sum($initData['sleep']) / count(array_filter($initData['sleep'], function($x){ return $x > 0; }) ?: [1]);
        ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-orange-100 flex justify-between items-center">
                <div><p class="text-xs font-bold text-gray-400 uppercase">Avg Stress (Last 7 days)</p><h3 class="text-2xl font-bold text-gray-800"><?php echo round($k_s,1); ?>/10</h3></div>
                <div class="p-2 bg-orange-50 text-orange-500 rounded-lg"><i data-lucide="zap" class="w-5 h-5"></i></div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-purple-100 flex justify-between items-center">
                <div><p class="text-xs font-bold text-gray-400 uppercase">Avg Anxiety (Last 7 days)</p><h3 class="text-2xl font-bold text-gray-800"><?php echo round($k_a,1); ?>/10</h3></div>
                <div class="p-2 bg-purple-50 text-purple-500 rounded-lg"><i data-lucide="wind" class="w-5 h-5"></i></div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-blue-100 flex justify-between items-center">
                <div><p class="text-xs font-bold text-gray-400 uppercase">Avg Sleep (Last 7 days)</p><h3 class="text-2xl font-bold text-gray-800"><?php echo round($k_sl,1); ?>/10</h3></div>
                <div class="p-2 bg-blue-50 text-blue-500 rounded-lg"><i data-lucide="moon" class="w-5 h-5"></i></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Stress & Anxiety Trends</h3>
                    <div class="flex items-center gap-1">
                        <button onclick="changeOffset('trend', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
                        <button onclick="changeOffset('trend', -7)" id="btnNext_trend" class="p-1 hover:bg-gray-100 rounded text-gray-500 disabled:opacity-30" disabled><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
                    </div>
                </div>
                <div class="relative h-72 w-full"><canvas id="trendChart"></canvas></div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Mood Breakdown</h3>
                        <p class="text-[10px] text-gray-400" id="moodDateRange">Last 7 Days</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button onclick="changeOffset('mood', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
                        <button onclick="changeOffset('mood', -7)" id="btnNext_mood" class="p-1 hover:bg-gray-100 rounded text-gray-500 disabled:opacity-30" disabled><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
                    </div>
                </div>
                <div class="relative flex-1 flex items-center justify-center"><canvas id="moodChart"></canvas></div>
                <div id="noMoodData" class="hidden text-center text-xs text-gray-400 mt-2">No logs for this week</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Sleep Quality</h3>
                    <div class="flex items-center gap-1">
                        <button onclick="changeOffset('sleep', 7)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
                        <button onclick="changeOffset('sleep', -7)" id="btnNext_sleep" class="p-1 hover:bg-gray-100 rounded text-gray-500 disabled:opacity-30" disabled><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
                    </div>
                </div>
                <div class="relative h-64 w-full"><canvas id="sleepChart"></canvas></div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Recent Diary Entries</h3>
                    <span class="text-xs text-gray-400">Latest 5</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500">
                            <tr>
                                <th class="p-4">Date & Time</th>
                                <th class="p-4">Emotion</th>
                                <th class="p-4">Levels</th>
                                <th class="p-4">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (count($recent_entries) > 0): ?>
                                <?php foreach ($recent_entries as $entry): ?>
                                    <tr class="hover:bg-orange-50/30 transition">
                                        <td class="p-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-800 date-display" data-timestamp="<?php echo strtotime($entry['Occurrence']); ?>"></div>
                                            <div class="text-xs text-gray-400 time-display" data-timestamp="<?php echo strtotime($entry['Occurrence']); ?>"></div>
                                        </td>
                                        <td class="p-4">
                                            <?php 
                                                $displayEmotion = ucfirst($entry['Emotion']);
                                                // Optional: Truncate very long mood strings for the table
                                                if (strlen($displayEmotion) > 25) {
                                                    $displayEmotion = substr($displayEmotion, 0, 25) . '...';
                                                }
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700" title="<?php echo htmlspecialchars($entry['Emotion']); ?>">
                                                <?php echo htmlspecialchars($displayEmotion); ?>
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex gap-2">
                                                <span class="text-xs border px-2 py-1 rounded bg-orange-50 border-orange-200 text-orange-600" title="Stress">S: <?php echo $entry['Stress']; ?></span>
                                                <span class="text-xs border px-2 py-1 rounded bg-purple-50 border-purple-200 text-purple-600" title="Anxiety">A: <?php echo $entry['Anxiety']; ?></span>
                                            </div>
                                        </td>
                                        <td class="p-4 truncate max-w-xs text-gray-500 italic">
                                            <?php echo !empty($entry['Notes']) ? htmlspecialchars(substr($entry['Notes'], 0, 30).(strlen($entry['Notes'])>30?'...':'')) : '<span class="text-gray-300">-</span>'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="p-6 text-center text-gray-400">No entries found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Pass initial data to external JS file
    window.emotionalDiaryData = <?php echo json_encode($initData); ?>;
    window.emotionalDiaryMoodLabels = <?php echo json_encode($initMoodLabels); ?>;
    window.emotionalDiaryMoodData = <?php echo json_encode($initMoodData); ?>;
</script>
<script src="../../js/patient/emotional_diary_visuals.js"></script>
