const modal = document.getElementById('videoModal');
const iframe = document.getElementById('modalIframe');
const carousel = document.getElementById('banner-carousel');
const dots = document.querySelectorAll('.dot');

let slideInterval = setInterval(() => scrollBanner('right'), 5000);

function updateDots(activeIndex) {
    dots.forEach((dot, index) => {
        if (index === activeIndex) {
            dot.classList.add('bg-white');
            dot.classList.remove('bg-white/50');
        } else {
            dot.classList.add('bg-white/50');
            dot.classList.remove('bg-white');
        }
    });
}

dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        const slideIndex = parseInt(dot.dataset.slide);
        carousel.scrollTo({ left: slideIndex * carousel.offsetWidth, behavior: 'smooth' });
        updateDots(slideIndex);
        clearInterval(slideInterval);
        slideInterval = setInterval(() => scrollBanner('right'), 5000);
    });
});

carousel.addEventListener('scroll', () => {
    const index = Math.round(carousel.scrollLeft / carousel.offsetWidth);
    updateDots(index);
});

updateDots(0);

function scrollBanner(direction) {
    const scrollAmount = carousel.offsetWidth;
    if (direction === 'right') {
        if (carousel.scrollLeft + scrollAmount >= carousel.scrollWidth) {
            carousel.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    } else {
        if (carousel.scrollLeft <= 0) {
            carousel.scrollTo({ left: carousel.scrollWidth, behavior: 'smooth' });
        } else {
            carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        }
    }
    clearInterval(slideInterval);
    slideInterval = setInterval(() => scrollBanner('right'), 5000);
}

function openItem(item) {
    if (item.type === 'video') {
        let url = item.url;
        if(url.includes('watch?v=')) url = url.replace('watch?v=', 'embed/');
        iframe.src = url + "?autoplay=1";
        modal.classList.add('active');
    } else {
        window.open(item.url, '_blank');
    }
}

function handleResourceClick(url, isVideo) {
    if (isVideo) {
        let embedUrl = url;
        if(url.includes('watch?v=')) embedUrl = url.replace('watch?v=', 'embed/');
        else if(url.includes('youtu.be/')) embedUrl = url.replace('youtu.be/', 'youtube.com/embed/');
        iframe.src = embedUrl + "?autoplay=1";
        modal.classList.add('active');
    } else {
        window.open(url, '_blank');
    }
}

function closeVideo() {
    modal.classList.remove('active');
    iframe.src = "";
}
