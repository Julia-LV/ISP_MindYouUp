<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch accepted Professionals
$sql = "SELECT link.Link_ID, u.User_ID, u.First_Name, u.Last_Name, u.User_Image, link.Status, link.Assigned_Date
        FROM patient_professional_link link
        JOIN user_profile u ON link.Professional_ID = u.User_ID
        WHERE link.Patient_ID = ? 
        AND link.Connection_Status = 'Accepted'";

if (!$conn) {
    die("Database connection failed");
}
        
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include('../../components/header_component.php'); 
?>

<div class="flex h-screen bg-[#E9F0E9] font-sans">
    <?php include('../../includes/navbar.php'); ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="max-w-5xl mx-auto">
            
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">My Professionals</h1>
                    <p class="text-gray-500 text-sm">Doctors and therapists you are connected with.</p>
                </div>
                
                <a href="search_professionals.php" class="flex items-center gap-2 bg-[#F0856C] text-white px-5 py-2.5 rounded-xl shadow-md hover:bg-[#F26647] transition">
                    <span class="text-xl font-bold">+</span> 
                    <span class="font-medium">Add New</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($doc = $result->fetch_assoc()): ?>
                        
                        <?php 
                            // FIXED: Used $doc instead of $patient
                            $formId = "del_doc_" . $doc['Link_ID']; 
                            $docName = htmlspecialchars($doc['First_Name'] . ' ' . $doc['Last_Name']);
                        ?>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center text-center relative hover:shadow-md transition">
                            
                            <div class="absolute top-4 right-4">
                                <form id="<?= $formId ?>" action="../common/delete_handler.php" method="POST">
                                    <input type="hidden" name="link_id" value="<?= $doc['Link_ID'] ?>">
                                    
                                    <button type="button" 
                                            onclick="confirmDelete('<?= $formId ?>', 'Dr. <?= $docName ?>')"
                                            class="text-gray-300 hover:text-red-500 transition p-1" title="Remove">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <img src="<?= htmlspecialchars($doc['User_Image'] ?? '../../assets/default_doc.png') ?>" 
                                 class="w-20 h-20 rounded-full object-cover mb-4 border-4 border-indigo-50">
                            
                            <h3 class="font-bold text-gray-800 text-lg">
                                Dr. <?= $docName ?>
                            </h3>
                            
                            <p class="text-[#F26647] text-sm font-medium mb-1">
                                <?= htmlspecialchars($doc['Specialization'] ?? 'General Practitioner') ?>
                            </p>
                            
                            <p class="text-gray-400 text-xs mb-6">
                                Connected since <?= date('M Y', strtotime($doc['Assigned_Date'])) ?>
                            </p>

                            <a href="chat.php?link_id=<?= $doc['Link_ID'] ?>" 
                               class="w-full py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition">
                                Send Message
                            </a>

                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full py-16 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">No Professionals Yet</h3>
                        <p class="text-gray-500 mb-6">Click the "Add New" button to find a doctor.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
    
    <?php include('../../components/modals.php'); ?>
</div>