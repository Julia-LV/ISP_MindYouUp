<?php
session_start();
include('../../config.php');

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- AGE CALCULATION  ---
function calculateAge($dob) {
    if (empty($dob)) return 'N/A';
    try {
        $birthDate = new DateTime($dob);
        $now = new DateTime();
        $interval = $now->diff($birthDate);
        return $interval->y;
    } catch (Exception $e) {
        return 'N/A';
    }
}

// 2. Fetch Pending REQUESTS 
$req_sql = "SELECT link.Link_ID, u.First_Name, u.Last_Name, u.User_Image, link.Assigned_Date
            FROM patient_professional_link link
            JOIN user_profile u ON link.Patient_ID = u.User_ID
            WHERE link.Professional_ID = ? AND link.Connection_Status = 'Pending'";
$req_stmt = $conn->prepare($req_sql);
$req_stmt->bind_param("i", $user_id);
$req_stmt->execute();
$requests = $req_stmt->get_result();
$requestCount = $requests->num_rows; 

// 3. Fetch Active Patients 

$sql = "SELECT link.Link_ID, u.User_ID, u.First_Name, u.Last_Name, u.User_Image, u.Email, u.Birthday, link.Status as Medical_Status, link.Assigned_Date
        FROM patient_professional_link link
        JOIN user_profile u ON link.Patient_ID = u.User_ID
        WHERE link.Professional_ID = ? AND link.Connection_Status = 'Accepted'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

function getStatusBadge($status) {
    if (empty($status)) return 'bg-gray-100 text-gray-400 border-gray-200';
    switch ($status) {
        case 'Pending': return 'bg-yellow-50 text-yellow-600 border-yellow-200';
        case 'Currently Followed': return 'bg-green-50 text-green-600 border-green-200';
        case 'Discharged': return 'bg-orange-50 text-orange-600 border-orange-200';
        case 'Drop Out': return 'bg-gray-50 text-gray-600 border-gray-200';
        default: return 'bg-gray-50 text-gray-500 border-gray-200';
    }
}
?>

<?php include('../../components/header_component.php'); ?>

<div class="flex min-h-screen bg-[#E9F0E9] font-sans">
    <?php include('../../includes/navbar.php'); ?>

    <main class="flex-1 p-8">
        <div class="max-w-6xl mx-auto">

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">My Patients</h1>
                    <p class="text-gray-500 text-sm">Manage the patient list and statuses.</p>
                </div>

                <div class="flex gap-3">
                    
                    <button onclick="openModal('requestsModal')" class="relative flex items-center gap-2 bg-white text-gray-700 border border-gray-300 px-5 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span class="font-medium">Requests</span>
                        
                        <?php if($requestCount > 0): ?>
                            <span class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white text-xs font-bold flex items-center justify-center rounded-full shadow-md">
                                <?= $requestCount ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <a href="search_patients.php" class="flex items-center gap-2 bg-[#F0856C] text-white px-5 py-2.5 rounded-xl shadow-md hover:bg-[#F26647] transition">
                        <span class="text-xl font-bold">+</span>
                        <span class="font-medium">Add New</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($patient = $result->fetch_assoc()): ?>
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center text-center relative hover:shadow-md transition">
                            
                            <div class="w-full flex justify-between items-start absolute top-4 px-4 left-0">
                                <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md border <?= getStatusBadge($patient['Medical_Status']) ?>">
                                    <?= !empty($patient['Medical_Status']) ? $patient['Medical_Status'] : 'Unknown' ?>
                                </span>
                                <?php $formId = "del_" . $patient['Link_ID']; ?>
                                <form id="<?= $formId ?>" action="../common/delete_handler.php" method="POST">
                                    <input type="hidden" name="link_id" value="<?= $patient['Link_ID'] ?>">
                                    <button type="button" onclick="confirmDelete('<?= $formId ?>', '<?= htmlspecialchars($patient['First_Name']) ?>')" class="text-gray-300 hover:text-red-500 transition p-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                            </div>

                            <div class="mt-6">
                                <img src="<?= htmlspecialchars($patient['User_Image'] ?? '../../assets/default_user.png') ?>" class="w-20 h-20 rounded-full object-cover mb-4 border-4 border-green-50 mx-auto">
                            </div>
                            <h3 class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($patient['First_Name'] . ' ' . $patient['Last_Name']) ?></h3>
                            
                            <p class="text-gray-500 text-sm mb-1">
                                <?= calculateAge($patient['Birthday']) ?> Years Old
                            </p>
                            
                            <p class="text-gray-400 text-xs mb-6">Added <?= date('M Y', strtotime($patient['Assigned_Date'])) ?></p>

                            <div class="w-full flex gap-2">
                                <a href="chat.php?link_id=<?= $patient['Link_ID'] ?>" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition">Message</a>
                                <form action="update_status_handler.php" method="POST" class="flex-1">
                                    <input type="hidden" name="link_id" value="<?= $patient['Link_ID'] ?>">
                                    <select name="status" onchange="this.form.submit()" class="w-full py-2.5 px-2 rounded-xl bg-indigo-50 text-indigo-700 text-sm font-semibold border-none cursor-pointer hover:bg-indigo-100 focus:ring-0 text-center appearance-none">
                                        <option value="" disabled selected>Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Currently Followed">Followed</option>
                                        <option value="Discharged">Discharged</option>
                                        <option value="Drop Out">Drop Out</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full py-16 text-center">
                        <p class="text-gray-500">No active patients connected yet.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <div id="requestsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 m-4 shadow-2xl relative">
            
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h2 class="text-xl font-bold text-gray-800">Connection Requests</h2>
                <button onclick="closeModal('requestsModal')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <?php if($requestCount > 0): ?>
                <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                    <?php while($req = $requests->fetch_assoc()): ?>
                        <div class="flex items-center justify-between bg-orange-50 p-4 rounded-xl border border-orange-100">
                            <div class="flex items-center gap-3">
                                <img src="<?= htmlspecialchars($req['User_Image'] ?? '../../assets/default_user.png') ?>" class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($req['First_Name'].' '.$req['Last_Name']) ?></h4>
                                    <p class="text-xs text-gray-500">Requested: <?= date('M d', strtotime($req['Assigned_Date'])) ?></p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <form action="handle_request.php" method="POST">
                                    <input type="hidden" name="link_id" value="<?= $req['Link_ID'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-green-700">Accept</button>
                                </form>
                                <form action="handle_request.php" method="POST">
                                    <input type="hidden" name="link_id" value="<?= $req['Link_ID'] ?>">
                                    <input type="hidden" name="action" value="decline">
                                    <button onclick="return confirm('Decline?')" class="bg-white border border-red-200 text-red-500 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-50">Decline</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-10">
                    <p class="text-gray-400">No pending requests.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include('../../components/modals.php'); ?>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    
    window.onclick = function(event) {
        const modal = document.getElementById('requestsModal');
        if (event.target == modal) {
            closeModal('requestsModal');
        }
    }
</script>