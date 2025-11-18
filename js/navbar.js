const mobileBtn = document.getElementById('mobileMenuBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

mobileBtn.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
});

// Fechar ao clicar no overlay
overlay.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
});