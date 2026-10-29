<?php
// Build safe API URL dynamically based on current script endpoint
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$api_endpoint = $protocol . $host . $script . '?gs_chat_api=1';
?>

<div id="gs-chat-widget-container">
    <!-- Floating Chat Button -->
    <button id="gs-chat-toggle-btn" aria-label="Open Chat">
        <i class="fa-solid fa-comments"></i>
        <span id="gs-chat-badge" style="display:none;">0</span>
    </button>

    <!-- Main Chat Window -->
    <div id="gs-chat-window" class="gs-chat-hidden">
        <div class="gs-chat-header">
            <div class="gs-chat-title-area">
                <span class="gs-chat-status-dot"></span>
                <h3>GetSimple CE Live Chat <span id="gs-admin-badge" style="display:none; background:#d97706; color:#fff; font-size:10px; padding:1px 5px; border-radius:4px; margin-left:4px; vertical-align:middle;">ADMIN</span></h3>
            </div>
            <div class="gs-chat-header-actions">
                <button id="gs-chat-settings-btn" title="Change Nickname"><i class="fa-solid fa-user-gear"></i></button>
                <button id="gs-chat-close-btn" title="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>

        <div id="gs-chat-announcement" class="gs-chat-announcement">
            <i class="fa-solid fa-bullhorn"></i> <span id="gs-announcement-text">Loading announcement...</span>
        </div>

        <div id="gs-chat-messages" class="gs-chat-messages"></div>

        <form id="gs-chat-form" class="gs-chat-form">
            <input type="text" id="gs-chat-input" placeholder="Type a public message..." autocomplete="off" required maxlength="500">
            <button type="submit" id="gs-chat-send-btn"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>

    <!-- Nickname Setup Modal with Close Button -->
    <div id="gs-chat-modal" class="gs-chat-modal gs-chat-hidden">
        <div class="gs-chat-modal-content">
            <button type="button" id="gs-modal-close-btn" class="gs-modal-close-x" title="Close"><i class="fa-solid fa-xmark"></i></button>
            <h3>Welcome to Community Chat</h3>
            <p>Choose a display name and avatar icon:</p>
            <input type="text" id="gs-modal-username" placeholder="Your Nickname..." maxlength="20" required>
            <div class="gs-chat-avatar-picker">
                <span class="avatar-option selected" data-avatar="fa-user-ninja"><i class="fa-solid fa-user-ninja"></i></span>
                <span class="avatar-option" data-avatar="fa-user-astronaut"><i class="fa-solid fa-user-astronaut"></i></span>
                <span class="avatar-option" data-avatar="fa-robot"><i class="fa-solid fa-robot"></i></span>
                <span class="avatar-option" data-avatar="fa-cat"><i class="fa-solid fa-cat"></i></span>
                <span class="avatar-option" data-avatar="fa-dog"><i class="fa-solid fa-dog"></i></span>
                <span class="avatar-option" data-avatar="fa-ghost"><i class="fa-solid fa-ghost"></i></span>
            </div>
            <button type="button" id="gs-modal-save-btn">Join Chat</button>
        </div>
    </div>
</div>

<style>
/* 1. CONTAINER SHIELD: Reset inheritance and isolate styles */
#gs-chat-widget-container,
#gs-chat-widget-container * {
    box-sizing: border-box !important;
    letter-spacing: normal !important;
    text-transform: none !important;
    text-shadow: none !important;
    animation: none;
}

#gs-chat-widget-container {
    --gs-chat-primary: #2563eb;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
    z-index: 99999 !important;
    position: fixed !important;
    bottom: 20px !important;
    /* Default position property overridden dynamically via inline JS/CSS variable */
    right: 20px !important; 
    left: auto !important;
    color: #0f172a !important;
    text-align: left !important;
}

/* Dynamic Positioning Classes applied via JS */
#gs-chat-widget-container.gs-chat-pos-left {
    right: auto !important;
    left: 20px !important;
}
#gs-chat-widget-container.gs-chat-pos-right {
    left: auto !important;
    right: 20px !important;
}

/* 2. TOGGLE BUTTON */
#gs-chat-toggle-btn {
    width: 60px !important;
    height: 60px !important;
    border-radius: 50% !important;
    background: var(--gs-chat-primary, #2563eb) !important;
    color: #ffffff !important;
    border: none !important;
    cursor: pointer !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    font-size: 24px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: transform 0.2s, background 0.2s !important;
    position: relative !important;
    padding: 0 !important;
    margin: 0 !important;
}
#gs-chat-toggle-btn:hover { transform: scale(1.05) !important; filter: brightness(0.9); }

#gs-chat-badge {
    position: absolute !important;
    top: -2px !important;
    right: -2px !important;
    background: #dc2626 !important;
    color: #ffffff !important;
    font-size: 11px !important;
    padding: 2px 6px !important;
    border-radius: 10px !important;
    font-weight: bold !important;
    display: inline-block !important;
}

/* 3. CHAT WINDOW */
#gs-chat-window {
    position: absolute !important;
    bottom: 75px !important;
    width: 360px !important;
    height: 500px !important;
    background: #ffffff !important;
    border-radius: 14px !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    border: 1px solid #e5e7eb !important;
    transition: opacity 0.2s, transform 0.2s !important;
}

/* Dynamic window positioning based on container parent class */
#gs-chat-widget-container.gs-chat-pos-right #gs-chat-window {
    right: 0 !important;
    left: auto !important;
}
#gs-chat-widget-container.gs-chat-pos-left #gs-chat-window {
    left: 0 !important;
    right: auto !important;
}

#gs-chat-window.gs-chat-hidden { opacity: 0 !important; transform: translateY(10px) !important; pointer-events: none !important; display: none !important; }

.gs-chat-header {
    background: var(--gs-chat-primary, #2563eb) !important;
    color: #ffffff !important;
    padding: 12px 16px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}
.gs-chat-title-area { display: flex !important; align-items: center !important; gap: 8px !important; }
.gs-chat-status-dot { width: 9px !important; height: 9px !important; background: #10b981 !important; border-radius: 50% !important; display: inline-block !important; }
.gs-chat-header h3 { margin: 0 !important; font-size: 16px !important; font-weight: 600 !important; color: #ffffff !important; }

.gs-chat-header-actions {
    display: flex !important;
    gap: 4px !important;
}

.gs-chat-header-actions button {
    background: transparent !important;
    border: none !important;
    color: #ffffff !important;
    cursor: pointer !important;
    font-size: 16px !important;
    width: 30px !important;
    height: 30px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    margin: 0 !important;
    opacity: 0.8 !important;
    transition: opacity 0.2s !important;
}
.gs-chat-header-actions button:hover { opacity: 1 !important; }

.gs-chat-announcement {
    background: #eff6ff !important;
    color: #1e40af !important;
    padding: 8px 12px !important;
    font-size: 12px !important;
    border-bottom: 1px solid #dbeafe !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.gs-chat-messages {
    flex: 1 !important;
    padding: 12px !important;
    overflow-y: auto !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
    background: #f8fafc !important;
}

.gs-chat-msg { display: flex !important; gap: 8px !important; max-width: 85% !important; position: relative !important; }
.gs-chat-msg.self { align-self: flex-end !important; flex-direction: row-reverse !important; }

.gs-chat-avatar {
    width: 32px !important;
    height: 32px !important;
    background: #e2e8f0 !important;
    color: #334155 !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 14px !important;
    flex-shrink: 0 !important;
}

.gs-chat-bubble {
    background: #ffffff !important;
    color: #0f172a !important;
    padding: 8px 12px !important;
    border-radius: 10px !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    word-break: break-word !important;
    position: relative !important;
}
.gs-chat-msg.self .gs-chat-bubble { background: #dbeafe !important; color: #1e3a8a !important; }

.user-nick { color: var(--gs-chat-primary, #2563eb) !important; }

.gs-chat-msg-info { font-size: 11px !important; color: #64748b !important; margin-bottom: 2px !important; display: flex !important; gap: 6px !important; }
.gs-chat-msg.self .gs-chat-msg-info { justify-content: flex-end !important; }

/* Admin Delete Message Button */
.gs-admin-delete-btn {
    background: #dc2626 !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 50% !important;
    width: 12px !important;
    height: 12px !important;
    font-size: 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    position: absolute !important;
    top: -6px !important;
    right: -6px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
    padding: 0 !important;
    margin: 0 !important;
    z-index: 5 !important;
}
.gs-admin-delete-btn:hover { background: #b91c1c !important; }

/* 4. CHAT FORM & INPUT FIX */
.gs-chat-form {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    padding: 10px !important;
    background: #ffffff !important;
    border-top: 1px solid #e5e7eb !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 !important;
}

#gs-chat-input {
    flex: 1 !important;
    width: auto !important;
    min-width: 0 !important;
    height: 38px !important;
    padding: 8px 12px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 6px !important;
    outline: none !important;
    font-size: 14px !important;
    background: #ffffff !important;
    color: #0f172a !important;
    margin: 0 !important;
}
#gs-chat-input:focus { border-color: var(--gs-chat-primary, #2563eb) !important; }

#gs-chat-send-btn {
    background: var(--gs-chat-primary, #2563eb) !important;
    color: #ffffff !important;
    border: none !important;
    width: 42px !important;
    height: 38px !important;
    flex-shrink: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    border-radius: 6px !important;
    cursor: pointer !important;
    margin: 0 !important;
}
#gs-chat-send-btn:hover { filter: brightness(0.9); }

/* 5. MODAL STYLING */
.gs-chat-modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(0,0,0,0.5) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 100000 !important;
}
.gs-chat-modal.gs-chat-hidden { display: none !important; }

.gs-chat-modal-content {
    background: #ffffff !important;
    padding: 24px !important;
    border-radius: 12px !important;
    width: 320px !important;
    text-align: center !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
    position: relative !important;
}

.gs-modal-close-x {
    position: absolute !important;
    top: 12px !important;
    right: 12px !important;
    background: transparent !important;
    border: none !important;
    font-size: 16px !important;
    color: #64748b !important;
    cursor: pointer !important;
    width: auto !important;
    height: auto !important;
    padding: 4px !important;
    transition: color 0.2s !important;
}
.gs-modal-close-x:hover { color: #0f172a !important; }

.gs-chat-modal-content h3 { margin-top: 0 !important; color: #1e293b !important; }
.gs-chat-modal-content p { color: #64748b !important; font-size: 13px !important; }

.gs-chat-modal-content input {
    width: 100% !important;
    height: 38px !important;
    padding: 10px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 6px !important;
    margin: 12px 0 !important;
    box-sizing: border-box !important;
    background: #ffffff !important;
    color: #0f172a !important;
}

.gs-chat-avatar-picker {
    display: flex !important;
    justify-content: center !important;
    gap: 8px !important;
    margin-bottom: 16px !important;
    flex-wrap: wrap !important;
}

.avatar-option {
    width: 36px !important;
    height: 36px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 16px !important;
    cursor: pointer !important;
    border-radius: 50% !important;
    border: 2px solid transparent !important;
    background: #f1f5f9 !important;
    color: #334155 !important;
    transition: transform 0.2s, background 0.2s !important;
}
.avatar-option.selected {
    border-color: var(--gs-chat-primary, #2563eb) !important;
    background: #eff6ff !important;
    color: var(--gs-chat-primary, #2563eb) !important;
    transform: scale(1.1) !important;
}

#gs-modal-save-btn {
    background: var(--gs-chat-primary, #2563eb) !important;
    color: #ffffff !important;
    border: none !important;
    width: 100% !important;
    height: 40px !important;
    padding: 10px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
}

/* 6. RESPONSIVE MOBILE STYLES */
@media screen and (max-width: 480px) {
    #gs-chat-widget-container {
        bottom: 12px !important;
        right: 12px !important;
        left: 12px !important;
    }

    #gs-chat-window {
        position: fixed !important;
        top: 10px !important;
        left: 10px !important;
        right: 10px !important;
        bottom: 80px !important;
        width: auto !important;
        height: auto !important;
        max-width: 100vw !important;
        max-height: calc(100vh - 90px) !important;
        border-radius: 12px !important;
    }

    .gs-chat-modal-content {
        width: 90% !important;
        max-width: 340px !important;
        padding: 18px !important;
    }

    #gs-chat-input, 
    .gs-chat-modal-content input {
        font-size: 16px !important;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const apiUrl = "<?php echo $api_endpoint; ?>";
    const toggleBtn = document.getElementById("gs-chat-toggle-btn");
    const chatWindow = document.getElementById("gs-chat-window");
    const closeBtn = document.getElementById("gs-chat-close-btn");
    const settingsBtn = document.getElementById("gs-chat-settings-btn");
    const chatForm = document.getElementById("gs-chat-form");
    const chatInput = document.getElementById("gs-chat-input");
    const messagesContainer = document.getElementById("gs-chat-messages");
    const announcementText = document.getElementById("gs-announcement-text");
    const adminBadge = document.getElementById("gs-admin-badge");
    const widgetContainer = document.getElementById("gs-chat-widget-container");
    
    const modal = document.getElementById("gs-chat-modal");
    const modalUsername = document.getElementById("gs-modal-username");
    const modalSaveBtn = document.getElementById("gs-modal-save-btn");
    const modalCloseBtn = document.getElementById("gs-modal-close-btn");
    const avatarOptions = document.querySelectorAll(".avatar-option");

    let currentUser = localStorage.getItem("gs_chat_user");
    let currentAvatar = localStorage.getItem("gs_chat_avatar") || "fa-user-ninja";
    
    let clientId = localStorage.getItem("gs_chat_client_id");
    if (!clientId) {
        clientId = "user_" + Math.random().toString(36).substr(2, 9) + "_" + Date.now();
        localStorage.setItem("gs_chat_client_id", clientId);
    }

    let lastMessageCount = 0;
    let isChatOpen = false;
    let isAdminUser = false;
    let cachedMessages = [];

    avatarOptions.forEach(opt => {
        if(opt.getAttribute("data-avatar") === currentAvatar) {
            avatarOptions.forEach(o => o.classList.remove("selected"));
            opt.classList.add("selected");
        }
        opt.addEventListener("click", () => {
            avatarOptions.forEach(o => o.classList.remove("selected"));
            opt.classList.add("selected");
            currentAvatar = opt.getAttribute("data-avatar");
        });
    });

    modalSaveBtn.addEventListener("click", () => {
        const val = modalUsername.value.trim();
        if (val) {
            currentUser = val;
            localStorage.setItem("gs_chat_user", currentUser);
            localStorage.setItem("gs_chat_avatar", currentAvatar);
            modal.classList.add("gs-chat-hidden");
        } else {
            alert("Please enter a nickname!");
        }
    });

    modalCloseBtn.addEventListener("click", () => {
        modal.classList.add("gs-chat-hidden");
    });

    settingsBtn.addEventListener("click", () => {
        modalUsername.value = currentUser || "";
        modal.classList.remove("gs-chat-hidden");
    });

    toggleBtn.addEventListener("click", () => {
        isChatOpen = !isChatOpen;
        if (isChatOpen) {
            chatWindow.classList.remove("gs-chat-hidden");
            document.getElementById("gs-chat-badge").style.display = "none";
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        } else {
            chatWindow.classList.add("gs-chat-hidden");
        }
    });

    closeBtn.addEventListener("click", () => {
        isChatOpen = false;
        chatWindow.classList.add("gs-chat-hidden");
    });

    async function fetchMessages() {
        try {
            const res = await fetch(apiUrl);
            const data = await res.json();
            if (data.status === "success") {
                announcementText.textContent = data.announcement || "Welcome to community chat!";
                
                // Dynamic Theme Color Application
                const themeColor = data.theme_color || "#2563EB";
                widgetContainer.style.setProperty("--gs-chat-primary", themeColor);

                // Dynamic Position Application (right or left)
                const chatPosition = data.chat_position === "left" ? "left" : "right";
                widgetContainer.classList.remove("gs-chat-pos-right", "gs-chat-pos-left");
                widgetContainer.classList.add("gs-chat-pos-" + chatPosition);

                if (data.is_admin) {
                    isAdminUser = true;
                    adminBadge.style.display = "inline-block";
                }

                const messages = data.messages || [];
                cachedMessages = messages;
                
                if (messages.length !== lastMessageCount) {
                    renderMessages(messages);
                    if (messages.length > lastMessageCount && !isChatOpen && lastMessageCount > 0) {
                        const badge = document.getElementById("gs-chat-badge");
                        badge.style.display = "block";
                        badge.textContent = messages.length - lastMessageCount;
                    }
                    lastMessageCount = messages.length;
                }
            }
        } catch (e) {
            console.error("Chat sync error:", e);
        }
    }

    function renderMessages(messages) {
        messagesContainer.innerHTML = "";
        messages.forEach(msg => {
            const isSelf = msg.user === currentUser;
            const msgDiv = document.createElement("div");
            msgDiv.className = `gs-chat-msg ${isSelf ? "self" : ""}`;
            
            const avatarClass = msg.avatar && msg.avatar.startsWith('fa-') ? `fa-solid ${msg.avatar}` : 'fa-solid fa-user';
            const adminDeleteHTML = isAdminUser ? `<button class="gs-admin-delete-btn" onclick="deleteChatMessage('${msg.id}')" title="Delete Message"><i class="fa-solid fa-xmark"></i></button>` : '';

            msgDiv.innerHTML = `
                <div class="gs-chat-avatar"><i class="${avatarClass}"></i></div>
                <div>
                    <div class="gs-chat-msg-info">
                        <span><strong class="user-nick">${escapeHtml(msg.user)}</strong></span>
                        <span>${msg.timestamp}</span>
                    </div>
                    <div class="gs-chat-bubble">
                        ${escapeHtml(msg.text)}
                        ${adminDeleteHTML}
                    </div>
                </div>
            `;
            messagesContainer.appendChild(msgDiv);
        });
        if (isChatOpen) messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    window.deleteChatMessage = async function(msgId) {
        if (!confirm("Are you sure you want to delete this message?")) return;
        try {
            const res = await fetch(apiUrl, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ type: "delete_message", id: msgId })
            });
            const data = await res.json();
            if (data.status === "success") {
                fetchMessages();
            } else {
                alert("Failed to delete message: " + (data.message || "Unauthorized"));
            }
        } catch (err) {
            console.error("Failed to delete message:", err);
        }
    };

    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    chatForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!currentUser) {
            modal.classList.remove("gs-chat-hidden");
            return;
        }
        const text = chatInput.value.trim();
        if (!text) return;
        chatInput.value = "";

        try {
            const res = await fetch(apiUrl, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ 
                    type: "send_message", 
                    user: currentUser, 
                    avatar: currentAvatar, 
                    text: text,
                    client_id: clientId 
                })
            });
            const data = await res.json();
            if (data.status === "success") {
                fetchMessages();
            } else {
                alert(data.message || "Failed to send message.");
                if (data.message && data.message.toLowerCase().includes("nickname")) {
                    modal.classList.remove("gs-chat-hidden");
                }
            }
        } catch (err) {
            console.error("Failed to send message:", err);
        }
    });

    fetchMessages();
    setInterval(fetchMessages, 4000);
});
</script>