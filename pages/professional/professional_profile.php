<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch Data
$sql = "SELECT u.First_Name, u.Last_Name, u.Email, u.User_Image, u.Age,
               p.Specialization 
        FROM user_profile u 
        LEFT JOIN professional_profile p ON u.User_ID = p.User_ID 
        WHERE u.User_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

function displayVal($val) {
    return !empty($val) ? htmlspecialchars($val) : '<span class="text-gray-400 italic">Not Set</span>';
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
                            <?php $img = !empty($profile['User_Image']) ? $profile['User_Image'] : '../../assets/default_doc.png'; ?>
                            <img src="<?= htmlspecialchars($img) ?>" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-indigo-50 shadow-sm">
                        </div>
                        
                        <h2 class="text-xl font-bold text-gray-800 leading-tight">
                            Dr. <?= htmlspecialchars($profile['First_Name'] . ' ' . $profile['Last_Name']) ?>
                        </h2>
                        
                        <p class="text-[#F26647] font-bold uppercase text-xs tracking-widest mt-2 mb-1">
                            Professional
                        </p>

                        <p class="text-gray-500 text-sm mb-6"><?= htmlspecialchars($profile['Email']) ?></p>

                        <a href="edit_professional_profile.php" 
                           class="block w-full py-2.5 px-4 bg-[#F0856C] border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-[#F26647] transition shadow-sm">
                           Edit Profile
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800 text-lg">Professional Details</h3>
                        </div>

                        <div class="text-sm">
                            <div class="grid grid-cols-3 px-6 py-4 bg-white border-b border-gray-50">
                                <div class="font-medium text-gray-500 col-span-1">Full Name</div>
                                <div class="font-semibold text-gray-800 col-span-2">
                                    Dr. <?= displayVal($profile['First_Name'] . ' ' . $profile['Last_Name']) ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 px-6 py-4 bg-gray-50 border-b border-gray-50">
                                <div class="font-medium text-gray-500 col-span-1">Age</div>
                                <div class="font-semibold text-gray-800 col-span-2">
                                    <?= displayVal($profile['Age']) ?> Years Old
                                </div>
                            </div>

                            <div class="grid grid-cols-3 px-6 py-4 bg-white">
                                <div class="font-medium text-gray-500 col-span-1">Specialization</div>
                                <div class="font-semibold text-gray-800 col-span-2">
                                    <?= displayVal($profile['Specialization']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="my_patients.php" class="flex items-center justify-between p-5 rounded-2xl bg-white border border-green-200 shadow-sm hover:shadow-md hover:border-green-300 transition group cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class=" text-indigo-600 flex items-center justify-center text-xl">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-lg">My Patients</h4>
                                <p class="text-sm text-gray-500">View and manage your patient connections</p>
                            </div>
                        </div>
                        <span class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 group-hover:bg-[#F0856C] group-hover:text-white transition">
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