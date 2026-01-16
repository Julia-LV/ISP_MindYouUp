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
    window.addEventListener('load', function() {
        const loader = document.getElementById('preloader');
        if (loader) {
            loader.classList.add('preloader-hidden');
        }
    });

    document.addEventListener('click', function(e) {
        const target = e.target.closest('a');
        if (target && target.href && target.href.includes(window.location.origin) && !target.getAttribute('target') && !target.href.includes('#')) {
            const loader = document.getElementById('preloader');
            if (loader) {
                loader.classList.remove('preloader-hidden');
            }
        }
    });
</script>