<?php
session_start();
include('../../config.php');

// 1. Security Check
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../auth/login.php");
    exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] != 'Patient') {
    header("Location: ../professional/professional_profile.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch Data 
// UPDATED SQL: We removed 'p.Patient_Status' and added a subquery for 'Latest_Status'
$sql = "SELECT u.First_Name, u.Last_Name, u.Email, u.User_Image, u.Age,
               p.Treatment_Type,
               -- Subquery: Go find the most recent status from the link table
               (SELECT Status FROM patient_professional_link 
                WHERE Patient_ID = u.User_ID 
                ORDER BY Link_ID DESC LIMIT 1) AS Latest_Status
        FROM user_profile u 
        LEFT JOIN patient_profile p ON u.User_ID = p.User_ID 
        WHERE u.User_ID = ?";
    
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

function displayVal($val) {
    return !empty($val) ? htmlspecialchars($val) : '<span class="text-gray-400 italic">Not Set</span>';
}

// Helper for Status Colors (Visual Upgrade)
function getStatusColor($status) {
    switch($status) {
        case 'Pending': return 'text-yellow-600 bg-yellow-50 border-yellow-200';
        case 'Currently Followed': return 'text-green-600 bg-green-50 border-green-200';
        case 'Discharged': return 'text-orange-600 bg-orange-50 border-orange-200';
        case 'Drop Out': return 'text-gray-600 bg-gray-50 border-gray-200';
        default: return 'text-gray-500 italic';
    }
}

include('../../components/header_component.php'); 
?>

<div class="flex h-screen bg-[#E9F0E9] font-sans">
    <?php include('../../includes/navbar.php'); ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="max-w-5xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-8">My Profile</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                        <div class="relative inline-block mb-4">
                            <?php $img = !empty($profile['User_Image']) ? $profile['User_Image'] : '../../assets/default_user.png'; ?>
                            <img src="<?= htmlspecialchars($img) ?>" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-green-50 shadow-sm">
                        </div>
                        
                        <h2 class="text-xl font-bold text-gray-800 leading-tight">
                            <?= htmlspecialchars($profile['First_Name'] . ' ' . $profile['Last_Name']) ?>
                        </h2>

                        <p class="text-green-600 font-bold text-xs uppercase tracking-widest mt-2 mb-1">
                            Patient
                        </p>

                        <p class="text-gray-500 text-sm mb-6"><?= htmlspecialchars($profile['Email']) ?></p>
                        
                        <a href="edit_profile.php" class="block w-full py-2.5 px-4 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition shadow-sm">
                            Edit Profile
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800 text-lg">Account Details</h3>
                        </div>

                        <div class="text-sm">
                            <div class="grid grid-cols-3 px-6 py-4 bg-white border-b border-gray-50">
                                <div class="font-medium text-gray-500 col-span-1">Full Name</div>
                                <div class="font-semibold text-gray-800 col-span-2">
                                    <?= displayVal($profile['First_Name'] . ' ' . $profile['Last_Name']) ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 px-6 py-4 bg-gray-50 border-b border-gray-50">
                                <div class="font-medium text-gray-500 col-span-1">Age</div>
                                <div class="font-semibold text-gray-800 col-span-2">
                                    <?= displayVal($profile['Age']) ?> Years
                                </div>
                            </div>

                            <div class="grid grid-cols-3 px-6 py-4 bg-white border-b border-gray-50">
                                <div class="font-medium text-gray-500 col-span-1">Current Status</div>
                                <div class="font-semibold col-span-2">
                                    <?php 
                                        $status = $profile['Latest_Status'] ?? 'Not Connected';
                                        $color = getStatusColor($status);
                                    ?>
                                    <span class="px-2 py-1 rounded-md border text-xs uppercase tracking-wider <?= $color ?>">
                                        <?= $status ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 px-6 py-4 bg-gray-50">
                                <div class="font-medium text-gray-500 col-span-1">Treatment Type</div>
                                <div class="font-semibold text-gray-800 col-span-2">
                                    <?= displayVal($profile['Treatment_Type']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="my_professionals.php" class="flex items-center justify-between p-5 rounded-2xl bg-white border border-green-200 shadow-sm hover:shadow-md hover:border-green-300 transition group cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xl">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-lg">My Professionals</h4>
                                <p class="text-sm text-gray-500">View and manage your doctor connections</p>
                            </div>
                        </div>
                        <span class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 group-hover:bg-green-600 group-hover:text-white transition">
                            &rarr;
                        </span>
                    </a>

                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>