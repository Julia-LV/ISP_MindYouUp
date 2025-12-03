<?php
session_start();
include('../../config.php');

// --- 0. Security Check ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$professional_id = $_SESSION['user_id'];
$patient_list = [];

// --- 1. Fetch List of Linked Patients ---
$sql_patients = "SELECT 
                    up.User_ID, up.First_Name, up.Last_Name
                 FROM patient_professional_link ppl
                 JOIN user_profile up ON ppl.Patient_ID = up.User_ID
                 WHERE ppl.Professional_ID = ?
                 ORDER BY up.First_Name";

$stmt = $conn->prepare($sql_patients);
$stmt->bind_param("i", $professional_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $patient_list[] = $row;
}
$stmt->close();

// --- 2. Determine Time Filter and Patient Selection ---

$selected_filter = $_GET['time_filter'] ?? 'all'; 
$selected_patient_id = $_GET['patient_id'] ?? 'all'; 

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

// Patient Selection Logic
$patient_ids_to_query = [];
$is_all_patients_selected = ($selected_patient_id === 'all');

if (!$is_all_patients_selected) {
    $requested_id = (int)$selected_patient_id;
    
    // Security check: Ensure the requested ID is actually linked
    $is_linked = false;
    foreach ($patient_list as $patient) {
        if ($patient['User_ID'] == $requested_id) {
            $is_linked = true;
            break;
        }
    }
    
    if ($is_linked) {
        $patient_ids_to_query[] = $requested_id;
    }
} elseif (!empty($patient_list)) {
    // Default: View data for ALL linked patients
    $patient_ids_to_query = array_column($patient_list, 'User_ID');
}


// --- 3. Base Query Condition and Binding Setup ---
$condition = "";
$bind_types = "";
$bind_values = [];

if (!empty($patient_ids_to_query)) {
    $placeholders = implode(',', array_fill(0, count($patient_ids_to_query), '?'));
    $condition = " AND t.Patient_ID IN ($placeholders)";
    $bind_types = str_repeat('i', count($patient_ids_to_query));
    $bind_values = $patient_ids_to_query;
}


/* ----------------------------------------------------------
   4. DATA QUERIES (Using the dynamic $condition)
-----------------------------------------------------------*/

// Helper function to execute and fetch data (adapted to handle date filter)
function fetchData($conn, $base_sql, $condition, $date_condition, $bind_types, $bind_values) {
    if (empty($bind_values)) {
        return []; // Skip query if no IDs to query
    }

    $sql = str_replace(["/*CONDITION_PLACEHOLDER*/", "/*DATE_PLACEHOLDER*/"], [$condition, $date_condition], $base_sql);
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters dynamically
    $params = array_merge([$bind_types], $bind_values);
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    
    call_user_func_array([$stmt, 'bind_param'], $refs);
    
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}


// --- 4.1. TYPE_DESCRIPTION PIE CHART ---
$base_sql_type = "SELECT Type_Description, COUNT(*) as total
                  FROM tic_log t
                  WHERE Type_Description <> 'No Tics Today'
                  /*CONDITION_PLACEHOLDER*/
                  /*DATE_PLACEHOLDER*/
                  GROUP BY Type_Description";

$typeData = fetchData($conn, $base_sql_type, $condition, $date_condition, $bind_types, $bind_values);
$typeLabels = array_column($typeData, 'Type_Description');
$typeValues = array_column($typeData, 'total');


// --- 4.2. DURATION PIE ---
$base_sql_duration = "SELECT Duration, COUNT(*) as total
                      FROM tic_log t
                      WHERE Type_Description <> 'No Tics Today'
                      AND (Duration = 'Less than a minute' OR Duration = 'More than a minute')
                      /*CONDITION_PLACEHOLDER*/
                      /*DATE_PLACEHOLDER*/
                      GROUP BY Duration";

$durationData = fetchData($conn, $base_sql_duration, $condition, $date_condition, $bind_types, $bind_values);
$durationLabels = array_column($durationData, 'Duration');
$durationValues = array_column($durationData, 'total');


// --- 4.3. DAILY TIC COUNT (LINE GRAPH) - Raw Data ---
$base_sql_daily = "SELECT DATE(Created_At) AS day, COUNT(*) AS total
                   FROM tic_log t
                   WHERE Type_Description <> 'No Tics Today'
                   /*CONDITION_PLACEHOLDER*/
                   /*DATE_PLACEHOLDER*/
                   GROUP BY DATE(Created_At)
                   ORDER BY day ASC";

$dailyDataRaw = fetchData($conn, $base_sql_daily, $condition, $date_condition, $bind_types, $bind_values);


/* ----------------------------------------------------------
   5. ZERO DAYS LOGIC (Ensuring all dates between min/max are present)
-----------------------------------------------------------*/

$days = [];
$counts = [];

if (!empty($dailyDataRaw)) {
    $dailyMap = [];
    foreach ($dailyDataRaw as $row) {
        $dailyMap[$row['day']] = (int)$row['total'];
    }

    $minDate = new DateTime($dailyDataRaw[0]['day']);
    $maxDate = new DateTime(end($dailyDataRaw)['day']);
    
    // Adjust maxDate to include today if the current filter is 'all' or if the last record is not today
    if ($selected_filter === 'all' || $selected_filter === 'week' || $selected_filter === 'month') {
         $maxDate = new DateTime('today');
    }
    
    // Ensure minDate is not greater than maxDate
    if ($minDate > $maxDate) {
        $minDate = $maxDate;
    }

    $interval = new DateInterval('P1D'); // Period of 1 Day
    // Add one day to maxDate to ensure it is included in the period
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
<title>Professional Tic Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* CSS para o carousel e charts */
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
    align-items: center;
}
/* Aumentar o tamanho do container dos gráficos de PIZZA */
.chart-container {
    width: 90%; 
    max-width: 450px; /* Aumentado de 350px para 450px */
    margin: 0 auto;
}

.nav-arrow {
    position: absolute;
    top: 55%; 
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

<?php 
// include '../../includes/navbar.php'; 
?>

<div class="max-w-5xl mx-auto mt-10 bg-white shadow-lg p-6 rounded-xl">

    <h2 class="text-2xl font-bold text-center mb-6 text-[#005949]"> Professional Dashboard</h2>
    
    <div class="mb-6 border-b pb-4">
        <form method="GET" action="home_professional.php" id="filterForm" class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            
            <label for="patient_id" class="text-gray-700 font-medium whitespace-nowrap">Patient:</label>
            <select name="patient_id" id="patient_id" 
                    onchange="document.getElementById('filterForm').submit();"
                    class="mt-1 block w-full sm:w-auto p-2 border border-gray-300 rounded-md shadow-sm focus:border-[#005949] focus:ring focus:ring-[#005949] focus:ring-opacity-50">
                
                <option value="all" <?php if ($is_all_patients_selected) echo 'selected'; ?>>— All Linked Patients —</option>
                
                <?php foreach ($patient_list as $patient): ?>
                    <option value="<?php echo htmlspecialchars($patient['User_ID']); ?>"
                            <?php if ($selected_patient_id == $patient['User_ID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($patient['First_Name'] . ' ' . $patient['Last_Name'] . ' (ID: ' . $patient['User_ID'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="time_filter" class="text-gray-700 font-medium whitespace-nowrap">Time:</label>
            <select name="time_filter" id="time_filter" 
                    onchange="document.getElementById('filterForm').submit();"
                    class="mt-1 block w-full sm:w-auto p-2 border border-gray-300 rounded-md shadow-sm focus:border-[#005949] focus:ring focus:ring-[#005949] focus:ring-opacity-50">
                <option value="all" <?php if ($selected_filter == 'all') echo 'selected'; ?>>All Time</option>
                <option value="month" <?php if ($selected_filter == 'month') echo 'selected'; ?>>Last 30 Days</option>
                <option value="week" <?php if ($selected_filter == 'week') echo 'selected'; ?>>Last 7 Days</option>
            </select>
            
        </form>
    </div>

    <?php if (empty($patient_list)): ?>
        <div class="text-center p-8 bg-yellow-50 rounded-lg text-yellow-700">
            <p class="font-semibold">No Patients Found.</p>
            <p class="text-sm mt-1">You do not have any patients linked yet. Use the 'Link Patient' feature on your profile page.</p>
        </div>
    <?php elseif (empty($typeLabels) && empty($durationLabels) && empty($days)): ?>
        <div class="text-center p-8 bg-blue-50 rounded-lg text-blue-700">
            <p class="font-semibold">No Tic Data Logged for the selected period.</p>
            <p class="text-sm mt-1">The selected patient(s) have not logged any Tic data for the current filters.</p>
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