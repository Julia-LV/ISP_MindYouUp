<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Patient') { 
    header("Location: ../auth/login.php"); 
    exit; 
}
$user_id = $_SESSION['user_id'];

// SQL: Fetch Professionals + Check Connection Status
$sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.User_Image, p.Specialization, link.Connection_Status
        FROM user_profile u 
        LEFT JOIN professional_profile p ON u.User_ID = p.User_ID
        LEFT JOIN patient_professional_link link 
             ON u.User_ID = link.Professional_ID AND link.Patient_ID = ?
        WHERE u.Role = 'Professional'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include('../../components/header_component.php'); 
?>

<div class="flex min-h-screen bg-[#E9F0E9] font-sans">
    <?php include('../../includes/navbar.php'); ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="max-w-4xl mx-auto">
            
            <div class="flex items-center gap-4 mb-8">
                <a href="my_professionals.php" class="w-10 h-10 flex items-center justify-center rounded-full bg-white text-gray-600 hover:bg-gray-50 shadow-sm transition">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-800">Find a Professional</h1>
            </div>

            <div class="space-y-4">
                <?php while($doc = $result->fetch_assoc()): ?>
                    
                    <?php 
                        $formId = "req_doc_" . $doc['User_ID']; 
                        $name = "Dr. " . htmlspecialchars($doc['First_Name'] . ' ' . $doc['Last_Name']);
                        $connStatus = $doc['Connection_Status']; // Pending, Accepted, or NULL
                    ?>

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition">
                        
                        <div class="flex items-center gap-4">
                            <img src="<?= htmlspecialchars($doc['User_Image'] ?? '../../assets/default_doc.png') ?>" 
                                 class="w-14 h-14 rounded-full object-cover bg-gray-100 border-2 border-white shadow-sm">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">
                                    <?= htmlspecialchars($name) ?>
                                </h3>
                                <p class="text-indigo-600 text-sm font-medium">
                                    <?= htmlspecialchars($doc['Specialization'] ?? 'Medical Professional') ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if ($connStatus === 'Accepted'): ?>
                            <span class="text-green-600 font-medium text-sm px-4 py-2 bg-green-50 rounded-lg">
                                Connected
                            </span>

                        <?php elseif ($connStatus === 'Pending'): ?>
                            <button disabled class="bg-gray-100 text-gray-500 px-5 py-2.5 rounded-lg text-sm font-medium cursor-not-allowed">
                                Request Sent
                            </button>

                        <?php else: ?>
                            <form id="<?= $formId ?>" action="add_professional_handler.php" method="POST">
                                <input type="hidden" name="doctor_id" value="<?= $doc['User_ID'] ?>">
                                <button type="button" 
                                        onclick="confirmAdd('<?= $formId ?>', '<?= $name ?>')" 
                                        class="bg-[#F0856C] text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#F26647] transition shadow-md">
                                    Request Connection
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
    
    <?php include('../../components/modals.php'); ?>
</div>