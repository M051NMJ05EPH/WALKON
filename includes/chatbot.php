<!-- WalkOn AI Concierge Chatbot -->
<div id="walkon-ai-chatbot" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; font-family: 'Outfit', sans-serif;">
    <!-- Chat Toggle Button -->
    <button id="chat-toggle" onclick="toggleChat()" style="width: 65px; height: 65px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #2563eb); border: none; cursor: pointer; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4); display: flex; align-items: center; justify-content: center; transition: 0.3s; position: relative;">
        <i class="fas fa-robot" id="toggle-icon" style="color: white; font-size: 1.8rem;"></i>
        <span style="position: absolute; top: 0; right: 0; width: 15px; height: 15px; background: #10b981; border: 3px solid #fff; border-radius: 50%;"></span>
    </button>

    <!-- Chat Window -->
    <div id="chat-window" style="position: absolute; bottom: 85px; right: 0; width: 380px; height: 550px; background: #ffffff; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); display: none; flex-direction: column; overflow: hidden; border: 1px solid rgba(0,0,0,0.05); animation: chatSlideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0f172a, #1e293b); padding: 25px; color: white; display: flex; align-items: center; gap: 15px;">
            <div style="width: 45px; height: 45px; background: rgba(16,185,129,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(16,185,129,0.3);">
                <i class="fas fa-brain" style="color: #10b981; font-size: 1.2rem;"></i>
            </div>
            <div>
                <h4 style="margin: 0; font-size: 1rem; font-weight: 800;">WalkOn Concierge</h4>
                <div style="display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: #10b981;">
                    <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></span>
                    AI Model Online
                </div>
            </div>
            <button onclick="toggleChat()" style="margin-left: auto; background: none; border: none; color: rgba(255,255,255,0.5); cursor: pointer; font-size: 1.2rem;"><i class="fas fa-times"></i></button>
        </div>

        <!-- Messages Area -->
        <div id="chat-messages" style="flex: 1; padding: 20px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 15px;">
            <!-- AI Welcome Message -->
            <div style="align-self: flex-start; max-width: 85%; background: white; padding: 12px 16px; border-radius: 18px 18px 18px 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); color: #1e293b; font-size: 0.9rem; border: 1px solid #f1f5f9;">
                Hello! I'm your **WalkOn AI Concierge**. How can I help you elevate your footwear experience today?
            </div>
            <div style="align-self: flex-start; max-width: 85%; background: white; padding: 12px 16px; border-radius: 18px 18px 18px 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); color: #1e293b; font-size: 0.9rem; border: 1px solid #f1f5f9;">
                Try asking me about:
                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
                    <button onclick="quickAsk('Hottest Trends')" style="padding: 6px 12px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 50px; color: #0369a1; font-size: 0.75rem; font-weight: 600; cursor: pointer;">🔥 Hottest Trends</button>
                    <button onclick="quickAsk('Track Order')" style="padding: 6px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 50px; color: #166534; font-size: 0.75rem; font-weight: 600; cursor: pointer;">📦 Track Order</button>
                    <button onclick="quickAsk('Sell Shoes')" style="padding: 6px 12px; background: #fff7ed; border: 1px solid #ffedd5; border-radius: 50px; color: #9a3412; font-size: 0.75rem; font-weight: 600; cursor: pointer;">💰 Sell Shoes</button>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div style="padding: 20px; background: white; border-top: 1px solid #f1f5f9; display: flex; gap: 10px;">
            <input type="text" id="chat-input" placeholder="Ask anything..." style="flex: 1; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; outline: none; transition: 0.3s; font-size: 0.9rem;">
            <button onclick="sendMessage()" style="width: 45px; height: 45px; border-radius: 12px; background: var(--primary, #10b981); border: none; color: white; cursor: pointer; transition: 0.3s;">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes chatSlideUp {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.5; }
        100% { transform: scale(1); opacity: 1; }
    }
    #chat-input:focus { border-color: #10b981; box-shadow: 0 0 10px rgba(16,185,129,0.1); }
</style>

<script>
    function toggleChat() {
        const window = document.getElementById('chat-window');
        const icon = document.getElementById('toggle-icon');
        const toggleBtn = document.getElementById('chat-toggle');
        
        if (window.style.display === 'none' || window.style.display === '') {
            window.style.display = 'flex';
            icon.className = 'fas fa-times';
            toggleBtn.style.transform = 'rotate(90deg)';
        } else {
            window.style.display = 'none';
            icon.className = 'fas fa-robot';
            toggleBtn.style.transform = 'rotate(0deg)';
        }
    }

    function quickAsk(query) {
        document.getElementById('chat-input').value = query;
        sendMessage();
    }

    function sendMessage() {
        const input = document.getElementById('chat-input');
        const container = document.getElementById('chat-messages');
        const text = input.value.trim();
        
        if (!text) return;

        // User Message
        const userDiv = document.createElement('div');
        userDiv.style.cssText = "align-self: flex-end; max-width: 85%; background: #2563eb; color: white; padding: 12px 16px; border-radius: 18px 18px 4px 18px; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);";
        userDiv.textContent = text;
        container.appendChild(userDiv);
        
        input.value = '';
        container.scrollTop = container.scrollHeight;

        // AI Thinking...
        setTimeout(() => {
            const aiDiv = document.createElement('div');
            aiDiv.style.cssText = "align-self: flex-start; max-width: 85%; background: white; padding: 12px 16px; border-radius: 18px 18px 18px 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); color: #1e293b; font-size: 0.9rem; border: 1px solid #f1f5f9;";
            aiDiv.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> WalkOn AI is thinking...';
            container.appendChild(aiDiv);
            container.scrollTop = container.scrollHeight;

            // AI Response
            setTimeout(() => {
                const lowText = text.toLowerCase().trim();
                let response = "";

                if (lowText === 'hi' || lowText === 'hello' || lowText === 'hey') {
                    response = "Hello! I'm the WalkOn AI Concierge. I can help you find luxury footwear, track your orders, or help you start selling on our platform. What's on your mind?";
                } else if (lowText.includes('what is this site') || lowText.includes('what is walkon') || lowText.includes('who are you')) {
                    response = "**WalkOn** is a premium global multi-channel footwear platform. We connect high-end designers with fashion-forward customers and provide vendors with AI-powered tools to sync inventory across Amazon, Shopify, and more.";
                } else if (lowText.includes('trend')) {
                    response = "Current trends show a 40% surge in **Tech-Luxe Sneakers** and **Sustainable Knits**. Check out our 'Athletic' category for the latest drops!";
                } else if (lowText.includes('sell')) {
                    response = "Ready to partner? Our **AI Channel Sync** infrastructure allows you to list on Amazon & eBay instantly. Head to the <a href='start_selling.php' style='color: #2563eb; font-weight: 700;'>Start Selling</a> page to begin!";
                } else if (lowText.includes('track') || lowText.includes('order id')) {
                    response = "Please provide your order ID (e.g., #WK-1029). I can then pull real-time logistics data from our fulfillment network.";
                } else if (lowText.includes('order') || lowText.includes('vew')) {
                    response = "You can view all your recent purchases and their status on your <a href='my_orders.php' style='color: #2563eb; font-weight: 700; text-decoration: underline;'>My Orders</a> page. Need help with a specific one?";
                } else {
                    response = "That's an interesting question! While I'm still learning, I can definitely help with **orders, trends, or vendor onboarding**. Could you tell me more about what you're looking for?";
                }
                
                aiDiv.innerHTML = response;
                container.scrollTop = container.scrollHeight;
            }, 1000);
        }, 500);
    }

    // Enter Key Support
    document.getElementById('chat-input').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') sendMessage();
    });
</script>
