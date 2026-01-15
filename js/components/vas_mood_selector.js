
(function() {
    const moodData = [
        { score: 1, text: "1. Confident, calm, and relaxed", color: "text-emerald-500", bg: "bg-emerald-500", icon: '<path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>' },
        { score: 2, text: "2. Good, optimistic, and steady", color: "text-teal-500", bg: "bg-teal-500", icon: '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>' },
        { score: 3, text: "3. Generally positive", color: "text-cyan-500", bg: "bg-cyan-500", icon: '<path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/>' },
        { score: 4, text: "4. Mostly okay, slightly unsettled", color: "text-blue-500", bg: "bg-blue-500", icon: '<path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M16 14v6"/><path d="M8 14v6"/><path d="M12 16v6"/>' },
        { score: 5, text: "5. Neutral — neither good nor bad", color: "text-gray-500", bg: "bg-gray-500", icon: '<circle cx="12" cy="12" r="10"/><line x1="8" y1="15" x2="16" y2="15"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>' },
        { score: 6, text: "6. Mildly tense or uneasy", color: "text-yellow-500", bg: "bg-yellow-500", icon: '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>' },
        { score: 7, text: "7. Stressed and worried", color: "text-orange-500", bg: "bg-orange-500", icon: '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>' },
        { score: 8, text: "8. Overwhelmed and drained", color: "text-orange-600", bg: "bg-orange-600", icon: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>' },
        { score: 9, text: "9. Highly anxious or on edge", color: "text-red-500", bg: "bg-red-500", icon: '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>' },
        { score: 10, text: "10. Panic / Out of control", color: "text-rose-600", bg: "bg-rose-600", icon: '<circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>' }
    ];

    const slider = document.getElementById('vas-mood-slider');
    const hiddenInput = document.getElementById('vas-hidden-input');
    
    const scoreDisplay = document.getElementById('vas-score-display');
    const moodText = document.getElementById('vas-mood-text');
    const iconContainer = document.getElementById('vas-icon-container');
    const moodIcon = document.getElementById('vas-mood-icon');
    const scoreBadge = document.getElementById('vas-score-badge');
    const trackFill = document.getElementById('vas-track-fill');

    function updateVAS(val) {
        const index = val - 1;
        const data = moodData[index];

        const cleanText = data.text.split('. ')[1] || data.text;
        hiddenInput.value = cleanText;

        scoreDisplay.innerText = val;
        moodText.innerText = data.text;
        
        moodText.className = `text-center text-sm font-medium h-5 transition-colors duration-300 ${data.color}`;
        scoreBadge.className = `px-3 py-1 rounded-full text-sm font-bold text-white transition-colors duration-300 ${data.bg}`;
        
        iconContainer.style.color = ""; 
        iconContainer.className = `p-3 rounded-full bg-opacity-10 transition-all duration-300 transform ${data.color.replace('text-', 'bg-')} ${data.color}`; 
        moodIcon.innerHTML = data.icon;

        slider.className = `w-full h-2 rounded-lg appearance-none cursor-pointer focus:outline-none z-10 relative bg-transparent ${data.color}`;
        
        trackFill.className = `h-full transition-all duration-150 ${data.bg}`;
        const percentage = ((val - 1) / 9) * 100;
        trackFill.style.width = `${percentage}%`;

        iconContainer.classList.remove('scale-pop');
        void iconContainer.offsetWidth; 
        iconContainer.classList.add('scale-pop');
    }

    if(slider) {
        slider.addEventListener('input', (e) => updateVAS(e.target.value));
        updateVAS(slider.value);
    }
})();
