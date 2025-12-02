<?php
// pages/professional/contact_professional.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../../config.php');

// --- ACCESS CONTROL --- //
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'Professional') {
    header("Location: ../auth/login.php");
    exit;
}

$currentProfessional = $_SESSION['user_id']; // Professional User_ID

// --- FETCH LINKED PATIENTS AND LAST MESSAGE ---
// Query ajustada para incluir u.User_Image e usar a lógica de mensagens consistente
$sql = "
SELECT 
    ppl.Patient_ID AS Patient_User_ID,
    CONCAT(u.First_Name, ' ', u.Last_Name) AS Name,
    u.User_Image,
    MAX(cl.Chat_Time) AS Last_Message_Time,
    SUBSTRING_INDEX(
        GROUP_CONCAT(cl.Chat_Text ORDER BY cl.Chat_Time DESC SEPARATOR '||'),
        '||', 1
    ) AS Last_Message
FROM patient_professional_link ppl
JOIN user_profile u ON ppl.Patient_ID = u.User_ID
LEFT JOIN chat_log cl 
    ON (
        (cl.Sender = ? AND cl.Receiver = u.User_ID)
        OR 
        (cl.Sender = u.User_ID AND cl.Receiver = ?)
    )
WHERE ppl.Professional_ID = ?
GROUP BY ppl.Patient_ID
ORDER BY Last_Message_Time DESC;
";

$stmt = $conn->prepare($sql);
// Bind params: CurrentProf (Sender), CurrentProf (Receiver), CurrentProf (Link Check)
$stmt->bind_param("iii", $currentProfessional, $currentProfessional, $currentProfessional);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<title>Your Patients – Messages</title>

</head>
<body class="bg-[#E9F0E9] min-h-screen flex flex-col font-sans">

    <div class="flex-shrink-0">
        <?php include '../../includes/navbar.php'; ?>
    </div>

    <div class="flex-grow w-full max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        <h2 class="text-2xl md:text-3xl font-bold text-[#005949] mb-6">Your Patients</h2>

        <div class="space-y-4">

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>

                    <?php
                        $patient_id = $row['Patient_User_ID'];
                        $name       = $row['Name'];
                        
                        // Lógica da Imagem de Perfil
                        $db_image_path = $row['User_Image'];
                        $default_image = 'https://via.placeholder.com/150';
                        $profile_image = $default_image;

                        if (!empty($db_image_path)) {
                            // Ajustar caminho relativo (../../) se começar com /
                            if (substr($db_image_path, 0, 1) === '/') {
                                $profile_image = '../..' . $db_image_path;
                            } else {
                                $profile_image = '../../' . $db_image_path;
                            }
                        }
                        
                        // Cache buster
                        $final_image_src = htmlspecialchars($profile_image) . "?" . time();

                        // Texto e Data
                        $preview = $row['Last_Message'] 
                                    ? substr($row['Last_Message'], 0, 60) . (strlen($row['Last_Message']) > 60 ? "..." : "")
                                    : "No messages yet";
                        
                        $timestamp = $row['Last_Message_Time']
                                    ? date("M d, H:i", strtotime($row['Last_Message_Time']))
                                    : "";
                        
                        $textColor = $row['Last_Message'] ? "text-gray-600" : "text-gray-400 italic";
                    ?>

                    <div onclick="openChat(<?php echo $patient_id; ?>)" 
                         class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer flex items-center border border-gray-100">
                        
                        <div class="flex-shrink-0 mr-4">
                            <img class="h-12 w-12 rounded-full object-cover border-2 border-[#005949]" 
                                 src="<?php echo $final_image_src; ?>" 
                                 alt="<?php echo htmlspecialchars($name); ?>">
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline mb-1">
                                <h3 class="text-lg font-semibold text-gray-900 truncate pr-2">
                                    <?php echo htmlspecialchars($name); ?>
                                </h3>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    <?php echo htmlspecialchars($timestamp); ?>
                                </span>
                            </div>
                            <p class="text-sm <?php echo $textColor; ?> truncate">
                                <?php echo htmlspecialchars($preview); ?>
                            </p>
                        </div>

                        <div class="ml-2 text-gray-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                    <?php endwhile; ?>
            <?php else: ?>
                
                <div class="text-center py-10 bg-white rounded-xl shadow-sm">
                    <p class="text-gray-500 text-lg">No linked patients found.</p>
                    <p class="text-gray-400 text-sm mt-1">Go to your profile to link a new patient.</p>
                </div>

            <?php endif; ?>

        </div>
    </div>

<script>
// Redireciona para o chat passando o ID do paciente
function openChat(patientUserID) {
    window.location.href = "chat.php?patient_user_id=" + patientUserID;
}
</script>

</body>
</html>