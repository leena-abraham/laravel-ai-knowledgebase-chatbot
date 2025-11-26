<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat Widget</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 380px;
            height: 600px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 9999;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .chat-avatar {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .chat-title {
            flex: 1;
        }
        
        .chat-title h3 {
            font-size: 18px;
            margin-bottom: 4px;
        }
        
        .chat-title p {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f9fafb;
        }
        
        .message {
            margin-bottom: 16px;
            display: flex;
            gap: 8px;
        }
        
        .message-user {
            justify-content: flex-end;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 16px;
            line-height: 1.5;
        }
        
        .message-user .message-bubble {
            background: #6366f1;
            color: white;
            border-radius: 16px 16px 0 16px;
        }
        
        .message-bot .message-bubble {
            background: white;
            color: #1f2937;
            border-radius: 16px 16px 16px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 4px;
        }
        
        .typing-indicator {
            display: none;
            padding: 12px 16px;
            background: white;
            border-radius: 16px;
            width: fit-content;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .typing-indicator.active {
            display: block;
        }
        
        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #6b7280;
            margin: 0 2px;
            animation: typing 1.4s infinite;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-10px);
            }
        }
        
        .chat-input {
            padding: 16px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }
        
        .input-container {
            display: flex;
            gap: 8px;
        }
        
        .chat-input input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 24px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease;
        }
        
        .chat-input input:focus {
            border-color: #6366f1;
        }
        
        .send-button {
            width: 44px;
            height: 44px;
            background: #6366f1;
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .send-button:hover {
            background: #4f46e5;
            transform: scale(1.05);
        }
        
        .send-button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: scale(1);
        }
        
        @media (max-width: 480px) {
            .chat-widget {
                width: 100%;
                height: 100%;
                bottom: 0;
                right: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="chat-widget">
        <div class="chat-header">
            <div class="chat-avatar">🤖</div>
            <div class="chat-title">
                <h3>AI Support</h3>
                <p>We're here to help!</p>
            </div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message message-bot">
                <div class="message-bubble">
                    <div>Hello! 👋 How can I help you today?</div>
                    <div class="message-time" id="welcomeTime"></div>
                </div>
            </div>
        </div>
        
        <div class="chat-input">
            <div class="input-container">
                <input type="text" id="messageInput" placeholder="Type your message..." autocomplete="off">
                <button class="send-button" id="sendButton">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        const companySlug = '{{ $companySlug }}';
        let sessionId = localStorage.getItem('chat_session_id');
        
        const messagesContainer = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        
        // Set welcome time
        document.getElementById('welcomeTime').textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        // Create typing indicator
        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'typing-indicator';
        typingIndicator.innerHTML = '<span></span><span></span><span></span>';
        messagesContainer.appendChild(typingIndicator);
        
        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'message-user' : 'message-bot'}`;
            
            const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            messageDiv.innerHTML = `
                <div class="message-bubble">
                    <div>${content}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;
            
            messagesContainer.insertBefore(messageDiv, typingIndicator);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;
            
            // Add user message
            addMessage(message, true);
            messageInput.value = '';
            sendButton.disabled = true;
            
            // Show typing indicator
            typingIndicator.classList.add('active');
            
            try {
                const response = await fetch(`/api/chat/${companySlug}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: sessionId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Store session ID
                    if (data.session_id) {
                        sessionId = data.session_id;
                        localStorage.setItem('chat_session_id', sessionId);
                    }
                    
                    // Hide typing indicator
                    typingIndicator.classList.remove('active');
                    
                    // Add bot response
                    addMessage(data.response);
                } else {
                    typingIndicator.classList.remove('active');
                    addMessage('Sorry, I encountered an error. Please try again.');
                }
            } catch (error) {
                typingIndicator.classList.remove('active');
                addMessage('Sorry, I couldn\'t connect to the server. Please try again.');
            }
            
            sendButton.disabled = false;
            messageInput.focus();
        }
        
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        
        // Focus input on load
        messageInput.focus();
    </script>
</body>
</html>
