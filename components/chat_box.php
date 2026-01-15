<?php
/* components/chat_box.php */
$header_bg = ($chat_user_type === 'Professional') ? 'bg-[#F0856C]' : 'bg-[#005949]';
$target_name = $chat_target_name ?? 'Conversation';

// 1. SMART PATH LOGIC
$raw_image = trim($chat_target_image ?? '');
$img_path = null;

if (!empty($raw_image)) {
    
    if (strpos($raw_image, '../') === 0) {
        $img_path = $raw_image;
    } else {
        
        $img_path = "../../uploads/" . $raw_image;
    }
}
?>

<style>
    
    body {
        margin: 0;
        padding: 0;
        overflow: hidden !important;
    }
</style>

<div class="w-full bg-white rounded-lg shadow-lg overflow-hidden flex flex-col h-[600px] border border-gray-200">
    <div class="<?= $header_bg ?> p-4 flex justify-between items-center text-white shadow-md z-10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center overflow-hidden border border-white/30">
                <?php if ($img_path): ?>
                    <img src="<?= $img_path ?>"
                        class="w-full h-full object-cover"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <span class="font-bold text-lg hidden"><?= strtoupper(substr($target_name, 0, 1)) ?></span>
                <?php else: ?>
                    <span class="font-bold text-lg"><?= strtoupper(substr($target_name, 0, 1)) ?></span>
                <?php endif; ?>
            </div>
            <div>
                <h3 class="font-bold text-lg leading-tight"><?= htmlspecialchars($target_name) ?></h3>
                <div class="flex items-center gap-1 opacity-80">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    <span class="text-xs">Secure Connection</span>
                </div>
            </div>
        </div>
    </div>

    <div id="chat-ui-container" class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#E5DDD5]"></div>

    <div id="attachment-preview" class="hidden p-3 bg-gray-100 border-t border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div id="preview-thumbnail" class="w-12 h-12 bg-gray-300 rounded-lg overflow-hidden flex items-center justify-center text-[10px] text-gray-600 font-bold uppercase"></div>
            <div class="text-sm">
                <p id="preview-filename" class="font-medium text-gray-700 truncate max-w-[200px]"></p>
                <p class="text-xs text-gray-500">Ready to send...</p>
            </div>
        </div>
        <button type="button" id="cancel-attachment" class="text-red-500 p-2">✕</button>
    </div>

    <div class="p-3 bg-[#F0F2F5] border-t border-gray-200">
        <form id="chat-ui-form" class="flex items-center gap-2" enctype="multipart/form-data">
            <input type="file" id="chat-file-input" class="hidden" accept="image/*,video/*,.pdf">
            <button type="button" onclick="document.getElementById('chat-file-input').click()" class="p-2 text-gray-500 hover:bg-gray-200 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                </svg>
            </button>
            <input type="text" id="chat-ui-input" class="flex-1 border-none rounded-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#005949] shadow-sm" placeholder="Type a message..." autocomplete="off">
            <button type="submit" class="<?= $header_bg ?> text-white p-3 rounded-full shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M3.478 2.405a.75.75 0 0 0-.926.94l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.405z" />
                </svg>
            </button>
        </form>
    </div>
</div>

<script>
    // Pass data to external JS file
    window.chatBoxData = {
        linkId: <?= json_encode($chat_link_id) ?>,
        myType: <?= json_encode($chat_user_type) ?>,
        myId: <?= json_encode($chat_my_id) ?>,
        targetId: <?= json_encode($chat_target_id) ?>
    };
</script>
<script src="../../js/components/chat_box.js"></script>