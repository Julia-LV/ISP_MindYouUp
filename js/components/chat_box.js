document.addEventListener('DOMContentLoaded', function() {
    const linkId = window.chatBoxData?.linkId || 0;
    const myType = window.chatBoxData?.myType || '';
    const myId = window.chatBoxData?.myId || 0;
    const targetId = window.chatBoxData?.targetId || 0;

    const container = document.getElementById('chat-ui-container');
    const fileInput = document.getElementById('chat-file-input');
    const previewArea = document.getElementById('attachment-preview');

    let isInitialLoad = true;

    // File Preview Logic (WhatsApp Style)
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            document.getElementById('preview-filename').textContent = file.name;
            previewArea.classList.remove('hidden');
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('preview-thumbnail').innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                reader.readAsDataURL(file);
            } else {
                document.getElementById('preview-thumbnail').textContent = file.name.split('.').pop();
            }
        }
    });

    document.getElementById('cancel-attachment').addEventListener('click', () => {
        fileInput.value = "";
        previewArea.classList.add('hidden');
    });

    // Render Function
    const render = (messages) => {
        const html = messages.map(msg => {
            const isMe = msg.Sender_Type === myType;
            const bubble = isMe ? 'bg-[#E7FFDB] rounded-tr-none ml-auto' : 'bg-white rounded-tl-none mr-auto';

            let media = '';
            if (msg.File_Path) {
                const path = '../../uploads_chat/' + msg.File_Path;
                if (msg.File_Type?.startsWith('image/')) {
                    media = `<img src="${path}" class="rounded-lg max-w-[250px] max-h-[200px] w-auto h-auto object-cover mb-2 cursor-pointer" onclick="window.open('${path}')">`;
                } else {
                    media = `<a href="${path}" target="_blank" class="flex items-center gap-2 bg-black/5 p-2 rounded-md mb-2 no-underline border border-black/5 hover:bg-black/10 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                            <span class="text-[11px] font-bold text-blue-700 truncate w-32">${msg.File_Path.split('_').pop()}</span>
                            </a>`;
                }
            }

            return `<div class="flex flex-col w-full">
            <div class="${bubble} px-3 py-2 rounded-lg shadow-sm max-w-[85%] w-fit text-sm text-gray-800">
                ${media}
                <span>${msg.Chat_Text || ''}</span>
            </div>
        </div>`;
        }).join('');

        if (container.innerHTML !== html) {
            const isAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
            container.innerHTML = html;
            if (isAtBottom || isInitialLoad) {
                container.scrollTop = container.scrollHeight;
                isInitialLoad = false;
            }
        }
    };

    // Send Logic
    document.getElementById('chat-ui-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('chat-ui-input');
        const formData = new FormData();
        formData.append('link_id', linkId);
        formData.append('sender_type', myType);
        formData.append('message', input.value.trim());
        formData.append('sender_id', myId);
        formData.append('receiver_id', targetId);
        if (fileInput.files[0]) formData.append('chat_file', fileInput.files[0]);

        input.value = '';
        fileInput.value = '';
        previewArea.classList.add('hidden');

        await fetch('../../components/chat_handler.php?action=send', {
            method: 'POST',
            body: formData
        });
        fetchMessages();
    });

    const fetchMessages = async () => {
        const res = await fetch(`../../components/chat_handler.php?action=fetch&link_id=${linkId}`);
        render(await res.json());
    };

    setInterval(fetchMessages, 3000);
    fetchMessages();
});
