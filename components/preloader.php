<style>
    /* PRELOADER: Completely centered and static */
    #preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: #E9F0E9; 
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.4s ease;
    }

    .preloader-hidden {
        opacity: 0;
        pointer-events: none;
        visibility: hidden;
    }

    #preloader img {
        width: 220px; 
        height: auto;
        opacity: 0.55;
    }
</style>

<div id="preloader">
    <img src="../../assets/img/MYU logos/preloader.png" alt="Loading...">
</div>

<script>
<<<<<<< HEAD
    const loader = document.getElementById('preloader');

    function hidePreloader() {
=======
    window.addEventListener('load', function() {
        const loader = document.getElementById('preloader');
>>>>>>> parent of 53e2d28 (Merge branch 'main' of https://github.com/Julia-LV/ISP_MindYouUp)
        if (loader) {
            loader.classList.add('preloader-hidden');
        }
    });

<<<<<<< HEAD
    // 1. Standard Load
    window.addEventListener('load', hidePreloader);

    // 2. Fix for Back/Forward Button (bfcache)
    window.addEventListener('pageshow', (event) => {
        hidePreloader();
    });

    // 3. Safety Timeout (Reduced to 3s for better UX)
    setTimeout(hidePreloader, 3000);

    // 4. Refined Click Listener
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a');
        
        if (target && target.href &&
            target.href.includes(window.location.origin) &&
            !target.getAttribute('target') &&
            !target.href.includes('#') &&
            !target.hasAttribute('download') && 
            target.tagName === 'A' &&
            !e.defaultPrevented) { 

            // Only show if it's a left click and not holding Ctrl/Cmd
            if (e.button === 0 && !e.ctrlKey && !e.metaKey) {
                if (loader) {
                    loader.classList.remove('preloader-hidden');
                }
=======
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a');
        if (target && target.href && target.href.includes(window.location.origin) && !target.getAttribute('target') && !target.href.includes('#')) {
            const loader = document.getElementById('preloader');
            if (loader) {
                loader.classList.remove('preloader-hidden');
>>>>>>> parent of 53e2d28 (Merge branch 'main' of https://github.com/Julia-LV/ISP_MindYouUp)
            }
        }
    });
</script>