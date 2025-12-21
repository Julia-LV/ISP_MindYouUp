<?php
/*
 * home_patient.php
 * Patient Dashboard
 * Updated: Muscle Pie Chart & Sleep/Anxiety/Tic Analysis
 */

// --- 1. PHP Logic & Data Fetching ---
session_start();

// --- CONFIG & DB CONNECTION ---
$config_path = '../../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    $conn = null;
}

// --- SECURITY CHECK ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != "Patient") {
    // header("Location: ../auth/login.php"); exit;
}

$patient_id = $_SESSION["user_id"] ?? 1; 

// --- FETCH NAME FROM DB ---
$patient_name = $_SESSION["First_Name"] ?? "Patient";
if (($patient_name === "Patient") && $conn) {
    $sql_name = "SELECT First_Name FROM user_profile WHERE User_ID = ?";
    if ($stmt = $conn->prepare($sql_name)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $patient_name = $row['First_Name'];
        }
        $stmt->close();
    }
}

// --- A. DYNAMIC GREETING ---
$hour = date('G');
$greeting_text = "Good Evening";
if ($hour < 12) {
    $greeting_text = "Good Morning";
} elseif ($hour < 18) {
    $greeting_text = "Good Afternoon";
}

// --- DATA FETCHING ---

// 1. Get Today's Stats
$today_tics = 0;
$avg_stress = 0; 

if ($conn) {
    // Count Tics
    $sql_tics = "SELECT COUNT(*) as count FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = CURDATE()";
    $stmt = $conn->prepare($sql_tics);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $today_tics = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // Avg Stress
    $sql_stress = "SELECT AVG(Stress) as avg_stress FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = CURDATE()";
    if ($stmt = $conn->prepare($sql_stress)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $avg_stress = $res['avg_stress'] !== null ? round($res['avg_stress'], 1) : 0;
        $stmt->close();
    }
}

// --- B. SMILEY LOGIC ---
$stress_icon = 'smile';
$stress_color_class = 'text-green-500 bg-green-50 border-green-400'; 
if ($avg_stress > 3 && $avg_stress <= 7) {
    $stress_icon = 'meh';
    $stress_color_class = 'text-orange-500 bg-orange-50 border-orange-400'; 
} elseif ($avg_stress > 7) {
    $stress_icon = 'frown';
    $stress_color_class = 'text-red-500 bg-red-50 border-red-400'; 
}

// 2. Fetch Graph Data (Last 7 Days)
$dates = [];
$tic_counts = [];
$stress_levels = [];
$tic_intensities = [];

// For Bottom Graphs
$muscle_labels = []; $muscle_data = [];
$hourly_activity = array_fill(0, 24, 0);
$sleep_quality = []; $anxiety_levels = []; $daily_tic_load = [];

if ($conn) {
    // 7 Day Loop
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $short_date = date('D', strtotime("-$i days"));
        $dates[] = $short_date;

        // A. Tic Data
        $sql = "SELECT COUNT(*) as c, AVG(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $patient_id, $date);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $tic_counts[] = $res['c'] ?? 0;
        $tic_intensities[] = $res['i'] ? round($res['i'], 1) : 0;
        $stmt->close();

        // B. Stress Data
        $sql = "SELECT AVG(Stress) as s FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("is", $patient_id, $date);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stress_levels[] = $res['s'] ? round($res['s'], 1) : 0;
            $stmt->close();
        } else {
            $stress_levels[] = 0;
        }

        // C. Sleep & Anxiety Data (NOW FETCHING ANXIETY TOO)
        $sql = "SELECT Sleep, Anxiety FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("is", $patient_id, $date);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            
            $sleep_quality[] = $res['Sleep'] ?? 0;
            $anxiety_levels[] = $res['Anxiety'] ?? 0; // Capture Anxiety
            $daily_tic_load[] = $tic_counts[count($tic_counts)-1]; 
            
            $stmt->close();
        } else {
             $sleep_quality[] = 0;
             $anxiety_levels[] = 0;
             $daily_tic_load[] = 0;
        }
    }

    // D. Muscle Group Data (For Pie Chart)
    $sql = "SELECT Muscle_Group, COUNT(*) as count FROM tic_log WHERE Patient_ID = ? AND Muscle_Group != '' GROUP BY Muscle_Group ORDER BY count DESC LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $muscle_labels[] = $row['Muscle_Group'];
        $muscle_data[] = $row['count'];
    }
    $stmt->close();

    // E. Hourly Data
    $sql = "SELECT HOUR(Created_At) as tic_hour, COUNT(*) as count FROM tic_log WHERE Patient_ID = ? AND Created_At >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY HOUR(Created_At)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $hourly_activity[$row['tic_hour']] = $row['count'];
    }
    $stmt->close();

} else {
    // MOCK DATA
    $dates = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $tic_counts = [5, 12, 8, 15, 6, 9, $today_tics];
    $stress_levels = [3, 7, 4, 8, 2, 5, 3];
    $tic_intensities = [2, 6, 3, 7, 2, 4, 2];
    $muscle_labels = ['Eyes', 'Neck', 'Shoulder', 'Arm', 'Vocal'];
    $muscle_data = [15, 10, 8, 5, 4];
    $hourly_activity = array_fill(0, 24, 2);
    $sleep_quality = [6, 7, 5, 8, 4, 6, 7];
    $anxiety_levels = [4, 8, 3, 7, 2, 5, 4]; // Mock Anxiety
    $daily_tic_load = [10, 8, 15, 5, 20, 10, 5];
}

// 3. Recent Activity
$recent_activities = [];
if ($conn) {
    $sql_t = "SELECT 'tic' as type, Type_Description as title, Intensity as val1, Pain_Level as val2, Created_At as time FROM tic_log WHERE Patient_ID = ? ORDER BY Created_At DESC LIMIT 5";
    $sql_e = "SELECT 'mood' as type, Emotion as title, Stress as val1, Anxiety as val2, Occurrence as time FROM emotional_diary WHERE Patient_ID = ? ORDER BY Occurrence DESC LIMIT 5";
    
    $temp = [];
    if($stmt = $conn->prepare($sql_t)) { $stmt->bind_param("i", $patient_id); $stmt->execute(); $res = $stmt->get_result(); while($r=$res->fetch_assoc()) $temp[]=$r; $stmt->close(); }
    if($stmt = $conn->prepare($sql_e)) { $stmt->bind_param("i", $patient_id); $stmt->execute(); $res = $stmt->get_result(); while($r=$res->fetch_assoc()) $temp[]=$r; $stmt->close(); }
    
    usort($temp, function ($a, $b) { return strtotime($b['time']) - strtotime($a['time']); });
    $recent_activities = array_slice($temp, 0, 5);
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
                <h2 class="text-3xl font-bold text-[#005949]">
                    <?php echo $greeting_text . ", " . htmlspecialchars($patient_name); ?>
                </h2>
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
                <div class="bg-[#E9F0E9] p-3 rounded-xl text-[#005949]">
                    <i data-lucide="activity" class="w-8 h-8"></i>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 <?php echo explode(' ', $stress_color_class)[2]; ?> flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Avg Stress Today</p>
                    <div class="flex items-baseline mt-2">
                        <span class="text-4xl font-bold text-gray-800"><?php echo $avg_stress; ?></span>
                        <span class="text-sm text-gray-400 ml-1">/ 10</span>
                    </div>
                </div>
                <div class="p-3 rounded-xl <?php echo $stress_color_class; ?>">
                    <i data-lucide="<?php echo $stress_icon; ?>" class="w-8 h-8"></i>
                </div>
            </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Weekly Tic Frequency</h3>
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded">Last 7 Days</span>
                </div>
                <div class="relative h-64 w-full"><canvas id="ticFrequencyChart"></canvas></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Stress vs. Tic Intensity</h3>
                
                </div>
                <div class="relative h-64 w-full"><canvas id="correlationChart"></canvas></div>
                <div class="mt-4 flex items-center justify-center gap-4 text-xs text-gray-500">
                    <div class="flex items-center"><span class="w-3 h-1 bg-orange-400 mr-2"></span>Stress Level</div>
                    <div class="flex items-center"><span class="w-3 h-3 bg-[#2dd4bf] mr-2 rounded-sm"></span>Avg Intensity</div>
                </div>
            </div> 
        </div> 

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Affected Areas</h3>
                <div class="relative h-60 w-full flex justify-center">
                    <canvas id="musclePieChart"></canvas>
                </div>
                
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Daily Rhythm</h3>
                <div class="relative h-60 w-full"><canvas id="hourlyAreaChart"></canvas></div>
                <div class="mt-2 text-xs text-gray-400 text-center">Shows tic activity by time of day.</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Sleep & Anxiety vs Tics</h3>
                <div class="relative h-60 w-full"><canvas id="sleepDualChart"></canvas></div>
                <div class="mt-2 text-xs text-gray-400 text-center">
                    <span class="text-orange-400">● Sleep</span> 
                    <span class="text-[#F282A9] ml-2">● Anxiety</span> 
                    <span class="text-[#005949] ml-2">-- Tics</span>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Recent Activity</h3>
                    <a href="#" class="text-sm text-[#005949] font-bold hover:underline">View All</a>
                </div>
                <div class="divide-y divide-gray-50">
                    <?php if (count($recent_activities) > 0): ?>
                        <?php foreach ($recent_activities as $act): 
                            if ($act['type'] === 'tic') {
                                $icon = 'activity'; $bg = 'bg-teal-50 text-[#005949]'; $desc = "Intensity: " . $act['val1'] . "/10";
                            } else {
                                $icon = 'smile'; $bg = 'bg-orange-50 text-orange-500'; $desc = "Stress: " . $act['val1'] . " | Anxiety: " . $act['val2'];
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
                                <span class="text-xs text-gray-400 font-medium"><?php echo date('H:i', strtotime($act['time'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-400 text-sm">No recent activity found.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-[#005949] rounded-lg p-6 text-white flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white opacity-10 rounded-full"></div>
                <div>
                    <div class="bg-white/20 w-fit p-2 rounded-lg mb-4"><i data-lucide="lightbulb" class="w-5 h-5 text-yellow-300"></i></div>
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

<script>
    lucide.createIcons();

    // Data from PHP
    const dates = <?php echo json_encode($dates); ?>;
    const ticCounts = <?php echo json_encode($tic_counts); ?>;
    const stressLevels = <?php echo json_encode($stress_levels); ?>;
    const ticIntensities = <?php echo json_encode($tic_intensities); ?>;
    
    // Bottom Charts Data
    const muscleLabels = <?php echo json_encode($muscle_labels); ?>;
    const muscleData = <?php echo json_encode($muscle_data); ?>;
    const hourlyData = <?php echo json_encode(array_values($hourly_activity)); ?>;
    const hourlyLabels = ["12AM","1AM","2AM","3AM","4AM","5AM","6AM","7AM","8AM","9AM","10AM","11AM","12PM","1PM","2PM","3PM","4PM","5PM","6PM","7PM","8PM","9PM","10PM","11PM"];
    
    // Triple Line Data
    const sleepQual = <?php echo json_encode($sleep_quality); ?>;
    const anxietyLevels = <?php echo json_encode($anxiety_levels); ?>;
    const dailyLoad = <?php echo json_encode($daily_tic_load); ?>;

    // 1. Frequency
    new Chart(document.getElementById('ticFrequencyChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{ label: 'Total Tics', data: ticCounts, borderColor: '#005949', backgroundColor: 'rgba(0, 89, 73, 0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } } } }
    });

    // 2. Correlation
    new Chart(document.getElementById('correlationChart'), {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [
                { type: 'line', label: 'Stress', data: stressLevels, borderColor: '#fb923c', borderWidth: 2, borderDash: [5,5], pointRadius: 0, tension: 0.4 },
                { type: 'bar', label: 'Avg Intensity', data: ticIntensities, backgroundColor: '#2dd4bf', borderRadius: 4, barThickness: 16 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: { display: true, min: 0, max: 10, ticks: { stepSize: 2 } } }
        }
    });

    // 3. Muscle Groups (PIE CHART)
    new Chart(document.getElementById('musclePieChart'), {
        type: 'pie',
        data: {
            labels: muscleLabels,
            datasets: [{
                data: muscleData,
                backgroundColor: [
                    '#005949', // Dark Green
                    '#F282A9', // Pink
                    '#F26647', // Orange
                    '#fcd34d', // Yellow
                    '#94a3b8'  // Gray
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
            }
        }
    });

    // 4. Hourly Activity
    new Chart(document.getElementById('hourlyAreaChart'), {
        type: 'line',
        data: {
            labels: hourlyLabels,
            datasets: [{ label: 'Activity', data: hourlyData, borderColor: '#2dd4bf', backgroundColor: 'rgba(45, 212, 191, 0.2)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 6 } }, y: { display: false } } }
    });

    // 5. Sleep + Anxiety + Tics (TRIPLE LINE)
    new Chart(document.getElementById('sleepDualChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                { 
                    label: 'Sleep Quality', 
                    data: sleepQual, 
                    borderColor: '#fb923c', // Orange
                    backgroundColor: '#fb923c', 
                    yAxisID: 'y', 
                    tension: 0.3, 
                    borderWidth: 2 
                },
                { 
                    label: 'Anxiety', 
                    data: anxietyLevels, 
                    borderColor: '#F282A9', // Purple
                    backgroundColor: '#F282A9', 
                    yAxisID: 'y', // Shares Axis with Sleep
                    tension: 0.3, 
                    borderWidth: 2 
                },
                { 
                    label: 'Tic Count', 
                    data: dailyLoad, 
                    borderColor: '#005949', // Green
                    backgroundColor: '#005949', 
                    yAxisID: 'y1', // Separate Axis
                    tension: 0.3, 
                    borderWidth: 2, 
                    borderDash: [2, 2] 
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } }, // Custom Legend in HTML
            scales: {
                x: { grid: { display: false } },
                y: { type: 'linear', display: false, position: 'left', min: 0, max: 10 },
                y1: { type: 'linear', display: false, position: 'right', grid: { display: false } }
            }
        }
    });
</script>