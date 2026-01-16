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
$selected_cat = $_GET['cat'] ?? null;

/* ---------- DATABASE FETCHING ---------- */
$prof_id = 0;
$stmt = $conn->prepare("SELECT Professional_ID FROM patient_professional_link WHERE Patient_ID = ? ORDER BY Assigned_Date DESC LIMIT 1");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$stmt->bind_result($prof_id);
$stmt->fetch();
$stmt->close();

$skills_grouped = []; $articles = []; $db_banners = [];

if ($prof_id > 0) {
    $sql = "SELECT rh.* FROM patient_resource_assignments pr 
            JOIN resource_hub rh ON pr.resource_id = rh.id 
            WHERE pr.patient_id = ? 
            ORDER BY rh.id DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['item_type'] === 'category') {
            $skills_grouped[$row['category_type']][] = $row;
        } elseif ($row['item_type'] === 'article') {
            $articles[] = $row;
        } elseif ($row['item_type'] === 'banner') {
            $db_banners[] = [
                'title' => $row['title'],
                'desc'  => $row['subtitle'],
                'type'  => $row['banner_content_type'] ?? 'article',
                'url'   => $row['media_url'],
                'img'   => !empty($row['image_url']) ? $row['image_url'] : 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800'
            ];
        }
    }
    $stmt->close();
}

/* ---------- FALLBACKS ---------- */
$starter_articles = [
    ['title' => 'Tics and Tic Disorders Infographic', 'media_url' => '../../uploads/starter/flyer.png', 'type' => 'image'],
    ['title' => 'Understanding Tourette Syndrome', 'media_url' => '../../uploads/starter/Understanding_tics.pdf', 'type' => 'pdf'],
];
$display_articles = !empty($articles) ? array_merge($articles, $starter_articles) : $starter_articles;

$default_banners = [
    ['title' => 'Deep Breathing', 'desc' => 'Calm your nervous system.', 'type' => 'video', 'url' => 'https://www.youtube.com/embed/aNXKjGFUlMs', 'img' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800'],
    ['title' => 'Muscle Relaxation', 'desc' => 'Release physical tension.', 'type' => 'video', 'url' => 'https://www.youtube.com/embed/1nZEdqcWqVk', 'img' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800']
];
$banner_items = !empty($db_banners) ? $db_banners : $default_banners;

$categories = [
    'competing_behaviours' => ['bg' => 'bg-[#F26647]', 'img' => 'https://images.unsplash.com/photo-1507413245164-6160d8298b31?w=300', 'name' => 'Competing Behaviours'],
    'habit_reversal'       => ['bg' => 'bg-[#F282A9]', 'img' => 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=300', 'name' => 'Habit Reversal'],
    'anxiety_management'   => ['bg' => 'bg-[#005949]', 'img' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=300', 'name' => 'Anxiety Management'],
    'pmr_training'         => ['bg' => 'bg-[#FFB100]', 'img' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=300', 'name' => 'PMR Training'],
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
    #banner-carousel { scroll-behavior: smooth; }
</style>

<div class="w-full min-h-screen overflow-y-auto bg-[#E9F0E9]">
    <div class="p-6 md:p-8 space-y-10 max-w-7xl mx-auto">

        <?php if (!$selected_cat): ?>
            <div class="text-left border-b border-gray-200 pb-4">
                <h2 class="text-3xl font-bold text-[#005949] mb-2"><?php echo htmlspecialchars($page_title); ?></h2>
                <p class="text-gray-600">Your personalized library.</p>
            </div>

            <section class="relative group/container">
                <div id="banner-carousel" class="no-scrollbar flex overflow-x-auto snap-x snap-mandatory gap-0 rounded-3xl shadow-lg">
                    <?php foreach ($banner_items as $index => $item): ?>
                        <div class="min-w-full snap-center shrink-0 relative h-64 md:h-[320px] overflow-hidden group">
                            <img src="<?php echo $item['img']; ?>" class="absolute inset-0 w-full h-full object-cover brightness-[0.45]">
                            
                            <div class="absolute bottom-8 left-12 md:bottom-10 md:left-16 z-10 flex flex-col items-start">
                                <h3 class="text-white text-3xl md:text-4xl font-black mb-1">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <p class="text-white/90 text-base md:text-lg mb-5 font-medium max-w-lg">
                                    <?php echo htmlspecialchars($item['desc']); ?>
                                </p>
                                
                                <button onclick="openItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" 
                                        class="px-8 py-2.5 bg-white text-[#005949] font-bold rounded-full flex items-center gap-2 hover:bg-[#B0D1B8] hover:text-white transition-all transform hover:scale-105 shadow-xl">
                                    <?php if($item['type'] === 'video'): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg> Watch Video
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg> Read Article
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex justify-center space-x-2 mt-4">
                    <?php for($i = 0; $i < count($banner_items); $i++): ?>
                        <button class="dot w-3 h-3 rounded-full bg-white/50 hover:bg-white/80 transition-colors" data-slide="<?php echo $i; ?>"></button>
                    <?php endfor; ?>
                </div>

                <button onclick="scrollBanner('left')" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-full text-white flex items-center justify-center opacity-0 group-hover/container:opacity-100 transition-opacity z-20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button onclick="scrollBanner('right')" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-full text-white flex items-center justify-center opacity-0 group-hover/container:opacity-100 transition-opacity z-20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </section>

            <section>
                <h3 class="text-xl font-bold text-[#005949] mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-[#F282A9] rounded-full"></span> Categories
                </h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($categories as $key => $cat): ?>
                    <a href="?cat=<?php echo $key; ?>" 
                       class="group relative aspect-square <?php echo $cat['bg']; ?> rounded-[2.5rem] p-8 flex flex-col items-center justify-between shadow-md hover:-translate-y-2 transition-all overflow-hidden text-center">
                        <div class="absolute inset-0 opacity-10 mix-blend-overlay">
                            <img src="<?php echo $cat['img']; ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="relative w-20 h-20 md:w-24 md:h-24 rounded-full border-4 border-white/20 overflow-hidden shadow-inner z-10">
                            <img src="<?php echo $cat['img']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <div class="z-10">
                            <h4 class="text-white font-black text-lg leading-tight"><?php echo $cat['name']; ?></h4>
                            <div class="mt-2 inline-flex items-center px-4 py-1 bg-white/20 rounded-full text-white text-[10px] font-bold uppercase tracking-widest">Open Folder</div>
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

        <?php else: ?>
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-[#005949]"><?php echo $categories[$selected_cat]['name'] ?? 'Resources'; ?></h2>
                    <p class="text-gray-600">Showing shared resources in this category.</p>
                </div>
                <a href="resourcehub_patient.php" class="bg-white px-6 py-2 rounded-full border border-gray-200 font-bold text-[#005949] shadow-sm hover:bg-gray-50 transition-all flex items-center gap-2">
                    ← Back to Hub
                </a>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-[#c7e4d7] overflow-hidden shadow-lg min-h-[400px]">
                <?php 
                $folder_items = $skills_grouped[$selected_cat] ?? [];
                if (empty($folder_items)): ?>
                    <div class="p-20 text-center">
                        <div class="bg-[#E9F0E9] w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 text-[#005949]">
                             <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#005949]">No resources yet</h3>
                        <p class="text-gray-600 mt-2">Your professional has not shared any resources in this category.</p>
                    </div>
                <?php else: foreach ($folder_items as $f_item): 
                    $target_url = !empty($f_item['media_url']) ? $f_item['media_url'] : $f_item['content'];
                    $is_video = (strpos($target_url, 'youtube') !== false || strpos($target_url, 'youtu.be') !== false);
                ?>
                    <div onclick="handleResourceClick('<?php echo $target_url; ?>', <?php echo $is_video ? 'true' : 'false'; ?>)" 
                       class="flex items-center p-8 border-b border-[#E9F0E9] last:border-0 hover:bg-[#F9FBF9] transition-all group cursor-pointer">
                        <div class="w-14 h-14 rounded-2xl bg-[#E9F0E9] flex items-center justify-center text-[#005949] group-hover:bg-[#005949] group-hover:text-white transition-all shadow-sm">
                            <?php if($is_video): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            <?php endif; ?>
                        </div>
                        <div class="ml-8 flex-1">
                            <h4 class="font-bold text-[#231F20] text-xl"><?php echo htmlspecialchars($f_item['title']); ?></h4>
                            <p class="text-sm text-gray-400 mt-1"><?php echo $is_video ? 'Watch Video Resource' : 'View Shared Document'; ?></p>
                        </div>
                        <div class="text-[#bcae8c] text-3xl group-hover:translate-x-2 transition-transform pr-4">›</div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<div id="videoModal" class="video-modal fixed inset-0 z-[999] items-center justify-center p-4">
    <div class="relative w-full max-w-4xl aspect-video bg-black rounded-3xl overflow-hidden shadow-2xl">
        <button onclick="closeVideo()" class="absolute top-4 right-4 text-white bg-black/50 w-10 h-10 rounded-full text-2xl z-10">✕</button>
        <iframe id="modalIframe" class="w-full h-full" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
</div>

<script src="../../js/patient/resourcehub_patient.js"></script>