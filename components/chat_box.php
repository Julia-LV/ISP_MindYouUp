<!-- 
  COMPONENT: Chat Box
  FILEPATH: components/chat_box.php
  DEPENDENCY: Tailwind CSS
-->

<?php
// Default Theme Colors (Emerald for Patient)
$theme_color = $chat_theme_color ?? 'emerald';
$header_bg = 'bg-[#005949]';
$msg_me_bg = 'bg-[#E7FFDB]'; // WhatsApp-ish green for self
?>

<div class="w-full bg-white rounded-lg shadow-lg overflow-hidden flex flex-col h-[600px] border border-gray-200">
    
    <!-- Chat Header -->
    <div class="<?php echo $header_bg; ?> p-4 flex justify-between items-center text-white shadow-md z-10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold text-white">
                DR
            </div>
            <div>
                <h3 class="font-bold text-lg leading-tight">Conversation</h3>
                <div class="flex items-center gap-1 opacity-80">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    <span class="text-xs">Secure Connection</span>
                </div>
            </div>
        </div>
        <span class="text-xs bg-black/20 px-2 py-1 rounded">Patient View</span>
    </div>

    <!-- Messages Area -->
    <div id="chat-ui-container" class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#E5DDD5]">
        <!-- JS will inject messages here -->
        <div class="flex justify-center mt-10">
             <span class="bg-white/80 px-4 py-1 rounded-full text-xs text-gray-500 shadow-sm">
                Loading messages...
             </span>
        </div>
    </div>

    <!-- Input Area -->
    <div class="p-3 bg-[#F0F2F5] border-t border-gray-200">
        <form id="chat-ui-form" class="flex items-center gap-2">
            <input 
                type="text" 
                id="chat-ui-input" 
                class="flex-1 border-none rounded-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#005949] shadow-sm text-gray-700"
                placeholder="Type a message..." 
                autocomplete="off"
            >
            <button 
                type="submit" 
                class="<?php echo $header_bg; ?> hover:opacity-90 text-white p-3 rounded-full transition-all shadow-md flex-shrink-0"
            >
                <!-- Send Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                </svg>
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Configuration
    const linkId = <?php echo json_encode($chat_link_id); ?>;
    const myType = 'Patient';
    
    // CRITICAL: Point to the 'common' folder handler
    const API_URL = '../../components/chat_handler.php'; 

    const container = document.getElementById('chat-ui-container');
    const form = document.getElementById('chat-ui-form');
    const input = document.getElementById('chat-ui-input');
    
    // 2. Helper Functions
    const scrollToBottom = () => {
        container.scrollTop = container.scrollHeight;
    };

    const render = (messages) => {
        if(messages.length === 0) {
            container.innerHTML = '<div class="text-center text-gray-500 text-sm mt-4">No messages yet. Say hello!</div>';
            return;
        }

        const html = messages.map(msg => {
            const isMe = msg.Sender_Type === myType;
            
            // Layout classes
            const align = isMe ? 'items-end' : 'items-start';
            const bubble = isMe ? 'bg-[#E7FFDB] rounded-tr-none' : 'bg-white rounded-tl-none';
            
            // Format time
            const time = new Date(msg.Chat_Time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

            return `
                <div class="flex flex-col ${align} w-full">
                    <div class="${bubble} px-3 py-2 rounded-lg shadow-sm max-w-[80%] text-sm text-gray-800 relative">
                        <span>${msg.Chat_Text}</span>
                        <span class="text-[10px] text-gray-400 float-right mt-2 ml-3">${time}</span>
                    </div>
                </div>
            `;
        }).join('');

        // Only update DOM if changed to prevent jitter
        if(container.innerHTML !== html) {
            container.innerHTML = html;
            scrollToBottom(); 
        }
    };

    // 3. Fetch Logic
    const fetchMessages = async () => {
        if(!linkId) return;
        try {
            const res = await fetch(`${API_URL}?action=fetch&link_id=${linkId}`);
            if(!res.ok) throw new Error("API Path Wrong");
            const data = await res.json();
            render(data);
        } catch (err) {
            console.error('Chat Error:', err);
            container.innerHTML = '<div class="text-red-500 text-center text-xs p-2">Connection Error. Check console.</div>';
        }
    };

    // 4. Send Logic
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if(!text) return;

        input.value = ''; // Clear immediately
        
        await fetch(`${API_URL}?action=send`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                link_id: linkId,
                sender_type: myType,
                message: text
            })
        });
        
        fetchMessages(); // Refresh immediately
    });

    // 5. Start
    fetchMessages();
    setInterval(fetchMessages, 3000);
});
</script>