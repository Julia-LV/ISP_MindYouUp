<?php
session_start();
include('../../config.php');

// Ensure user is logged in as patient
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$days = [];
$counts = [];

// --- 1. Determine Time Filter ---
$selected_filter = $_GET['time_filter'] ?? 'all'; // Default: 'all'
$date_condition = "";

// Time Filter Logic
switch ($selected_filter) {
    case 'week':
        $date_condition = " AND DATE(t.Created_At) >= DATE(NOW() - INTERVAL 7 DAY)";
        break;
    case 'month':
        $date_condition = " AND DATE(t.Created_At) >= DATE(NOW() - INTERVAL 30 DAY)";
        break;
    case 'all':
    default:
        // No date condition needed
        break;
}

/* ----------------------------------------------------------
   2. DATA QUERIES (Updated to use $date_condition)
-----------------------------------------------------------*/

// Helper function to execute and fetch data
function fetchData($conn, $base_sql, $patient_id, $date_condition) {
    // Replace placeholder with date condition
    $sql = str_replace("/*DATE_PLACEHOLDER*/", $date_condition, $base_sql);
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}


// --- 2.1. TYPE_DESCRIPTION PIE CHART ---
$base_sql_type = "SELECT Type_Description, COUNT(*) as total
                  FROM tic_log t
                  WHERE t.Patient_ID = ?
                  AND t.Type_Description <> 'No Tics Today'
                  /*DATE_PLACEHOLDER*/
                  GROUP BY t.Type_Description";

$typeData = fetchData($conn, $base_sql_type, $patient_id, $date_condition);
$typeLabels = array_column($typeData, 'Type_Description');
$typeValues = array_column($typeData, 'total');


// --- 2.2. DURATION PIE (ONLY <1 min / >1 min) ---
$base_sql_duration = "SELECT Duration, COUNT(*) as total
                      FROM tic_log t
                      WHERE t.Patient_ID = ?
                      AND t.Type_Description <> 'No Tics Today'
                      AND (t.Duration = 'Less than a minute' OR t.Duration = 'More than a minute')
                      /*DATE_PLACEHOLDER*/
                      GROUP BY t.Duration";

$durationData = fetchData($conn, $base_sql_duration, $patient_id, $date_condition);
$durationLabels = array_column($durationData, 'Duration');
$durationValues = array_column($durationData, 'total');


// --- 2.3. DAILY TIC COUNT (LINE GRAPH) - Raw Data ---
$base_sql_daily = "SELECT DATE(t.Created_At) AS day, COUNT(*) AS total
                   FROM tic_log t
                   WHERE t.Patient_ID = ?
                   AND t.Type_Description <> 'No Tics Today'
                   /*DATE_PLACEHOLDER*/
                   GROUP BY DATE(t.Created_At)
                   ORDER BY day ASC";

$dailyDataRaw = fetchData($conn, $base_sql_daily, $patient_id, $date_condition);


/* ----------------------------------------------------------
   3. ZERO DAYS LOGIC (Ensuring all dates between min/max are present)
-----------------------------------------------------------*/
if (!empty($dailyDataRaw)) {
    $dailyMap = [];
    foreach ($dailyDataRaw as $row) {
        $dailyMap[$row['day']] = (int)$row['total'];
    }

    $minDate = new DateTime($dailyDataRaw[0]['day']);
    $maxDate = new DateTime(end($dailyDataRaw)['day']);
    
    // Adjust maxDate to include today
    if ($selected_filter === 'all' || $selected_filter === 'week' || $selected_filter === 'month') {
         $maxDate = new DateTime('today');
    }
    
    if ($minDate > $maxDate) {
        $minDate = $maxDate;
    }

    $interval = new DateInterval('P1D'); 
    $period = new DatePeriod($minDate, $interval, $maxDate->modify('+1 day')); 

    foreach ($period as $date) {
        $dateStr = $date->format('Y-m-d');
        $days[] = $dateStr;
        $counts[] = $dailyMap[$dateStr] ?? 0; // If date not in map, count is 0
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Tic Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* Estilo Maximizado e Setas do home_professional.php */
.carousel {
    position: relative;
    overflow: hidden;
    width: 100%;
}
.carousel-inner {
    display: flex;
    transition: transform 0.4s ease-in-out;
}
.carousel-item {
    min-width: 100%;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center; /* Centrar gráficos dentro do item */
}
/* Aumentar o tamanho do container dos gráficos de PIZZA */
.chart-container {
    width: 90%; 
    max-width: 450px; 
    margin: 0 auto;
}

.nav-arrow {
    position: absolute;
    top: 55%; /* Ajustado para melhor centragem vertical */
    transform: translateY(-50%);
    background: #005949;
    color: white; 
    padding: 10px 14px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 10;
    line-height: 1; 
    font-size: 1.2rem; 
}
.nav-arrow:hover {
    background: #00463a;
}
#prev { left: 10px; }
#next { right: 10px; }
</style>
</head>
<body class="bg-gray-100">
<?php include '../../includes/navbar.php'; ?>
<?php include '../../components/header_component.php'; ?>
<?php 
// include '../../includes/navbar.php'; 
?>

<div class="max-w-6xl mx-auto mt-10 bg-white shadow-lg p-6 rounded-xl">

    <h2 class="text-2xl font-bold text-center mb-6 text-[#005949]">Your Tic Activity Dashboard</h2>
    
    <div class="mb-6 border-b pb-4 flex justify-center">
        <form method="GET" action="home_patient.php" id="filterForm" class="flex items-center space-x-4">
            
            <label for="time_filter" class="text-gray-700 font-medium whitespace-nowrap">Time Filter:</label>
            <select name="time_filter" id="time_filter" 
                    onchange="document.getElementById('filterForm').submit();"
                    class="mt-1 block w-full sm:w-auto p-2 border border-gray-300 rounded-md shadow-sm focus:border-[#005949] focus:ring focus:ring-[#005949] focus:ring-opacity-50">
                <option value="all" <?php if ($selected_filter == 'all') echo 'selected'; ?>>All Time</option>
                <option value="month" <?php if ($selected_filter == 'month') echo 'selected'; ?>>Last 30 Days</option>
                <option value="week" <?php if ($selected_filter == 'week') echo 'selected'; ?>>Last 7 Days</option>
            </select>
            
        </form>
    </div>

    <?php if (empty($typeLabels) && empty($durationLabels) && empty($days)): ?>
        <div class="text-center p-8 bg-blue-50 rounded-lg text-blue-700">
            <p class="font-semibold">No Tic Data Logged for the selected period.</p>
            <p class="text-sm mt-1">Start logging your Tics to see your dashboard data here.</p>
        </div>
    <?php else: ?>

        <div class="carousel mt-6">
            <div id="carousel-inner" class="carousel-inner">

                <div class="carousel-item">
                    <h3 class="text-xl font-semibold text-center mb-3">Percentage of Tic Types</h3>
                    <div class="chart-container">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>

                <div class="carousel-item">
                    <h3 class="text-xl font-semibold text-center mb-3">Duration of Tics</h3>
                    <div class="chart-container">
                        <canvas id="durationChart"></canvas>
                    </div>
                </div>

                <div class="carousel-item">
                    <h3 class="text-xl font-semibold text-center mb-3">Daily Tic Evolution</h3>
                    <canvas id="dailyChart"></canvas>
                </div>

            </div>

            <div id="prev" class="nav-arrow">&lt;</div>
            <div id="next" class="nav-arrow">&gt;</div>
        </div>
    <?php endif; ?>
</div>

<script>
// PHP values encoded to JavaScript variables
const typeLabels = <?= json_encode($typeLabels) ?>;
const typeValues = <?= json_encode($typeValues) ?>;
const durationLabels = <?= json_encode($durationLabels) ?>;
const durationValues = <?= json_encode($durationValues) ?>;
const dailyLabels = <?= json_encode($days) ?>; 
const dailyValues = <?= json_encode($counts) ?>; 

/* -------------------- CAROUSEL -------------------- */
let index = 0;
const totalSlides = 3;

function updateCarousel() {
    document.getElementById("carousel-inner").style.transform =
        `translateX(-${index * 100}%)`;
}

document.getElementById("next").onclick = () => {
    index = (index + 1) % totalSlides;
    updateCarousel();
};

document.getElementById("prev").onclick = () => {
    index = (index - 1 + totalSlides) % totalSlides;
    updateCarousel();
};

/* -------------------- CHART RENDERING -------------------- */

// Function to safely check for and render a chart
function renderChart(id, type, data, options = {}) {
    const ctx = document.getElementById(id);
    if (ctx) {
        // Only render if data is present
        if (data.datasets.length > 0 && data.datasets[0].data.length > 0) {
            new Chart(ctx, { type, data, options });
        }
    }
}

// --- PIE CHART: TYPES ---
renderChart('typeChart', 'pie', {
    labels: typeLabels,
    datasets: [{
        data: typeValues,
        backgroundColor: ['#005949', '#6D8D7E', '#E9F0E9', '#00997A', '#4CAF50'],
    }]
});

// --- PIE CHART: DURATION ---
renderChart('durationChart', 'pie', {
    labels: durationLabels,
    datasets: [{
        data: durationValues,
        backgroundColor: ['#005949', '#6D8D7E'],
    }]
});

// --- LINE GRAPH: DAILY TICS ---
renderChart('dailyChart', 'line', {
    labels: dailyLabels,
    datasets: [{
        label: 'Tics per Day',
        data: dailyValues,
        borderColor: '#005949',
        backgroundColor: 'rgba(0, 89, 73, 0.1)',
        fill: true,
        tension: 0.3,
    }]
}, {
    responsive: true,
    scales: {
        y: {
            beginAtZero: true,
            title: { display: true, text: 'Total Tics Logged' },
            ticks: { precision: 0 } 
        },
        x: {
             title: { display: true, text: 'Date' },
        }
    },
    plugins: {
        legend: { display: false }
    }
});
</script>

</body>
</html>