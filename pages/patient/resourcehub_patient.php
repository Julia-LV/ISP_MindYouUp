<?php
session_start();
require_once __DIR__ . '/../../config.php';

/* ---------- AUTH GUARD ---------- */
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || strtolower($_SESSION['role'] ?? '') !== 'patient') {
    header("Location: ../auth/login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$page_title = 'Resource Hub';

/* ---------- DATABASE FETCHING ---------- */
$prof_id = 0;
$stmt = $conn->prepare("SELECT Professional_ID FROM patient_professional_link WHERE Patient_ID = ? ORDER BY Assigned_Date DESC LIMIT 1");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$stmt->bind_result($prof_id);
$stmt->fetch();
$stmt->close();

$strategies = []; $skills = []; $articles = [];

if ($prof_id > 0) {
    // Aligned with Professional Admin table and column names
    $sql = "SELECT rh.* FROM patient_resource_assignments pr 
            JOIN resource_hub rh ON pr.resource_id = rh.id 
            WHERE pr.patient_id = ? 
            ORDER BY rh.id DESC"; // Showing newest first
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['item_type'] === 'banner') {
            $strategies[] = $row;
        } elseif ($row['item_type'] === 'category') {
            $skills[] = $row;
        } else {
            $articles[] = $row;
        }
    }
    $stmt->close();
}

/* ---------- FALLBACK CONTENT ---------- */
$starter_articles = [
    ['title' => 'Tics and Tic Disorders Infographic', 'media_url' => '../../uploads/starter/flyer.png', 'type' => 'image'],
    ['title' => 'Understanding Tourette Syndrome', 'media_url' => '../../uploads/starter/Understanding_tics.pdf', 'type' => 'pdf'],
    ['title' => 'Relaxation Techniques for Stress Relief', 'media_url' => '../../uploads/starter/Relaxation Techniques for Stress Relief.pdf', 'type' => 'pdf'],
];

// Combine shared articles with starter ones
$display_articles = !empty($articles) ? array_merge($articles, $starter_articles) : $starter_articles;

/* ---------- HARDCODED BANNER DATA (Static) ---------- */
$banner_items = [
    ['title' => 'Deep Breathing', 'desc' => 'Calm your nervous system.', 'type' => 'video', 'url' => 'https://www.youtube.com/embed/aNXKjGFUlMs', 'img' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800'],
    ['title' => 'Overcoming Tics', 'desc' => 'Strategies for managing anxiety-induced symptoms.', 'type' => 'article', 'url' => 'https://tidesmentalhealth.com/how-to-stop-anxiety-induced-tics/', 'img' => 'https://tidesmentalhealth.com/wp-content/uploads/How-to-Stop-Anxiety-Induced-Tics.jpg'],
    ['title' => 'Muscle Relaxation', 'desc' => 'Release physical tension step-by-step.', 'type' => 'video', 'url' => 'https://www.youtube.com/embed/1nZEdqcWqVk', 'img' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800']
];

/* ---------- CATEGORY UI DATA ---------- */
$categories = [
    'competing_behaviours' => [
        'bg' => 'bg-[#F26647]', 
        'img' => 'https://images.unsplash.com/photo-1507413245164-6160d8298b31?auto=format&fit=crop&q=80&w=300', 
        'name' => 'Competing Behaviours'
    ],
    'habit_reversal' => [
        'bg' => 'bg-[#F282A9]', 
        'img' => 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?auto=format&fit=crop&q=80&w=300',
        'name' => 'Habit Reversal'
    ],
    'anxiety_management' => [
        'bg' => 'bg-[#005949]', 
        'img' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&q=80&w=300', 
        'name' => 'Anxiety Management'
    ],
    'pmr_training' => [
        'bg' => 'bg-[#FFB100]', 
        'img' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&q=80&w=300', 
        'name' => 'PMR Training'
    ],
];

include '../../components/header_component.php';
include '../../includes/navbar.php'; 
?>

<script src="https://cdn.tailwindcss.com"></script>
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .video-modal { display: none; background: rgba(0,0,0,0.85); }
    .video-modal.active { display: flex; }
</style>

<div class="w-full min-h-screen overflow-y-auto bg-[#E9F0E9]">
    <div class="p-6 md:p-8 space-y-10 max-w-7xl mx-auto">
        
        <div class="text-left border-b border-gray-200 pb-4">
            <h2 class="text-3xl font-bold text-[#005949] mb-2"><?php echo htmlspecialchars($page_title); ?></h2>
            <p class="text-gray-600 ">Your personalized library.</p>
        </div>

        <section class="relative group/banner">
            <div id="banner-carousel" class="no-scrollbar flex overflow-x-auto snap-x snap-mandatory gap-4">
                <?php foreach ($banner_items as $item): ?>
                    <div class="min-w-full snap-center shrink-0">
                        <div onclick="openItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="relative h-52 md:h-72 w-full rounded-3xl overflow-hidden cursor-pointer shadow-sm group">
                            <img src="<?php echo $item['img']; ?>" class="absolute inset-0 w-full h-full object-cover brightness-50 group-hover:scale-105 transition-transform duration-1000" alt="">
                            <div class="absolute inset-0 p-8 flex flex-col justify-end bg-gradient-to-t from-black/80 via-transparent">
                                <h3 class="text-white text-3xl font-black pl-8"><?php echo $item['title']; ?></h3>
                                <p class="text-white/80 text-sm max-w-lg pl-8"><?php echo $item['desc']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <h3 class="text-xl font-bold text-[#005949] mb-6 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-[#F282A9] rounded-full"></span> Categories
            </h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($categories as $key => $cat): 
                    $uploaded = null;
                    // Look for matches in the new 'category_type' column
                    foreach($skills as $s) { 
                        if(isset($s['category_type']) && $s['category_type'] === $key) {
                            $uploaded = $s; 
                        }
                    }
                ?>
                <a href="<?php echo $uploaded ? htmlspecialchars($uploaded['media_url']) : '#'; ?>" 
                   target="<?php echo $uploaded ? '_blank' : '_self'; ?>"
                   class="group relative aspect-square <?php echo $cat['bg']; ?> rounded-[2.5rem] p-8 flex flex-col items-center justify-between shadow-md hover:-translate-y-2 transition-all overflow-hidden text-center">
                    
                    <div class="absolute inset-0 opacity-10 mix-blend-overlay">
                        <img src="<?php echo $cat['img']; ?>" class="w-full h-full object-cover">
                    </div>

                    <div class="relative w-20 h-20 md:w-24 md:h-24 rounded-full border-4 border-white/20 overflow-hidden shadow-inner z-10">
                        <img src="<?php echo $cat['img']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    
                    <div class="z-10">
                        <h4 class="text-white font-black text-lg leading-tight"><?php echo $cat['name']; ?></h4>
                        <div class="mt-2 inline-flex items-center px-3 py-1 bg-white/10 rounded-full text-white text-[10px] font-bold uppercase tracking-widest">
                            <?php echo $uploaded ? 'View Resource' : 'Locked'; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="pb-12">
            <h3 class="text-xl font-bold text-[#005949] mb-6 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-[#FFB100] rounded-full"></span> Articles & Guides
            </h3>
            <div class="bg-white rounded-3xl border border-[#c7e4d7] overflow-hidden shadow-sm">
                <?php foreach ($display_articles as $art): 
                    $is_image = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $art['media_url']);
                ?>
                    <a href="<?php echo htmlspecialchars($art['media_url']); ?>" target="_blank" class="flex items-center p-6 border-b border-[#E9F0E9] last:border-0 hover:bg-[#F9FBF9] transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-[#E9F0E9] flex items-center justify-center text-[#005949] group-hover:bg-[#005949] group-hover:text-white transition-all">
                            <?php if($is_image): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            <?php endif; ?>
                        </div>
                        <div class="ml-6 flex-1">
                            <h4 class="font-bold text-[#231F20] text-lg"><?php echo htmlspecialchars($art['title']); ?></h4>
                            <p class="text-sm text-gray-400">View Resource</p>
                        </div>
                        <div class="text-[#bcae8c] text-2xl group-hover:translate-x-2 transition-transform">›</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>

<div id="videoModal" class="video-modal fixed inset-0 z-[999] items-center justify-center p-4">
    <div class="relative w-full max-w-4xl aspect-video bg-black rounded-3xl overflow-hidden shadow-2xl">
        <button onclick="closeVideo()" class="absolute top-4 right-4 text-white bg-black/50 w-10 h-10 rounded-full text-2xl z-10">✕</button>
        <iframe id="modalIframe" class="w-full h-full" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
</div>

<script>
    // [Keep your existing JavaScript for Carousel and Modal here]
    // ... 
</script>