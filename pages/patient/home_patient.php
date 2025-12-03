<?php
/*
 * home_patient.php
 * * Patient Dashboard
 * Displays summary cards, trend graphs (Tics vs Emotions), and recent activity.
 */

// --- 1. PHP Logic & Data Fetching ---
session_start();

// --- CONFIG & DB CONNECTION ---
$config_path = '../../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    // Fallback if config isn't found (for preview purposes)
    $conn = null; 
}

// --- SECURITY CHECK ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != "Patient") {
    // Redirect logic would go here
    // header("Location: ../auth/login.php"); exit;
}

$patient_id = $_SESSION["user_id"] ?? 1; // Default to 1 for testing if session not set
$patient_name = $_SESSION["username"] ?? "Patient";

// --- DATA FETCHING FUNCTIONS ---

// 1. Get Today's Stats
$today_tics = 0;
$avg_stress = 0; // Default "N/A"

if ($conn) {
    // A. Count Tics Today (Table: tic_log, Date: Created_At)
    $sql_tics = "SELECT COUNT(*) as count FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = CURDATE()";
    $stmt = $conn->prepare($sql_tics);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $today_tics = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // B. Get Avg Stress Today (Table: emotional_diary, Date: Occurrence, Column: Stress)
    $sql_stress = "SELECT AVG(Stress) as avg_stress FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = CURDATE()";
    if ($stmt = $conn->prepare($sql_stress)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        // Round to 1 decimal place
        $avg_stress = $res['avg_stress'] !== null ? round($res['avg_stress'], 1) : "N/A";
        $stmt->close();
    }
}

// 2. Fetch Graph Data (Last 7 Days)
$dates = [];
$tic_counts = [];
$stress_levels = [];
$tic_intensities = [];

if ($conn) {
    // We get the last 7 days of data
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $short_date = date('D', strtotime("-$i days")); // Mon, Tue...
        
        $dates[] = $short_date;

        // A. Tic Data: Count & Avg Intensity (Table: tic_log)
        $sql = "SELECT COUNT(*) as c, AVG(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $patient_id, $date);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $tic_counts[] = $res['c'] ?? 0;
        $tic_intensities[] = $res['i'] ? round($res['i'], 1) : 0;
        $stmt->close();

        // B. Emotional Data: Avg Stress (Table: emotional_diary, Date: Occurrence)
        $sql = "SELECT AVG(Stress) as s FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("is", $patient_id, $date);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stress_levels[] = $res['s'] ? round($res['s'], 1) : 0;
            $stmt->close();
        } else {
            $stress_levels[] = 0; // Fallback
        }
    }
} else {
    // MOCK DATA if DB fails
    $dates = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $tic_counts = [5, 12, 8, 15, 6, 9, $today_tics];
    $stress_levels = [3, 7, 4, 8, 2, 5, 3];
    $tic_intensities = [2, 6, 3, 7, 2, 4, 2];
}

// 3. Fetch Recent Activity (Union of Tics and Emotions)
$recent_activities = [];

if ($conn) {
    // A. Fetch last 5 Tics
    // We Map: Title -> Type_Description, Val -> Intensity, Val2 -> Pain_Level
    $sql_t = "SELECT 'tic' as type, Type_Description as title, Intensity as val1, Pain_Level as val2, Created_At as time 
              FROM tic_log WHERE Patient_ID = ? ORDER BY Created_At DESC LIMIT 5";
    
    // B. Fetch last 5 Emotional Entries
    // We Map: Title -> Emotion, Val -> Stress, Val2 -> Anxiety
    $sql_e = "SELECT 'mood' as type, Emotion as title, Stress as val1, Anxiety as val2, Occurrence as time 
              FROM emotional_diary WHERE Patient_ID = ? ORDER BY Occurrence DESC LIMIT 5";

    $temp_activities = [];

    // Execute Tic Query
    if ($stmt = $conn->prepare($sql_t)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $temp_activities[] = $row; }
        $stmt->close();
    }

    // Execute Mood Query
    if ($stmt = $conn->prepare($sql_e)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $temp_activities[] = $row; }
        $stmt->close();
    }

    // Sort combined array by time DESC
    usort($temp_activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Take top 5
    $recent_activities = array_slice($temp_activities, 0, 5);
}

// --- END PHP LOGIC ---

$page_title = 'Dashboard';
// Include your existing components
include '../../components/header_component.php';
include '../../includes/navbar.php'; 
?>

<!-- Dependencies for Charts/Icons -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Main Content Wrapper (Matches ticlog.php bg color) -->
<main class="flex-1 w-full p-6 md:p-2 overflow-y-auto bg-[#E9F0E9]">

    <div class="p-6 md:p-8 space-y-6 max-w-7xl mx-auto">
        
        <!-- 1. Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold text-[#005949]">
                    Good Morning, <?php echo htmlspecialchars($patient_name); ?>
                </h2>
                <p class="text-gray-600 mt-1">Here is your daily overview.</p>
            </div>
            <a href="ticlog_motor.php" class="bg-[#005949] hover:bg-[#004539] text-white px-5 py-3 rounded-lg font-semibold shadow-sm transition-all flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Log New Tic
            </a>
        </div>

        <!-- 2. "At a Glance" Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Card 1: Tic Count -->
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[#005949] flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Tics Today</p>
                    <div class="flex items-baseline mt-2">
                        <span class="text-4xl font-bold text-gray-800"><?php echo $today_tics; ?></span>
                    </div>
                </div>
                <div class="bg-[#E9F0E9] p-3 rounded-xl text-[#005949]">
                    <i data-lucide="activity" class="w-8 h-8"></i>
                </div>
            </div>

            <!-- Card 2: Stress Level -->
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-orange-400 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Avg Stress Today</p>
                    <div class="flex items-baseline mt-2">
                        <span class="text-4xl font-bold text-gray-800"><?php echo $avg_stress; ?></span>
                        <span class="text-sm text-gray-400 ml-1">/ 10</span>
                    </div>
                </div>
                <div class="bg-orange-50 p-3 rounded-xl text-orange-500">
                    <i data-lucide="smile" class="w-8 h-8"></i>
                </div>
            </div>

            <!-- Card 3: Medication (Mocked for now) -->
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-400 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Next Medication</p>
                    <h3 class="text-lg font-bold text-gray-800 mt-2">Guanfacine</h3>
                    <p class="text-xs text-blue-600 font-bold mt-1">08:00 PM</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-xl text-blue-500">
                    <i data-lucide="pill" class="w-8 h-8"></i>
                </div>
            </div>
        </div>

        <!-- 3. Graphs Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Graph 1: Tic Frequency -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-[#005949]">Weekly Tic Frequency</h3>
                    <select class="text-xs bg-gray-50 border-none rounded text-gray-500 px-2 py-1 cursor-pointer focus:ring-[#005949]">
                        <option>Last 7 Days</option>
                    </select>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="ticFrequencyChart"></canvas>
                </div>
            </div>

            <!-- Graph 2: Correlation Analysis (Doctor's View) -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-[#005949]">Stress vs. Tic Intensity</h3>
                    <button class="text-[#005949] text-xs font-bold hover:underline">Analysis</button>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="correlationChart"></canvas>
                </div>
                <div class="mt-4 flex items-center justify-center gap-4 text-xs text-gray-500">
                    <div class="flex items-center"><span class="w-3 h-1 bg-orange-400 mr-2"></span>Stress Level</div>
                    <div class="flex items-center"><span class="w-3 h-3 bg-[#2dd4bf] mr-2 rounded-sm"></span>Tic Intensity</div>
                </div>
            </div>
        </div>

        <!-- 4. Recent Activity & Insights -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Recent Logs -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Recent Activity</h3>
                    <a href="#" class="text-sm text-[#005949] font-bold hover:underline">View All</a>
                </div>
                <div class="divide-y divide-gray-50" id="activity-feed">
                    <?php if (count($recent_activities) > 0): ?>
                        <?php foreach($recent_activities as $act): ?>
                            <?php 
                                // Determine styling based on type
                                if ($act['type'] === 'tic') {
                                    $icon = 'activity';
                                    $bg = 'bg-teal-50 text-[#005949]';
                                    $desc = "Intensity: " . $act['val1'] . "/10";
                                } else {
                                    $icon = 'smile';
                                    $bg = 'bg-orange-50 text-orange-500';
                                    $desc = "Stress: " . $act['val1'] . " | Anxiety: " . $act['val2'];
                                }
                            ?>
                            <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center <?php echo $bg; ?>">
                                    <i data-lucide="<?php echo $icon; ?>" class="w-5 h-5"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($act['title']); ?></h4>
                                    <p class="text-xs text-gray-500"><?php echo $desc; ?></p>
                                </div>
                                <span class="text-xs text-gray-400 font-medium">
                                    <?php echo date('H:i', strtotime($act['time'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-400 text-sm">No recent activity found.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Insights / Doctor's Note -->
            <div class="bg-[#005949] rounded-lg p-6 text-white flex flex-col justify-between relative overflow-hidden">
                <!-- Decorative element -->
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white opacity-10 rounded-full"></div>
                
                <div>
                    <div class="bg-white/20 w-fit p-2 rounded-lg mb-4">
                        <i data-lucide="lightbulb" class="w-5 h-5 text-yellow-300"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Pattern Detected</h3>
                    <p class="text-emerald-100 text-sm leading-relaxed">
                        Your tics seem to spike when your stress level goes above 7. Try a breathing exercise when you feel anxious.
                    </p>
                </div>
                <button class="mt-6 w-full py-3 bg-white text-[#005949] rounded-lg font-bold text-sm hover:bg-emerald-50 transition">
                    View Breathing Exercises
                </button>
            </div>
        </div>

    </div>
</main>

<!-- JS for Charts -->
<script>
    // 1. Initialize Icons
    lucide.createIcons();

    // 2. Pass PHP Data to JS
    const dates = <?php echo json_encode($dates); ?>;
    const ticCounts = <?php echo json_encode($tic_counts); ?>;
    const stressLevels = <?php echo json_encode($stress_levels); ?>;
    const ticIntensities = <?php echo json_encode($tic_intensities); ?>;

    // 3. Chart 1: Tic Frequency (Line Chart)
    const ctxFreq = document.getElementById('ticFrequencyChart').getContext('2d');
    new Chart(ctxFreq, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Total Tics',
                data: ticCounts,
                borderColor: '#005949', // Your brand color
                backgroundColor: 'rgba(0, 89, 73, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#005949',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // 4. Chart 2: Correlation (Combo Chart - Stress vs Intensity)
    const ctxCorr = document.getElementById('correlationChart').getContext('2d');
    new Chart(ctxCorr, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [
                {
                    type: 'line',
                    label: 'Stress Level',
                    data: stressLevels,
                    borderColor: '#fb923c', // Orange
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    type: 'bar',
                    label: 'Avg Tic Intensity',
                    data: ticIntensities,
                    backgroundColor: '#2dd4bf', // Teal (lighter)
                    borderRadius: 4,
                    barThickness: 16,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { 
                    display: false, 
                    min: 0, 
                    max: 10 
                }
            }
        }
    });
</script>