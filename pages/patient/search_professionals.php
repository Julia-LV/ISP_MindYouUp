<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }
$user_id = $_SESSION['user_id'];

// Fetch ALL Professionals that are NOT already connected to me
$sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.User_Image, p.Specialization 
        FROM user_profile u 
        LEFT JOIN professional_profile p ON u.User_ID = p.User_ID
        WHERE u.Role = 'Professional'
        AND u.User_ID NOT IN (SELECT Professional_ID FROM patient_professional_link WHERE Patient_ID = ?)";

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
                <a href="my_professionals.php" class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-gray-600 hover:bg-gray-50 shadow-sm">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-800">Find a Professional</h1>
            </div>

            <div class="space-y-4">
                <?php while($doc = $result->fetch_assoc()): ?>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <img src="<?= htmlspecialchars($doc['User_Image'] ?? '../../assets/default_doc.png') ?>" 
                                 class="w-14 h-14 rounded-full object-cover bg-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-800">
                                    Dr. <?= htmlspecialchars($doc['First_Name'] . ' ' . $doc['Last_Name']) ?>
                                </h3>
                                <p class="text-indigo-600 text-sm font-medium">
                                    <?= htmlspecialchars($doc['Specialization'] ?? 'Medical Professional') ?>
                                </p>
                            </div>
                        </div>
                        
                        <form action="add_professional_handler.php" method="POST">
                            <input type="hidden" name="doctor_id" value="<?= $doc['User_ID'] ?>">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-md">
                                Connect +
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
</div>