<?php
/*
 * home_professional.php
 * * Doctor/Professional Dashboard
 * * Allows selecting a patient, viewing analytics, and exporting a PDF report.
 */

session_start();

// --- CONFIG & DB CONNECTION ---
$config_path = '../../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    $conn = null;
}

// --- SECURITY CHECK ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "Professional") {
    // header("Location: ../auth/login.php"); exit;
}

$doctor_id = $_SESSION["user_id"] ?? 999; 

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
    // Mock Patients
    $patients = [
        ['id' => 1, 'name' => 'Priya Sharma'],
        ['id' => 2, 'name' => 'Rahul Verma']
    ];
}

// --- 2. HANDLE PATIENT SELECTION ---
$selected_patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : ($patients[0]['id'] ?? 0);
$selected_patient_name = "Unknown";
foreach ($patients as $p) {
    if ($p['id'] == $selected_patient_id) {
        $selected_patient_name = $p['name'];
        break;
    }
}

// --- 3. FETCH ANALYTICS ---
// (Summary Stats)
$total_tics_30d = 0;
$avg_stress_30d = 0;
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

// (Graph Data: Last 7 Days)
$dates = [];
$tic_severity = [];
$stress_levels = [];
if ($conn && $selected_patient_id) {
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('D', strtotime("-$i days"));

        $stmt = $conn->prepare("SELECT MAX(Intensity) as i FROM tic_log WHERE Patient_ID = ? AND DATE(Created_At) = ?");
        $stmt->bind_param("is", $selected_patient_id, $date);
        $stmt->execute();
        $tic_severity[] = $stmt->get_result()->fetch_assoc()['i'] ?? 0;
        $stmt->close();

        $stmt = $conn->prepare("SELECT AVG(Stress) as s FROM emotional_diary WHERE Patient_ID = ? AND DATE(Occurrence) = ?");
        $stmt->bind_param("is", $selected_patient_id, $date);
        $stmt->execute();
        $stress_levels[] = $stmt->get_result()->fetch_assoc()['s'] ?? 0;
        $stmt->close();
    }
} else {
    // Mock Data
    $dates = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $tic_severity = [4, 5, 8, 3, 4, 7, 2];
    $stress_levels = [3, 4, 8, 2, 3, 6, 2];
}

// (Graph Data: Pie Chart)
$motor_count = 0;
$vocal_count = 0;
if ($conn && $selected_patient_id) {
    $stmt = $conn->prepare("SELECT Type, COUNT(*) as c FROM tic_log WHERE Patient_ID = ? GROUP BY Type");
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        if (stripos($row['Type'], 'Motor') !== false) $motor_count += $row['c'];
        else $vocal_count += $row['c'];
    }
    $stmt->close();
} else {
    $motor_count = 15; $vocal_count = 5;
}

// (Recent Logs)
$recent_logs = [];
if ($conn && $selected_patient_id) {
    $sql = "SELECT Created_At, Type, Type_Description, Intensity, Describe_Text, Pain_Level 
            FROM tic_log WHERE Patient_ID = ? ORDER BY Created_At DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $recent_logs[] = $row;
    }
    $stmt->close();
}

$page_title = "Doctor Dashboard";
include '../../components/header_component.php'; 
include '../../includes/navbar.php'; 
?>

<!-- === 1. PDF LIBRARY === -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<main class="flex-1 w-full bg-gray-50 h-screen overflow-hidden flex flex-col">

    <!-- === 2. TOP BAR === -->
    <div class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between shrink-0 shadow-sm z-20">
        <div class="flex items-center gap-4">
            <div class="bg-teal-100 p-2 rounded-lg text-[#005949]">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
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
            <!-- PDF EXPORT BUTTON -->
            <button onclick="exportPDF()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export PDF
            </button>
            <button class="px-4 py-2 text-sm font-medium text-white bg-[#005949] rounded-lg hover:bg-[#004539] shadow-sm flex items-center gap-2">
                <i data-lucide="message-square" class="w-4 h-4"></i>
                Message
            </button>
        </div>
    </div>

    <!-- === 3. CONTENT AREA === -->
    <div class="flex-1 overflow-y-auto p-8">
        
        <!-- WRAPPER FOR PDF GENERATION -->
        <div id="report-container" class="max-w-7xl mx-auto space-y-6">

            <!-- HIDDEN HEADER FOR PDF (Visible only during export) -->
            <div id="pdf-header" class="hidden mb-6 border-b border-gray-300 pb-4">
                <h1 class="text-3xl font-bold text-[#005949]">Patient Analytics Report</h1>
                <div class="flex justify-between mt-2 text-gray-600">
                    <p><strong>Patient:</strong> <?php echo htmlspecialchars($selected_patient_name); ?></p>
                    <p><strong>Date Generated:</strong> <?php echo date('F j, Y'); ?></p>
                </div>
            </div>

            <!-- SUMMARY CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Stat 1 -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Tics (30 Days)</p>
                    <div class="mt-2 flex items-end gap-2">
                        <span class="text-3xl font-bold text-gray-800"><?php echo $total_tics_30d; ?></span>
                        <span class="text-xs text-green-600 font-medium mb-1 flex items-center">
                            <i data-lucide="arrow-down" class="w-3 h-3"></i> 12%
                        </span>
                    </div>
                </div>
                <!-- Stat 2 -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Avg Stress Score</p>
                    <div class="mt-2 flex items-end gap-2">
                        <span class="text-3xl font-bold text-gray-800"><?php echo $avg_stress_30d; ?></span>
                        <span class="text-xs text-gray-400 mb-1">/ 10</span>
                    </div>
                </div>
                <!-- Stat 3 -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Dominant Type</p>
                    <div class="mt-2">
                        <span class="text-2xl font-bold text-[#005949]">
                            <?php echo ($motor_count > $vocal_count) ? 'Motor' : 'Vocal'; ?>
                        </span>
                    </div>
                </div>
                <!-- Stat 4 -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Adherence</p>
                    <div class="mt-2">
                        <span class="text-2xl font-bold text-blue-600">92%</span>
                        <span class="text-xs text-gray-400">Log consistency</span>
                    </div>
                </div>
            </div>

            <!-- ANALYTICS ROW -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Graph -->
                <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800">Trigger Analysis: Stress vs. Severity</h3>
                        <div class="flex gap-3 text-xs">
                            <span class="flex items-center text-gray-500"><span class="w-2 h-2 rounded-full bg-orange-400 mr-1"></span> Stress</span>
                            <span class="flex items-center text-gray-500"><span class="w-2 h-2 rounded-full bg-[#005949] mr-1"></span> Tic Intensity</span>
                        </div>
                    </div>
                    <div class="h-72 w-full">
                        <canvas id="doctorComboChart"></canvas>
                    </div>
                </div>

                <!-- Doughnut Graph -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col">
                    <h3 class="font-bold text-gray-800 mb-4">Tic Classification</h3>
                    <div class="flex-1 flex items-center justify-center relative">
                        <canvas id="typeDoughnutChart"></canvas>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="text-center">
                                <span class="block text-2xl font-bold text-gray-800">
                                    <?php echo $motor_count + $vocal_count; ?>
                                </span>
                                <span class="text-xs text-gray-400">Total</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs">
                        <div class="bg-teal-50 p-2 rounded text-[#005949] font-semibold">
                            Motor: <?php echo $motor_count; ?>
                        </div>
                        <div class="bg-blue-50 p-2 rounded text-blue-600 font-semibold">
                            Vocal: <?php echo $vocal_count; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LOG TABLE -->
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
                                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap">
                                            <?php echo date('M d, H:i', strtotime($log['Created_At'])); ?>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 rounded text-xs font-semibold
                                                <?php echo (strpos($log['Type'], 'Motor') !== false) ? 'bg-teal-100 text-[#005949]' : 'bg-blue-100 text-blue-600'; ?>">
                                                <?php echo htmlspecialchars($log['Type']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 font-medium text-gray-800">
                                            <?php echo htmlspecialchars($log['Type_Description']); ?>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <div class="inline-block px-2 py-0.5 rounded text-xs font-bold
                                                <?php echo ($log['Intensity'] > 7) ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'; ?>">
                                                <?php echo $log['Intensity']; ?>/10
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($log['Describe_Text']); ?>">
                                            <?php echo htmlspecialchars($log['Describe_Text'] ?? '-'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">No logs found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- === 4. JAVASCRIPT LOGIC === -->
<script>
    lucide.createIcons();

    // Chart Data
    const labels = <?php echo json_encode($dates); ?>;
    const severityData = <?php echo json_encode($tic_severity); ?>;
    const stressData = <?php echo json_encode($stress_levels); ?>;
    const motorCount = <?php echo $motor_count; ?>;
    const vocalCount = <?php echo $vocal_count; ?>;

    // Initialize Charts
    const ctxCombo = document.getElementById('doctorComboChart').getContext('2d');
    new Chart(ctxCombo, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { type: 'line', label: 'Avg Stress', data: stressData, borderColor: '#fb923c', borderWidth: 2, borderDash: [5, 5], pointRadius: 4, tension: 0.3, yAxisID: 'y' },
                { type: 'bar', label: 'Max Intensity', data: severityData, backgroundColor: '#005949', borderRadius: 4, barThickness: 24, yAxisID: 'y' }
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

    // --- PDF EXPORT FUNCTION ---
    function exportPDF() {
        const element = document.getElementById('report-container');
        const header = document.getElementById('pdf-header');
        
        // Show header temporarily
        header.classList.remove('hidden');

        const opt = {
            margin:       [0.5, 0.5], // Top, Left margins (in inches)
            filename:     'Patient_Report_<?php echo date("Y-m-d"); ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true }, // Higher scale for better chart quality
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        // Generate PDF
        html2pdf().set(opt).from(element).save().then(() => {
            // Hide header again after save
            header.classList.add('hidden');
        });
    }
</script>