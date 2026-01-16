<div class="bg-white p-6 rounded-lg shadow-sm w-full">
    
    <input type="hidden" name="emotion" id="vas-hidden-input">

    <div class="flex justify-between items-center mb-6">
        <label class="block text-lg font-semibold text-gray-900">How are you feeling?</label>
        
        <div id="vas-score-badge" class="px-3 py-1 rounded-full text-sm font-bold text-white bg-gray-400 transition-colors duration-300">
            <span id="vas-score-display">5</span>/10
        </div>
    </div>

    <div class="space-y-6">
        
        <div class="flex flex-col items-center justify-center space-y-3">
            
            <div id="vas-icon-container" class="p-3 rounded-full bg-gray-50 text-gray-400 transition-all duration-300 transform">
                <svg id="vas-mood-icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="8" y1="15" x2="16" y2="15"></line>
                </svg>
            </div>
            
            <p id="vas-mood-text" class="text-center text-sm font-medium text-gray-600 h-5 transition-colors duration-300">
                Neutral
            </p>
        </div>

        <div class="w-full px-1">
            <div class="relative w-full flex items-center h-8">
                <input 
                    type="range" 
                    id="vas-mood-slider" 
                    min="1" 
                    max="10" 
                    step="1" 
                    value="5"
                    style="z-index: 20;"
                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer focus:outline-none relative bg-transparent"
                >
                <div class="absolute top-1/2 left-0 w-full h-2 -mt-1 bg-gray-100 rounded-lg overflow-hidden pointer-events-none">
                    <div id="vas-track-fill" class="h-full w-1/2 bg-gray-300 transition-all duration-150"></div>
                </div>
            </div>

            <div class="flex justify-between text-xs text-gray-400 mt-2 font-medium">
                <span>1. Calm</span>
                <span>5. Neutral</span>
                <span>10. Distressed</span>
            </div>
        </div>
    </div>
</div>

<style>
    
    #vas-mood-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 28px;
        width: 28px;
        border-radius: 50%;
        background: #ffffff;
        border: 4px solid currentColor;
        cursor: pointer;
        margin-top: -10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        transition: transform 0.1s, border-color 0.3s;
        position: relative;
        z-index: 30;
    }
    #vas-mood-slider::-webkit-slider-thumb:hover {
        transform: scale(1.1);
    }
    #vas-mood-slider::-webkit-slider-runnable-track {
        background: transparent; 
        height: 8px;
    }
    .scale-pop { animation: pop 0.3s ease-out; }
    @keyframes pop {
        0% { transform: scale(1); }
        50% { transform: scale(1.15); }
        100% { transform: scale(1); }
    }
</style>

<script src="../../js/components/vas_mood_selector.js"></script>