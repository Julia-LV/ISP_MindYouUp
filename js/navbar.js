document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('toggleButton');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const menuText = document.getElementById('menuText');
    const linkTexts = document.querySelectorAll('.link-text');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    
    let isExpanded = false;
    
    // Expand Function
    function expandSidebar() {
        sidebar.classList.remove('w-20', '-translate-x-full');
        sidebar.classList.add('w-64', 'translate-x-0');
        
        linkTexts.forEach(text => text.classList.remove('hidden'));
        if (menuText) menuText.classList.remove('hidden');
        
        if (window.innerWidth < 768) {
            if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');
        }
        
        isExpanded = true;
        localStorage.setItem('sidebarExpanded', 'true');
    }
    
    // Colapsar Function
    function collapseSidebar() {
        sidebar.classList.remove('w-64');
        sidebar.classList.add('w-20');
        
        linkTexts.forEach(text => text.classList.add('hidden'));
        if (menuText) menuText.classList.add('hidden');
        
        if (window.innerWidth < 768) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        }
        
        isExpanded = false;
        localStorage.setItem('sidebarExpanded', 'false');
    }
    
    function toggleSidebar() {
        if (isExpanded) {
            collapseSidebar();
        } else {
            expandSidebar();
        }
    }

    // EVENT LISTENERS
    if (toggleButton) {
        toggleButton.addEventListener('click', toggleSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', collapseSidebar);
    }
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            expandSidebar();
        });
    }


    function initSidebar() {
        const savedState = localStorage.getItem('sidebarExpanded');
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full');
            if (savedState === 'true') expandSidebar();
            else collapseSidebar();
        } else {
            collapseSidebar(); 
        }
    }

    function highlightActivePage() {
        const currentPath = window.location.pathname;
        sidebarLinks.forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref) {
                const linkFileName = linkHref.split('/').pop();
                const currentFileName = currentPath.split('/').pop();
                if (linkFileName === currentFileName) {
                    link.classList.add('active', 'bg-[#005949]');
                    const icon = link.querySelector('svg');
                    const text = link.querySelector('.link-text');
                    const path = link.querySelector('svg path');
                    if (icon) icon.classList.add('text-white');
                    if (text) text.classList.add('text-white');
                    if (path) path.classList.add('fill-white');
                }
            }
        });
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        } else {
            if (!isExpanded) sidebar.classList.add('-translate-x-full');
        }
    });

    initSidebar();
    highlightActivePage();
});