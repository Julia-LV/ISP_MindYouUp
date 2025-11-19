<!-- MOBILE TOP BAR -->
<div class="bg-[#FFF7E1] md:hidden w-full shadow-inner">
    <div class="flex justify-start items-center p-4">
        <button onclick="toggleSidebar()" class="text-gray-700 w-8 h-10 block">
            <!-- SVG Hamburger (fornecido por você) -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="#005949">
                <path d="M0 96C0 78.3 14.3 64 32 64l384 0c17.7 0 32 
                14.3 32 32s-14.3 32-32 32L32 128C14.3 128 0 113.7 
                0 96zM0 256c0-17.7 14.3-32 32-32l384 
                0c17.7 0 32 14.3 32 32s-14.3 32-32 
                32L32 288c-17.7 0-32-14.3-32-32zM448 
                416c0 17.7-14.3 32-32 32L32 
                448c-17.7 0-32-14.3-32-32s14.3-32 
                32-32l384 0c17.7 0 32 14.3 32 
                32z"/>
            </svg>
        </button>
        <img src="../../assets/img/MYU logos/MYU_Horizontal Logo.png" alt="Logo" class="w-full h-10 object-contain">
    </div>
</div>



<!-- SIDEBAR -->
<nav id="sidebar"
     class="fixed top-0 left-0 h-full bg-[#FFF7E1] shadow-inner
            transform -translate-x-full md:translate-x-0
            transition-all duration-300
            w-3/4 md:w-20 lg:w-64
            flex flex-col justify-between">

    <div class="w-full">
    <div class=" h-16 flex items-center justify-center py-6 border-b border-grey-100">
        <img src="../../assets/img/MYU logos/MYU_Horizontal Logo.png" alt="Logo Desktop" class="hidden lg:block w-full h-10 object-contain">
        <img src="../../assets/img/MYU logos/MYU_Vertical Logo.png" alt="Logo Tablet" class="hidden md:block lg:hidden w-full h-10 object-contain">
    </div>

    <!-- NAVIGATION -->
    <ul class="mt-6 space-y-2">

        <!-- HOME -->
        <li>
            <a href="dashboard.php"
               class="flex items-center gap-4 px-4 py-2 hover:bg-red-100 rounded-lg transition">

                <!-- Home Icon -->
                <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#005949">
                    <path d="M277.8 8.6c-12.3-11.4-31.3-11.4-43.5 0l-224 208c-9.6 9-12.8 22.9-8 35.1S18.8 272 32 272l16 0
                    0 176c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-176 16 0c13.2 0 25-8.1
                    29.8-20.3s1.6-26.2-8-35.1l-224-208z"/>
                </svg>

                <span class="text-green-800 font-bold hidden md:hidden lg:block">Home</span>
            </a>
        </li>

        <!-- TIC LOGIN -->
        <li>
            <a href="tic-log.php"
               class="flex items-center gap-4 px-4 py-2 hover:bg-red-100 rounded-lg transition">

                <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#005949">
                    <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM232 344l0-64-64 0c-13.3 0-24-10.7-24-24
                    s10.7-24 24-24l64 0 0-64c0-13.3 10.7-24 24-24s24 10.7 24 24l0 64 64 0c13.3 0
                    24 10.7 24 24s-10.7 24-24 24l-64 0 0 64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"/>
                </svg>

                <span class="text-green-800 font-bold hidden md:hidden lg:block">Tic Login</span>
            </a>
        </li>

        <!-- EMOTIONAL DIARY -->
        <li>
            <a href="emotional-diary.php"
               class="flex items-center gap-4 px-4 py-2 hover:bg-red-100 rounded-lg transition">

                <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="#005949">
                    <path d="M311.4 32l8.6 0c35.3 0 64 28.7 64 64l0 352c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 96
                    C0 60.7 28.7 32 64 32l8.6 0C83.6 12.9 104.3 0 128 0L256 0c23.7 0 44.4 12.9 55.4 32z"/>
                </svg>

                <span class="text-green-800 font-bold hidden md:hidden lg:block">Emotional Diary</span>
            </a>
        </li>

        <!-- TRACK MEDICINES -->
        <li>
            <a href="../../pages/patient/medication_tracking.php"
               class="flex items-center gap-4 px-4 py-2 hover:bg-red-100 rounded-lg transition">

                <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="#005949">
                    <path d="M96 112c0-26.5 21.5-48 48-48s48 21.5 48 48l0 112-96 0 0-112zm-64 0l0 288c0 61.9 50.1 112 112 112
                    s112-50.1 112-112l0-105.8 116.3 169.5c35.5 51.7 105.3 64.3 156 28.1s63-107.5
                    27.5-159.2L427.3 145.3c-35.5-51.7-105.3-64.3-156-28.1-5.6 4-10.7 8.4-15.3
                    13.1l0-18.3C256 50.1 205.9 0 144 0S32 50.1 32 112z"/>
                </svg>

                <span class="text-green-800 font-bold hidden md:hidden lg:block">Track Medicines</span>
            </a>
        </li>

        <!-- CHAT -->
        <li>
            <a href="chat.php"
               class="flex items-center gap-4 px-4 py-2 hover:bg-red-100 rounded-lg transition">

                <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="#005949">
                    <path d="M384 144c0 97.2-86 176-192 176-26.7 0-52.1-5-75.2-14L35.2 349.2c-9.3 4.9-20.7 3.2-28.2-4.2s-9.2-18.9-4.2-28.2
                    l35.6-67.2C14.3 220.2 0 183.6 0 144 0 46.8 86-32 192-32S384 46.8 384 144zm0 368
                    c-94.1 0-172.4-62.1-188.8-144 120-1.5 224.3-86.9 235.8-202.7 83.3 19.2 145 88.3
                    145 170.7 0 39.6-14.3 76.2-38.4 105.6l35.6 67.2c4.9 9.3 3.2 20.7-4.2 28.2s-18.9
                    9.2-28.2 4.2L459.2 498c-23.1 9-48.5 14-75.2 14z"/>
                </svg>

                <span class="text-green-800 font-bold hidden md:hidden lg:block">Chat</span>
            </a>
        </li>

        <!-- RESOURCE HUB -->
        <li>
            <a href="../../pages/patient/resourcehub_patient.php"
               class="flex items-center gap-4 px-4 py-2 hover:bg-red-100 rounded-lg transition">

                <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#005949">
                    <path d="M0 416L0 120c0-13.3 10.7-24 24-24s24 10.7 24 24l0 288c0 13.3 10.7 24 24 24s24-10.7 24-24L96 96
                    c0-35.3 28.7-64 64-64l288 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64
                    480c-35.3 0-64-28.7-64-64z"/>
                </svg>

                <span class="text-green-800 font-bold hidden md:hidden lg:block">Resource Hub</span>
            </a>
        </li>
    </ul>
    </div>

    <div class="w-full">
        <div class="w-full border-t border-red-300 mb-2"> 
        <ul class="space-y-2 mb-4">

            <!-- PROFILE -->
            <li>
                <a href="../../pages/patient/patient_profile.php"
                class="flex items-center gap-4 px-4 py-2 hover:bg-gray-200 rounded-lg transition">

                    <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="black">
                        <path d="M224 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56C95.8 304 16 383.8 16 482.3 16 498.7
                        29.3 512 45.7 512l356.6 0c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3l-59.4 0z"/>
                    </svg>

                    <span class="text-black-800 font-medium hidden md:hidden lg:block">Profile</span>
                </a>
            </li>

            <!-- SETTINGS -->
            <li>
                <a href="../../pages/common/settings.php"
                class="flex items-center gap-4 px-4 py-2 hover:bg-gray-200 rounded-lg transition">

                    <svg class="w-6 h-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="black">
                        <path d="M195.1 9.5C198.1-5.3 211.2-16 226.4-16l59.8 0c15.2 0 28.3 10.7 31.3 25.5L332 79.5c14.1 6 27.3
                        13.7 39.3 22.8l67.8-22.5c14.4-4.8 30.2 1.2 37.8 14.4l29.9 51.8c7.6 13.2 4.9
                        29.8-6.5 39.9L447 233.3c.9 7.4 1.3 15 1.3 22.7s-.5 15.3-1.3 22.7l53.4 47.5c11.4
                        10.1 14 26.8 6.5 39.9l-29.9 51.8c-7.6 13.1-23.4 19.2-37.8 14.4l-67.8-22.5c-12.1
                        9.1-25.3 16.7-39.3 22.8l-14.4 69.9c-3.1 14.9-16.2 25.5-31.3 25.5l-59.8 0c-15.2
                        0-28.3-10.7-31.3-25.5l-14.4-69.9c-14.1-6-27.2-13.7-39.3-22.8L73.5 432.3c-14.4
                        4.8-30.2-1.2-37.8-14.4L5.8 366.1c-7.6-13.2-4.9-29.8 6.5-39.9l53.4-47.5c-.9-7.4
                        -1.3-15-1.3-22.7s.5-15.3 1.3-22.7L12.3 185.8c-11.4-10.1-14-26.8-6.5-39.9L35.7
                        94.1c7.6-13.2 23.4-19.2 37.8-14.4l67.8 22.5c12.1-9.1 25.3-16.7 39.3-22.8L195.1
                        9.5zM256.3 336a80 80 0 1 0 -.6-160 80 80 0 1 0 .6 160z"/>
                    </svg>

                    <span class="text-black-800 font-medium hidden md:hidden lg:block">Settings</span>
                </a>
            </li>
        </ul>
        </div>
    </div>
</nav>
