@extends('layouts.app')

@section('content')
<div id="chat-app"
    data-auth="{{ auth()->id() }}"
    data-receiver="{{ $receiver->id }}"
    class="mx-auto max-w-2xl">

    <div class="bg-white shadow rounded-lg">
        {{-- Chat Header --}}
        <div class="p-4 border-b flex items-center space-x-3">
            <img src="{{ Storage::url($receiver->profile->profile_picture) }}" class="w-10 h-10 rounded-full">
            <h2 class="font-semibold text-lg">{{ $receiver->profile->display_name }}</h2>
        </div>

        {{-- Chat Messages --}}
        <div id="messages" class="h-96 overflow-y-auto p-4 bg-gray-50" style="display: flex; flex-direction: column; gap: 12px;"></div>

        {{-- Chat Input --}}
        <form id="chat-form" class="p-4 flex space-x-2">
            <input type="text" id="message-input"
                class="flex-1 border rounded px-3 py-2"
                placeholder="Type a message...">
            <button class="bg-blue-500 text-white px-4 py-2 rounded" type="submit">Send</button>
        </form>

    </div>
</div>
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const app = document.getElementById("chat-app");
        const auth = Number(app.dataset.auth);
        const receiver = Number(app.dataset.receiver);

        const messagesBox = document.getElementById("messages");
        const input = document.getElementById("message-input");
        const form = document.getElementById("chat-form");

        let messages = [];

        // Auto scroll
        function scrollBottom() {
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        // Render messages - FIXED VERSION
        function renderMessages() {
            messagesBox.innerHTML = "";

            messages.forEach(m => {
                // More robust type conversion
                const senderId = parseInt(m.sender);
                const isMine = senderId === parseInt(auth);

                const wrapper = document.createElement("div");
                const bubble = document.createElement("div");

                wrapper.style.display = 'flex';
                wrapper.style.justifyContent = isMine ? 'flex-end' : 'flex-start';
                wrapper.style.width = '100%';

                bubble.style.padding = '8px 16px';
                bubble.style.maxWidth = '70%';
                bubble.style.borderRadius = '18px';
                bubble.style.fontSize = '14px';
                bubble.style.boxShadow = '0 1px 2px rgba(0,0,0,0.1)';

                if (isMine) {
                    bubble.style.background = '#007bff';
                    bubble.style.color = 'white';
                    bubble.style.borderBottomRightRadius = '4px';
                } else {
                    bubble.style.background = '#f1f1f1';
                    bubble.style.color = 'black';
                    bubble.style.borderBottomLeftRadius = '4px';
                }

                bubble.textContent = m.message;
                wrapper.appendChild(bubble);
                messagesBox.appendChild(wrapper);
            });

            scrollBottom();
        }


        // Fetch chat history
        fetch(`/messages/${receiver}`)
            .then(res => res.json())
            .then(data => {
                messages = data;
                renderMessages();
            });

        // Listen to real-time events
        Echo.private(`chat.${auth}`)
            .listen(".message.sent", (e) => {
                messages.push({
                    id: e.id,
                    message: e.message,
                    sender: Number(e.sender), // force number
                    receiver: Number(e.receiver),
                    time: e.time
                });
                renderMessages();
            });



        // Sending the message
        form.addEventListener("submit", function(e) {
            e.preventDefault();

            const text = input.value.trim();
            if (!text) return;

            fetch("/messages", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        receiver_id: receiver,
                        message: text
                    })
                })
                .then(res => res.json())
                .then(msg => {
                    messages.push(msg);
                    renderMessages();
                });

            input.value = "";
        });
    });
</script>
@endpush
@endsection