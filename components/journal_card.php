<?php
/*
 * components/diary_journal_card.php
 *
 * A reusable Journal Card.
 * * Variables you can pass:
 * $journal_title       : The label text (Default: "Journal Entry")
 * $journal_placeholder : The placeholder text (Default: "Write any thought...")
 * $journal_rows        : Height of the box (Default: 8)
 */

// Set Defaults (So it still works for Emotional Diary without changes)
$journal_title = $journal_title ?? 'Journal Entry';
$journal_placeholder = $journal_placeholder ?? 'Write any thought or detail about your mood...';
$journal_rows = $journal_rows ?? 8;
?>

<div class="bg-white p-6 rounded-lg shadow-sm">
    <label for="notes" class="block text-lg font-semibold text-gray-900 mb-4">
        <?php echo htmlspecialchars($journal_title); ?>
    </label>
    
    <textarea 
        id="notes" 
        name="notes" 
        rows="<?php echo $journal_rows; ?>" 
        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-[#005949]"
        placeholder="<?php echo htmlspecialchars($journal_placeholder); ?>"
    ></textarea>
</div>