<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professional') { header("Location: ../auth/login.php"); exit; }
$user_id = $_SESSION['user_id'];

// Fetch ALL Patients NOT connected to this doctor
$sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.User_Image, u.Email, u.Age
        FROM user_profile u 
        WHERE u.Role = 'Patient'
        AND u.User_ID NOT IN (SELECT Patient_ID FROM patient_professional_link WHERE Professional_ID = ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include('../../components/header_component.php'); 
?>

<div class="flex h-screen bg-[#E9F0E9] font-sans">
    <?php include('../../includes/navbar.php'); ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center gap-4 mb-8">
                <a href="my_patients.php" class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-gray-600 hover:bg-gray-50 shadow-sm transition">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-800">Add New Patient</h1>
            </div>

            <div class="space-y-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($patient = $result->fetch_assoc()): ?>
                        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition">
                            <div class="flex items-center gap-4">
                                <img src="<?= htmlspecialchars($patient['User_Image'] ?? '../../assets/default_user.png') ?>" 
                                     class="w-14 h-14 rounded-full object-cover bg-gray-100">
                                <div>
                                    <h3 class="font-bold text-gray-800 text-lg">
                                        <?= htmlspecialchars($patient['First_Name'] . ' ' . $patient['Last_Name']) ?>
                                    </h3>
                                    <p class="text-gray-500 text-sm">
                                        <?= !empty($patient['Age']) ? $patient['Age'] . ' Years Old' : 'Age Not Set' ?>
                                    </p>
                                </div>
                            </div>
                            
                            <form action="add_patient_handler.php" method="POST">
                                <input type="hidden" name="patient_id" value="<?= $patient['User_ID'] ?>">
                                <button type="submit" class="bg-[#F0856C] text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#F26647] transition shadow-md">
                                    Add to List +
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-10 bg-white rounded-xl shadow-sm">No new patients found in the system.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>