<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'patient';

$base_path = ($user_role === 'professional') ? "../../pages/professional/" : "../../pages/patient/";
?>

<nav id="sidebar"
     class="fixed top-0 left-0 h-full bg-[#FCFBF7] shadow-inner
            transform transition-all duration-300 z-[100]
            flex flex-col justify-between
            -translate-x-full md:translate-x-0
            w-20">

    <div class="w-full">
        <div id="toggleButton" class="h-16 flex items-center gap-4 px-4 py-6 border-b border-gray-300 cursor-pointer">  
            <svg id="hamburgerIcon" class="w-8 h-8 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="#005949">
                <path d="M0 96C0 78.3 14.3 64 32 64l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 128C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 288c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32L32 448c-17.7 0-32-14.3-32-32s14.3-32 32-32l384 0c17.7 0 32 14.3 32 32z"/>
            </svg>
            <span id="menuText" class="text-xl text-green-800 font-bold hidden">Menu</span>
        </div>

        <div class="w-full overflow-y-auto">
            <ul class="space-y-3 py-4">
                
                <li>
                    <a href="<?php echo $base_path; ?>home_<?php echo $user_role; ?>.php" 
                       class="sidebar-link flex items-center gap-4 py-2 px-4 rounded-lg transition hover:bg-[#005949] group">
                        <svg class="w-6 h-6 text-gray-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path class="group-hover:fill-white" fill="#005949" d="M240 6.1c9.1-8.2 22.9-8.2 32 0l232 208c9.9 8.8 10.7 24 1.8 33.9s-24 10.7-33.9 1.8l-8-7.2 0 205.3c0 35.3-28.7 64-64 64l-288 0c-35.3 0-64-28.7-64-64l0-205.3-8 7.2c-9.9 8.8-25 8-33.9-1.8s-8-25 1.8-33.9L240 6.1zm16 50.1L96 199.7 96 448c0 8.8 7.2 16 16 16l48 0 0-104c0-39.8 32.2-72 72-72l48 0c39.8 0 72 32.2 72 72l0 104 48 0c8.8 0 16-7.2 16-16l0-248.3-160-143.4zM208 464l96 0 0-104c0-13.3-10.7-24-24-24l-48 0c-13.3 0-24 10.7-24 24l0 104z"/>
                        </svg>
                        <span class="link-text text-green-800 font-medium hidden group-hover:text-white">Home</span>
                    </a>
                </li>

                <?php if ($user_role === 'patient'): ?>
                    <li>
                        <a href="ticlog_motor.php" class="sidebar-link flex items-center gap-4 py-2 px-4 rounded-lg transition hover:bg-[#005949] group">
                            <svg class="w-6 h-6 text-gray-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path class="group-hover:fill-white" fill="#005949" d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM232 344l0-64-64 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l64 0 0-64c0-13.3 10.7-24 24-24s24 10.7 24 24l0 64 64 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-64 0 0 64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"/>
                            </svg>
                            <span class="link-text text-green-800 font-medium hidden group-hover:text-white">Tic Log</span>
                        </a>
                    </li>
                    <li>
                        <a href="new_emotional_diary.php" class="sidebar-link flex items-center gap-4 py-2 px-4 rounded-lg transition hover:bg-[#005949] group">
                            <svg class="w-6 h-6 text-gray-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path class="group-hover:fill-white" fill="#005949" d="M464 256a208 208 0 1 0 -416 0 208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0 256 256 0 1 1 -512 0zm177.3 63.4C192.3 335 218.4 352 256 352s63.7-17 78.7-32.6c9.2-9.6 24.4-9.9 33.9-.7s9.9 24.4 .7 33.9c-22.1 23-60 47.4-113.3 47.4s-91.2-24.4-113.3-47.4c-9.2-9.6-8.9-24.8 .7-33.9s24.8-8.9 33.9 .7zM144 208a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>
                            <span class="link-text text-green-800 font-medium hidden group-hover:text-white">Emotional Diary</span>
                        </a>
                    </li>
                    <li>
                        <a href="../../pages/patient/medication_tracking.php" class="sidebar-link flex items-center gap-4 py-2 px-4 rounded-lg transition hover:bg-[#005949] group">
                            <svg class="w-6 h-6 text-gray-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path class="group-hover:fill-white" fill="#005949" d="M96 112c0-26.5 21.5-48 48-48s48 21.5 48 48l0 112-96 0 0-112zm-64 0l0 288c0 61.9 50.1 112 112 112s112-50.1 112-112l0-105.8 116.3 169.5c35.5 51.7 105.3 64.3 156 28.1s63-107.5 27.5-159.2L427.3 145.3c-35.5-51.7-105.3-64.3-156-28.1-5.6 4-10.7 8.4-15.3 13.1l0-18.3C256 50.1 205.9 0 144 0S32 50.1 32 112zM296.6 240.2c-16-23.3-10-55.3 11.9-71 21.2-15.1 50.5-10.3 66 12.2l67 97.6-79.9 55.9-65-94.8z"/></svg>
                            <span class="link-text text-green-800 font-medium hidden group-hover:text-white">Track Medicines</span>
                        </a>
                    </li>

                <?php else: ?>
                    <li>
                        <a href="../../pages/professional/notes.php" class="sidebar-link flex items-center gap-4 py-2 px-4 rounded-lg transition hover:bg-[#005949] group">
                            <svg class="w-6 h-6 text-gray-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 23">
                                <path class="group-hover:fill-white" fill="#005949"
                                d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"/>
                                <path fill="#005949"
                                d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                             </svg>
                            <span class="link-text text-green-800 font-medium hidden group-hover:text-white"> Notes</span>
                        </a>
                    </li>

                <?php endif; ?>

                <li>
                    <a href="<?php echo $base_path; ?>chat.php" class="sidebar-link flex items-center gap-4 py-2 px-4 rounded-lg transition hover:bg-[#005949] group">
                        <svg class="w-6 h-6 text-gray-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path class="group-hover:fill-white" fill="#005949" d="M76.2 258.7c6.1-15.2 4-32.6-5.6-45.9-14.5-20.1-22.6-43.7-22.6-68.8 0-66.8 60.5-128 144-128s144 61.2 144 128-60.5 128-144 128c-15.9 0-31.1-2.3-45.3-6.5-10.3-3.1-21.4-2.5-31.4 1.5l-50.4 20.2 11.4-28.5zM0 144c0 35.8 11.6 69.1 31.7 96.8L1.9 315.2c-1.3 3.2-1.9 6.6-1.9 10 0 14.8 12 26.8 26.8 26.8 3.4 0 6.8-.7 10-1.9l96.3-38.5c18.6 5.5 38.4 8.4 58.9 8.4 106 0 192-78.8 192-176S298-32 192-32 0 46.8 0 144zM384 512c20.6 0 40.3-3 58.9-8.4l96.3 38.5c3.2 1.3 6.6 1.9 10 1.9 14.8 0 26.8-12 26.8-26.8 0-3.4-.7-6.8-1.9-10l-29.7-74.4c20-27.8 31.7-61.1 31.7-96.8 0-82.4-61.7-151.5-145-170.7-1.6 16.3-5.1 31.9-10.1 46.9 63.9 14.8 107.2 67.3 107.2 123.9 0 25.1-8.1 48.7-22.6 68.8-9.6 13.3-11.7 30.6-5.6 45.9l11.4 28.5-50.4-20.2c-10-4-21.1-4.5-31.4-1.5-14.2 4.2-29.4 6.5-45.3 6.5-72.2 0-127.1-45.7-140.7-101.2-15.6 3.2-31.7 5-48.1 5.2 16.4 81.9 94.7 144 188.8 144z"/></svg>
                        <span class="link-text text-green-800 font-medium hidden group-hover:text-white">Chat</span>
                    </a>
                </li>

                <li>
                    <a href="<?php echo $base_path; ?>resourcehub_<?php echo $user_role; ?>.php" class="sidebar-link flex items-center gap-4 py-2 px-4 rounded-lg transition hover:bg-[#005949] group">
                        <svg class="w-6 h-6 text-gray-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path class="group-hover:fill-white" fill="#005949" d="M168 80c-13.3 0-24 10.7-24 24l0 304c0 8.4-1.4 16.5-4.1 24L440 432c13.3 0 24-10.7 24-24l0-304c0-13.3-10.7-24-24-24L168 80zM72 480c-39.8 0-72-32.2-72-72L0 112C0 98.7 10.7 88 24 88s24 10.7 24 24l0 296c0 13.3 10.7 24 24 24s24-10.7 24-24l0-304c0-39.8 32.2-72 72-72l272 0c39.8 0 72 32.2 72 72l0 304c0 39.8-32.2 72-72 72L72 480zM192 152c0-13.3 10.7-24 24-24l48 0c13.3 0 24 10.7 24 24l0 48c0 13.3-10.7 24-24 24l-48 0c-13.3 0-24-10.7-24-24l0-48zm152 24l48 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-48 0c-13.3 0-24-10.7-24-24s10.7-24 24-24zM216 256l176 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-176 0c-13.3 0-24-10.7-24-24s10.7-24 24-24zm0 80l176 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-176 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/></svg>
                        <span class="link-text text-green-800 font-medium hidden group-hover:text-white">Resource Hub</span>
                    </a>
                </li>

                <li>
                    <a href="<?php echo ($user_role === 'professional') ? $base_path . 'YGTSS_form_professional.php' : '../../pages/patient/YGTSS_form.php'; ?>" 
                       class="sidebar-link flex items-center gap-4 py-2 px-4 rounded-lg transition hover:bg-[#005949] group">
                        <svg class="w-6 h-6 text-gray-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path class="group-hover:fill-white" fill="#005949" d="M64 112c-8.8 0-16 7.2-16 16l0 256c0 8.8 7.2 16 16 16l384 0c8.8 0 16-7.2 16-16l0-256c0-8.8-7.2-16-16-16L64 112zM0 128C0 92.7 28.7 64 64 64l384 0c35.3 0 64 28.7 64 64l0 256c0 35.3-28.7 64-64 64L64 448c-35.3 0-64-28.7-64-64L0 128zM160 320a32 32 0 1 1 -64 0 32 32 0 1 1 64 0zm-32-96a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm104-56l160 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-160 0c-13.3 0-24-10.7-24-24s10.7-24 24-24zm0 128l160 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-160 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/>
                        </svg>
                        <span class="link-text text-green-800 font-medium hidden group-hover:text-white"> Forms</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="w-full mt-auto border-t border-gray-300 py-4">
        <a href="../auth/logout.php" class="sidebar-link flex items-center gap-4 py-2 px-4 rounded-lg transition hover:bg-red-500 group">
            <svg class="w-6 h-6 text-gray-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path class="group-hover:fill-white" fill="black" d="M160 96c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 32C43 32 0 75 0 128L0 384c0 53 43 96 96 96l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l64 0zM502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 192 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128z"/></svg>
            <span class="link-text text-black-800 font-bold hidden group-hover:text-white">Logout</span>
        </a>
    </div>
</nav>

<script src="../../JS/navbar.js"></script>