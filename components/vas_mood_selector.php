<!-- 
  VAS Mood Selector Component (Adapted to Card Style)
  - Matches "diary_mood_card.php" container styling
  - Prevents overflow with compact layout
  - Input name="emotion" (1-10)
-->
<div class="bg-white p-6 rounded-lg shadow-sm w-full">
    
    <!-- Header: Title & Score Badge -->
    <div class="flex justify-between items-center mb-6">
        <label class="block text-lg font-semibold text-gray-900">How are you feeling?</label>
        
        <!-- Dynamic Score Badge -->
        <div id="vas-score-badge" class="px-3 py-1 rounded-full text-sm font-bold text-white bg-gray-400 transition-colors duration-300">
            <span id="vas-score-display">5</span>/10
        </div>
    </div>

    <!-- Content: Icon, Text, Slider -->
    <div class="space-y-6">
        
        <!-- Icon & Description Wrapper -->
        <div class="flex flex-col items-center justify-center space-y-3">
            
            <!-- Icon (Color changes via JS) -->
            <div id="vas-icon-container" class="p-3 rounded-full bg-gray-50 text-gray-400 transition-all duration-300 transform">
                <svg id="vas-mood-icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="8" y1="15" x2="16" y2="15"></line>
                </svg>
            </div>
            
            <!-- Description Text -->
            <p id="vas-mood-text" class="text-center text-sm font-medium text-gray-600 h-5 transition-colors duration-300">
                Neutral
            </p>
        </div>

        <!-- Slider Section -->
        <div class="w-full px-1">
            <div class="relative w-full flex items-center h-8">
                <!-- Input Slider -->
                <input 
                    type="range" 
                    name="emotion" 
                    id="vas-mood-slider" 
                    min="1" 
                    max="10" 
                    step="1" 
                    value="5"
                    style="z-index: 0;"
                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer focus:outline-none relative bg-transparent"
                >
                <!-- Background Track for aesthetics -->
                <div class="absolute top-1/2 left-0 w-full h-2 -mt-1 bg-gray-100 rounded-lg overflow-hidden pointer-events-none">
                    <div id="vas-track-fill" class="h-full w-1/2 bg-gray-300 transition-all duration-150"></div>
                </div>
            </div>

            <!-- Labels -->
            <div class="flex justify-between text-xs text-gray-400 mt-2 font-medium">
                <span>1. Calm</span>
                <span>5. Neutral</span>
                <span>10. Distressed</span>
            </div>
        </div>
    </div>
</div>

<!-- Internal Styles for Webkit Slider Thumb -->
<style>
    /* Custom Thumb Styling */
    #vas-mood-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 24px;
        width: 24px;
        border-radius: 50%;
        background: #ffffff;
        border: 4px solid currentColor; /* Inherits text color from JS */
        cursor: pointer;
        margin-top: -8px; /* Center thumb on track */
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        transition: transform 0.1s, border-color 0.3s;
    }
    #vas-mood-slider::-webkit-slider-thumb:hover {
        transform: scale(1.1);
    }
    /* Remove default track styling to use our custom div */
    #vas-mood-slider::-webkit-slider-runnable-track {
        background: transparent; 
        height: 8px;
    }
    
    /* Animation for Icon pop */
    .scale-pop { animation: pop 0.3s ease-out; }
    @keyframes pop {
        0% { transform: scale(1); }
        50% { transform: scale(1.15); }
        100% { transform: scale(1); }
    }
</style>

<!-- Logic -->
<script>
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
    const scoreDisplay = document.getElementById('vas-score-display');
    const moodText = document.getElementById('vas-mood-text');
    const iconContainer = document.getElementById('vas-icon-container');
    const moodIcon = document.getElementById('vas-mood-icon');
    const scoreBadge = document.getElementById('vas-score-badge');
    const trackFill = document.getElementById('vas-track-fill');

    function updateVAS(val) {
        const index = val - 1;
        const data = moodData[index];

        // Update Text & Score
        scoreDisplay.innerText = val;
        moodText.innerText = data.text;
        
        // Update Colors
        moodText.className = `text-center text-sm font-medium h-5 transition-colors duration-300 ${data.color}`;
        scoreBadge.className = `px-3 py-1 rounded-full text-sm font-bold text-white transition-colors duration-300 ${data.bg}`;
        
        // Update Icon
        // Remove old color class from previous state if needed, but relying on text-color inheritance is safer:
        iconContainer.style.color = ""; // Reset inline
        iconContainer.className = `p-3 rounded-full bg-opacity-10 transition-all duration-300 transform ${data.color.replace('text-', 'bg-')} ${data.color}`; 
        
        moodIcon.innerHTML = data.icon;

        // Update Slider Thumb Color (hack via inline style)
        // We need the hex code or computed color for the border
        // To keep it simple, we apply the text color class to the slider, and the CSS uses currentColor
        slider.className = `w-full h-2 rounded-lg appearance-none cursor-pointer focus:outline-none z-10 relative bg-transparent ${data.color}`;
        
        // Update Track Fill Width & Color
        trackFill.className = `h-full transition-all duration-150 ${data.bg}`;
        const percentage = ((val - 1) / 9) * 100;
        trackFill.style.width = `${percentage}%`;

        // Pop Animation
        iconContainer.classList.remove('scale-pop');
        void iconContainer.offsetWidth; 
        iconContainer.classList.add('scale-pop');
    }

    if(slider) {
        slider.addEventListener('input', (e) => updateVAS(e.target.value));
        // Init
        updateVAS(slider.value);
    }
})();
</script>