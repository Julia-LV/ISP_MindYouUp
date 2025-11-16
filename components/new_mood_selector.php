<?php
/*
 * components/diary_mood_card.php
 *
 * This component is the full-width "How are you feeling?" card
 * with the 5 emojis. It uses the styles from new_diary.css.
 */
?>
<div class="bg-white p-6 rounded-lg shadow-sm">
    <label class="block text-lg font-semibold text-gray-900 mb-4">How are you feeling?</label>
    <div class="flex justify-between items-start text-center">
        <!-- Very Good -->
        <div>
            <input type="radio" name="emotion" value="Very Good" id="mood-5" class="diary-mood-radio">
            <label for="mood-5" class="diary-mood-label" title="Very Good">😀</label>
            <span class="block text-xs text-gray-500 mt-1">Very Good</span>
        </div>
        <!-- Good -->
        <div>
            <input type="radio" name="emotion" value="Good" id="mood-4" class="diary-mood-radio">
            <label for="mood-4" class="diary-mood-label" title="Good">🙂</label>
            <span class="block text-xs text-gray-500 mt-1">Good</span>
        </div>
        <!-- Okay -->
        <div>
            <input type="radio" name="emotion" value="Okay" id="mood-3" class="diary-mood-radio">
            <label for="mood-3" class="diary-mood-label" title="Okay">😐</label>
            <span class="block text-xs text-gray-500 mt-1">Okay</span>
        </div>
        <!-- Bad -->
        <div>
            <input type="radio" name="emotion" value="Bad" id="mood-2" class="diary-mood-radio">
            <label for="mood-2" class="diary-mood-label" title="Bad">🙁</label>
            <span class="block text-xs text-gray-500 mt-1">Bad</span>
        </div>
        <!-- Very Bad -->
        <div>
            <input type="radio" name="emotion" value="Very Bad" id="mood-1" class="diary-mood-radio">
            <label for="mood-1" class="diary-mood-label" title="Very Bad">😢</label>
            <span class="block text-xs text-gray-500 mt-1">Very Bad</span>
        </div>
    </div>
</div>