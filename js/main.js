/*
 * main.js
 *
 * This is your new global JavaScript file.
 * It will handle the sidebar toggling (hamburger menu).
 */

// This code waits for the page to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Find the toggle button (the hamburger icon)
    const sidebarToggle = document.getElementById('sidebar-toggle');
    
    // Find the sidebar itself
    const sidebar = document.getElementById('main-sidebar');

    // Find the mobile-only overlay
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle && sidebar) {
        // When the hamburger button is clicked...
        sidebarToggle.addEventListener('click', function() {
            // ...toggle the 'hidden' class on the sidebar
            sidebar.classList.toggle('hidden');
            
            // ...also toggle the dark overlay on mobile
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('hidden');
            }
        });
    }

    // Also close the sidebar if the user clicks the overlay
    if (sidebarOverlay && sidebar) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.add('hidden');
            sidebarOverlay.classList.add('hidden');
        });
    }

});