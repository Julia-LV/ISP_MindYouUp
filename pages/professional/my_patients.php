<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch CONNECTED Patients
$sql = "SELECT link.Link_ID, link.Assigned_Date, link.Status,
               u.First_Name, u.Last_Name, u.Email, u.User_Image, u.Age
        FROM patient_professional_link link
        JOIN user_profile u ON link.Patient_ID = u.User_ID
        WHERE link.Professional_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

function getStatusBadge($status)
{
    if (empty($status)) return 'bg-gray-100 text-gray-400 border-gray-200';
    switch ($status) {
        case 'Pending':
            return 'bg-yellow-50 text-yellow-600 border-yellow-200';
        case 'Currently Followed':
            return 'bg-green-50 text-green-600 border-green-200';
        case 'Discharged':
            return 'bg-orange-50 text-orange-600 border-orange-200';
        case 'Drop Out':
            return 'bg-gray-50 text-gray-600 border-gray-200';
        default:
            return 'bg-gray-50 text-gray-500 border-gray-200';
    }
}

include('../../components/header_component.php');
?>

<div class="flex h-screen bg-[#E9F0E9] font-sans">
    <?php include('../../includes/navbar.php'); ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="max-w-6xl mx-auto">

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">My Patients</h1>
                    <p class="text-gray-500 text-sm">Manage the patient list and statuses.</p>
                </div>

                <a href="search_patients.php" class="flex items-center gap-2 bg-[#F0856C] text-white px-5 py-2.5 rounded-xl shadow-md hover:bg-[#F26647] transition">
                    <span class="text-xl font-bold">+</span>
                    <span class="font-medium">Add New</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($patient = $result->fetch_assoc()): ?>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center text-center relative hover:shadow-md transition">

                            <div class="w-full flex justify-between items-start absolute top-4 px-4">
                                <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md border <?= getStatusBadge($patient['Status']) ?>">
                                    <?= !empty($patient['Status']) ? $patient['Status'] : 'Unknown' ?>
                                </span>

                                <?php $formId = "del_" . $patient['Link_ID']; ?>

                                <form id="<?= $formId ?>" action="../common/delete_handler.php" method="POST">
                                    <input type="hidden" name="link_id" value="<?= $patient['Link_ID'] ?>">

                                    <button type="button"
                                        onclick="confirmDelete('<?= $formId ?>', '<?= htmlspecialchars($patient['First_Name']) ?>')"
                                        class="text-gray-300 hover:text-red-500 transition p-1" title="Remove">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <div class="mt-6">
                                <img src="<?= htmlspecialchars($patient['User_Image'] ?? '../../assets/default_user.png') ?>"
                                    class="w-20 h-20 rounded-full object-cover mb-4 border-4 border-green-50 mx-auto">
                            </div>

                            <h3 class="font-bold text-gray-800 text-lg">
                                <?= htmlspecialchars($patient['First_Name'] . ' ' . $patient['Last_Name']) ?>
                            </h3>

                            <p class="text-gray-500 text-sm mb-1">
                                <?= !empty($patient['Age']) ? $patient['Age'] . ' Years Old' : 'Age Not Set' ?>
                            </p>

                            <p class="text-gray-400 text-xs mb-6">
                                Added <?= date('M Y', strtotime($patient['Assigned_Date'])) ?>
                            </p>

                            <div class="w-full flex gap-2">
                                <a href="mailto:<?= $patient['Email'] ?>" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition">
                                    Message
                                </a>

                                <form action="update_status_handler.php" method="POST" class="flex-1">
                                    <input type="hidden" name="link_id" value="<?= $patient['Link_ID'] ?>">
                                    <select name="status" onchange="this.form.submit()"
                                        class="w-full py-2.5 px-2 rounded-xl bg-indigo-50 text-indigo-700 text-sm font-semibold border-none cursor-pointer hover:bg-indigo-100 focus:ring-0 text-center appearance-none">
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
                        <p class="text-gray-500">No patients connected yet.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
    <?php include('../../components/modals.php'); ?>
</div>