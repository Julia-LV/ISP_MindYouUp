const sidebar = document.getElementById('sidebar');
const toggleButton = document.getElementById('toggleButton');
const linkTexts = document.querySelectorAll('.link-text');
const navLinks = document.querySelectorAll('.sidebar-link');
const menuText = document.getElementById('menuText');
const mainWrapper = document.getElementById('main-wrapper');
const overlay = document.getElementById('sidebar-overlay'); // Add this line

// State variable to track if sidebar is open
let isSidebarOpen = false;

// Function to toggle the sidebar
function toggleSidebar() {
    isSidebarOpen = !isSidebarOpen; // Toggle state

    // --- RESPONSIVE LOGIC START ---
    // Desktop Breakpoint (xl in Tailwind is usually 1280px, but let's assume 1024px for tablet boundary)
    const isDesktop = window.innerWidth >= 1024;
    
    if (isSidebarOpen) {
        // --- OPEN SIDEBAR (Wide) ---
        
        // 1. Sidebar Width
        sidebar.classList.remove('w-20');
        sidebar.classList.add('w-64');

        // 2. Main Content Padding
        if (mainWrapper) {
            mainWrapper.classList.remove('md:pl-20');
            mainWrapper.classList.add('md:pl-64');
        }

        // 3. Links Layout (Left Align)
        navLinks.forEach(link => {
            link.classList.remove('justify-center', 'px-0');
            link.classList.add('justify-start', 'px-4', 'gap-4');
        });

        // 4. Show Text Labels
        linkTexts.forEach(text => text.classList.remove('hidden'));
        menuText.classList.remove('hidden');

    } else {
        // --- CLOSE SIDEBAR (Narrow) ---

        // 1. Sidebar Width
        sidebar.classList.add('w-20');
        sidebar.classList.remove('w-64');

        // 2. Main Content Padding
        if (mainWrapper) {
            mainWrapper.classList.add('md:pl-20');
            mainWrapper.classList.remove('md:pl-64');
        }

        // 3. Links Layout (Center Align)
        navLinks.forEach(link => {
            link.classList.add('justify-center', 'px-0');
            link.classList.remove('justify-start', 'px-4', 'gap-4');
        });

        // 4. Hide Text Labels
        linkTexts.forEach(text => text.classList.add('hidden'));
        menuText.classList.add('hidden');
    }
}

// Initialization Function
function initializeSidebar() {
    // Mobile vs Desktop visibility
    if (window.innerWidth >= 768) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
    } else {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
    }

    // Force Closed State on Load/Resize (Default)
    isSidebarOpen = false; 
    
    sidebar.classList.remove('w-64');
    sidebar.classList.add('w-20');

    if (mainWrapper) {
        mainWrapper.classList.remove('md:pl-64');
        mainWrapper.classList.add('md:pl-20');
    }

    navLinks.forEach(link => {
        link.classList.remove('justify-start', 'px-4', 'gap-4');
        link.classList.add('justify-center', 'px-0');
    });

    linkTexts.forEach(text => text.classList.add('hidden'));
    menuText.classList.add('hidden');
}

// Event Listeners
if (toggleButton) {
    toggleButton.addEventListener('click', toggleSidebar);
}

window.addEventListener('load', initializeSidebar);
// Optional: re-initialize on resize to reset layout
window.addEventListener('resize', initializeSidebar);