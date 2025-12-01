<?php
/*
 * components/diary_mood_card.php
 *
 * --- UPDATED ---
 * Replaced text emojis with <img> tags.
 *
 * !!! IMPORTANT !!!
 * You MUST update the 'src' path for each image
 * to match your filenames in /assets/img/emojis/
 */
?>
<div class="bg-white p-6 rounded-lg shadow-sm">
    <label class="block text-lg font-semibold text-gray-900 mb-4">How are you feeling?</label>
    <div class="flex justify-between space-x-15 items-start text-center">
        
        <!-- Very Good -->
        <div>
            <input type="radio" name="emotion" value="In contorl" id="mood-5" class="diary-mood-radio">
            <label for="mood-5" class="diary-mood-label" title="In control">
                
                <img src="../../assets/img/emojis/v_new.png" alt="In control" class="w-12 h-12">
            </label>
            <span class="block text-xs text-gray-500 mt-1">1. I feel in control</span>
        </div>
        
        <!-- Good -->
        <div>
            <input type="radio" name="emotion" value="Somewhat in control" id="mood-4" class="diary-mood-radio">
            <label for="mood-4" class="diary-mood-label" title="Somewhat in control">
                <!-- UPDATE THIS PATH: -->
                <img src="../../assets/img/emojis/iv_new.png" alt="Somewhat in control" class="w-12 h-12">
            </label>
            <span class="block text-xs text-gray-500 mt-1">2. I feel somewhat in control</span>
        </div>
        
        <!-- Okay -->
        <div>
            <input type="radio" name="emotion" value="Neutral" id="mood-3" class="diary-mood-radio">
            <label for="mood-3" class="diary-mood-label" title="Neutral">
                <!-- UPDATE THIS PATH: -->
                <img src="../../assets/img/emojis/iii_new.png" alt="Neutral" class="w-12 h-12">
            </label>
            <span class="block text-xs text-gray-500 mt-1">3. I feel neutral</span>
        </div>
        
        <!-- Bad -->
        <div>
            <input type="radio" name="emotion" value="Little control" id="mood-2" class="diary-mood-radio">
            <label for="mood-2" class="diary-mood-label" title="Little control">
                <!-- UPDATE THIS PATH: -->
                <img src="../../assets/img/emojis/ii_new.png" alt="Little control" class="w-12 h-12">
            </label>
            <span class="block text-xs text-gray-500 mt-1">4. I feel with little control</span>
        </div>
        
        <!-- Very Bad -->
        <div>
            <input type="radio" name="emotion" value="Out of control" id="mood-1" class="diary-mood-radio">
            <label for="mood-1" class="diary-mood-label" title="Out of control">
                <!-- UPDATE THIS PATH: -->
                <img src="../../assets/img/emojis/i_new.png" alt="Out of control" class="w-12 h-12">
            </label>
            <span class="block text-xs text-gray-500 mt-1">5. I feel out of control</span>
        </div>
        
    </div>
</div>