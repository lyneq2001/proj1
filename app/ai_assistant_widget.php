<div id="ai-assistant" class="ai-assistant">
    <div class="ai-widget-shell" aria-hidden="true"></div>
    
    <!-- Floating Action Button -->
    <div
        id="ai-chat-bubble"
        class="ai-chat-bubble"
        role="button"
        tabindex="0"
        aria-expanded="false"
        aria-controls="ai-chat-window"
        aria-label="Otwórz czat z asystentem AI"
    >
        <div class="ai-bubble-content">
            <div class="ai-avatar" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2Z" fill="currentColor"/>
                    <path d="M19 13V9C19 7.9 18.1 7 17 7H7C5.9 7 5 7.9 5 9V13C5 14.1 5.9 15 7 15H17C18.1 15 19 14.1 19 13Z" fill="currentColor"/>
                    <path d="M19 17V19C19 20.1 18.1 21 17 21H7C5.9 21 5 20.1 5 19V17C5 15.9 5.9 15 7 15H17C18.1 15 19 15.9 19 17Z" fill="currentColor"/>
                </svg>
            </div>
            <span class="ai-bubble-text">Potrzebujesz pomocy?</span>
        </div>
        <div class="ai-pulse" aria-hidden="true"></div>
        <div class="ai-notification-badge" id="ai-notification">1</div>
    </div>

    <!-- Chat Window -->
    <div id="ai-chat-window" class="ai-chat-window hidden" role="dialog" aria-modal="false" aria-live="polite">
        <!-- Header -->
        <div class="ai-chat-header">
            <div class="ai-header-content">
                <div class="ai-header-avatar" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2Z" fill="currentColor"/>
                        <path d="M19 13V9C19 7.9 18.1 7 17 7H7C5.9 7 5 7.9 5 9V13C5 14.1 5.9 15 7 15H17C18.1 15 19 14.1 19 13Z" fill="currentColor"/>
                        <path d="M19 17V19C19 20.1 18.1 21 17 21H7C5.9 21 5 20.1 5 19V17C5 15.9 5.9 15 7 15H17C18.1 15 19 15.9 19 17Z" fill="currentColor"/>
                    </svg>
                </div>
                <div class="ai-header-info">
                    <h3 class="ai-header-title">Asystent Nieruchomości</h3>
                    <p class="ai-header-status">
                        <span class="ai-status-dot"></span>
                        Online • Gotowy do pomocy
                    </p>
                </div>
            </div>
            <div class="ai-header-actions">
                <button id="ai-minimize-chat" class="ai-header-btn" type="button" aria-label="Minimalizuj czat">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 12H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <button id="ai-close-chat" class="ai-header-btn ai-close-btn" type="button" aria-label="Zamknij czat">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="ai-chat-messages" class="ai-chat-messages">
            <!-- Messages will be dynamically added here -->
        </div>

        <!-- Quick Actions -->
        <div class="ai-quick-actions">
            <button class="ai-quick-action" data-action="add-property">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Dodaj nieruchomość
            </button>
            <button class="ai-quick-action" data-action="search-help">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Pomoc w wyszukiwaniu
            </button>
            <button class="ai-quick-action" data-action="contact-support">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 11.5C21 16.75 16.75 21 11.5 21C6.25 21 2 16.75 2 11.5C2 6.25 6.25 2 11.5 2C16.75 2 21 6.25 21 11.5Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M8 11H15M8 8H15M8 14H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Kontakt
            </button>
        </div>

        <!-- Input Area -->
        <div class="ai-chat-input-container">
            <div class="ai-chat-input-wrapper">
                <input
                    type="text"
                    id="ai-chat-input"
                    class="ai-chat-input"
                    placeholder="Napisz wiadomość do asystenta..."
                    maxlength="500"
                    autocomplete="off"
                >
                <div class="ai-input-actions">
                    <button id="ai-send-message" class="ai-send-btn" type="button" aria-label="Wyślij wiadomość">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="ai-chat-footer">
                <span class="ai-char-counter" aria-live="polite">0/500</span>
                <span class="ai-powered-by">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L13.5 4.5L16 6L13.5 7.5L12 10L10.5 7.5L8 6L10.5 4.5L12 2Z" fill="currentColor"/>
                        <path d="M18 8L19.5 10.5L22 12L19.5 13.5L18 16L16.5 13.5L14 12L16.5 10.5L18 8Z" fill="currentColor"/>
                        <path d="M6 8L7.5 10.5L10 12L7.5 13.5L6 16L4.5 13.5L2 12L4.5 10.5L6 8Z" fill="currentColor"/>
                        <path d="M12 14L13.5 16.5L16 18L13.5 19.5L12 22L10.5 19.5L8 18L10.5 16.5L12 14Z" fill="currentColor"/>
                    </svg>
                    Powered by AI
                </span>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced AI Assistant Styles */
.ai-assistant {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 4.5rem;
    height: 4.5rem;
    z-index: 2147483000;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    transform-origin: bottom right;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.ai-assistant.open {
    width: 26rem;
    height: 36rem;
}

.ai-assistant.minimized {
    width: 20rem;
    height: 4.5rem;
}

.ai-widget-shell {
    position: absolute;
    inset: 0;
    border-radius: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 
        0 25px 50px -12px rgba(102, 126, 234, 0.4),
        0 8px 25px -8px rgba(102, 126, 234, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(20px);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none;
    z-index: 1;
}

.ai-assistant.open .ai-widget-shell {
    border-radius: 1.5rem;
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 
        0 32px 64px -16px rgba(15, 23, 42, 0.25),
        0 16px 32px -16px rgba(15, 23, 42, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.9);
}

.ai-assistant.minimized .ai-widget-shell {
    border-radius: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Chat Bubble */
.ai-chat-bubble {
    position: relative;
    z-index: 2;
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #fff;
    outline: none;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    animation: float 6s ease-in-out infinite;
    overflow: hidden;
}

.ai-chat-bubble::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    border-radius: inherit;
}

.ai-chat-bubble:hover,
.ai-chat-bubble:focus-visible {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 
        0 20px 40px -12px rgba(102, 126, 234, 0.5),
        0 8px 20px -8px rgba(102, 126, 234, 0.3);
}

.ai-chat-bubble:focus-visible {
    outline: 2px solid rgba(255, 255, 255, 0.8);
    outline-offset: 2px;
}

.ai-chat-bubble.open {
    opacity: 0;
    transform: scale(0.8);
    pointer-events: none;
    animation: none;
}

.ai-bubble-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0 1rem;
}

.ai-bubble-text {
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.ai-assistant.minimized .ai-bubble-text {
    opacity: 1;
    transform: translateX(0);
}

.ai-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.ai-assistant.minimized .ai-avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 0.75rem;
}

/* Notification Badge */
.ai-notification-badge {
    position: absolute;
    top: -0.25rem;
    right: -0.25rem;
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
    border-radius: 50%;
    width: 1.25rem;
    height: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(238, 90, 82, 0.4);
    animation: pulse 2s infinite;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
}

.ai-notification-badge.show {
    opacity: 1;
    transform: scale(1);
}

/* Pulse Animation */
.ai-pulse {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
    width: 0.5rem;
    height: 0.5rem;
    background: #4ade80;
    border-radius: 50%;
    animation: pulse 2s infinite;
    box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
}

/* Chat Window */
.ai-chat-window {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border-radius: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.9);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
    transform: translateY(20px) scale(0.95);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 3;
}

.ai-chat-window.active {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0) scale(1);
}

.ai-assistant.minimized .ai-chat-window {
    height: 4.5rem;
    overflow: hidden;
}

/* Header */
.ai-chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    flex-shrink: 0;
}

.ai-assistant.minimized .ai-chat-header {
    padding: 1rem 1.5rem;
}

.ai-header-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.ai-header-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.ai-header-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.ai-header-title {
    font-weight: 700;
    font-size: 1rem;
    margin: 0;
    line-height: 1.2;
}

.ai-header-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    opacity: 0.9;
    margin: 0;
}

.ai-status-dot {
    width: 0.5rem;
    height: 0.5rem;
    background: #4ade80;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.ai-header-actions {
    display: flex;
    gap: 0.5rem;
}

.ai-header-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 0.5rem;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
}

.ai-header-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

/* Messages */
.ai-chat-messages {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    scroll-behavior: smooth;
}

.ai-assistant.minimized .ai-chat-messages {
    display: none;
}

.ai-message {
    display: flex;
    gap: 0.75rem;
    max-width: 100%;
    animation: messageSlide 0.3s ease-out;
}

.ai-message-bot {
    align-self: flex-start;
}

.ai-message-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.ai-message-avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.75rem;
    font-weight: 600;
}

.ai-message-bot .ai-message-avatar {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
}

.ai-message-user .ai-message-avatar {
    background: linear-gradient(135deg, #4ade80, #22c55e);
    color: #fff;
}

.ai-message-content {
    background: rgba(241, 245, 249, 0.8);
    padding: 0.875rem 1rem;
    border-radius: 1rem;
    max-width: 80%;
    border: 1px solid rgba(148, 163, 184, 0.1);
    backdrop-filter: blur(10px);
    position: relative;
}

.ai-message-user .ai-message-content {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
}

.ai-message-content p {
    margin: 0;
    line-height: 1.5;
    font-size: 0.9rem;
}

.ai-message-time {
    font-size: 0.75rem;
    opacity: 0.6;
    margin-top: 0.5rem;
    text-align: right;
}

/* Quick Actions */
.ai-quick-actions {
    display: flex;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(148, 163, 184, 0.1);
    background: rgba(248, 250, 252, 0.8);
    flex-shrink: 0;
}

.ai-assistant.minimized .ai-quick-actions {
    display: none;
}

.ai-quick-action {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.75rem;
    font-size: 0.8rem;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.ai-quick-action:hover {
    background: rgba(102, 126, 234, 0.1);
    border-color: rgba(102, 126, 234, 0.3);
    color: #667eea;
    transform: translateY(-1px);
}

/* Input Area */
.ai-chat-input-container {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid rgba(148, 163, 184, 0.1);
    background: rgba(248, 250, 252, 0.8);
    flex-shrink: 0;
}

.ai-assistant.minimized .ai-chat-input-container {
    display: none;
}

.ai-chat-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.ai-chat-input {
    width: 100%;
    padding: 0.875rem 3.5rem 0.875rem 1rem;
    border: 1px solid rgba(148, 163, 184, 0.3);
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    font-size: 0.9rem;
    transition: all 0.2s ease;
    resize: none;
    font-family: inherit;
}

.ai-chat-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.ai-input-actions {
    position: absolute;
    right: 0.5rem;
    display: flex;
    gap: 0.25rem;
}

.ai-send-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    border-radius: 0.5rem;
    width: 2.25rem;
    height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
}

.ai-send-btn:hover:not(:disabled) {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.ai-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.ai-chat-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.75rem;
}

.ai-char-counter,
.ai-powered-by {
    font-size: 0.75rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Animations */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
}

@keyframes pulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
    }
    70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px rgba(74, 222, 128, 0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(74, 222, 128, 0);
    }
}

@keyframes messageSlide {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Typing Indicator */
.ai-typing-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: rgba(241, 245, 249, 0.8);
    border-radius: 1rem;
    border: 1px solid rgba(148, 163, 184, 0.1);
    max-width: fit-content;
}

.ai-typing-dots {
    display: flex;
    gap: 4px;
}

.ai-typing-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #64748b;
    animation: typingDot 1.4s ease-in-out infinite both;
}

.ai-typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.ai-typing-dots span:nth-child(2) { animation-delay: -0.16s; }
.ai-typing-dots span:nth-child(3) { animation-delay: 0s; }

@keyframes typingDot {
    0%, 80%, 100% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Responsive Design */
@media (max-width: 640px) {
    .ai-assistant {
        bottom: 1rem;
        right: 1rem;
        width: 4rem;
        height: 4rem;
    }

    .ai-assistant.open {
        width: calc(100vw - 2rem);
        height: calc(100vh - 2rem);
        bottom: 1rem;
        right: 1rem;
    }

    .ai-assistant.minimized {
        width: calc(100vw - 2rem);
        height: 4rem;
    }

    .ai-chat-bubble {
        width: 4rem;
        height: 4rem;
    }

    .ai-quick-actions {
        flex-direction: column;
        gap: 0.5rem;
    }

    .ai-message-content {
        max-width: 85%;
    }
}

/* Scrollbar Styling */
.ai-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.ai-chat-messages::-webkit-scrollbar-track {
    background: rgba(148, 163, 184, 0.1);
    border-radius: 3px;
}

.ai-chat-messages::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.3);
    border-radius: 3px;
}

.ai-chat-messages::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.5);
}

/* Utility Classes */
.hidden {
    display: none !important;
}
</style>

<script>
class AIAssistant {
    constructor() {
        this.isOpen = false;
        this.isMinimized = false;
        this.messages = [];
        this.conversationHistory = [];
        this.ensureWidgetInBody();
        this.initializeElements();
        this.attachEventListeners();
        this.showWelcomeNotification();
    }

    ensureWidgetInBody() {
        const assistantContainer = document.getElementById('ai-assistant');
        if (!assistantContainer || assistantContainer.parentElement === document.body) {
            return;
        }
        document.body.appendChild(assistantContainer);
    }

    initializeElements() {
        this.container = document.getElementById('ai-assistant');
        this.chatBubble = document.getElementById('ai-chat-bubble');
        this.chatWindow = document.getElementById('ai-chat-window');
        this.closeButton = document.getElementById('ai-close-chat');
        this.minimizeButton = document.getElementById('ai-minimize-chat');
        this.chatInput = document.getElementById('ai-chat-input');
        this.sendButton = document.getElementById('ai-send-message');
        this.chatMessages = document.getElementById('ai-chat-messages');
        this.charCounter = document.querySelector('.ai-char-counter');
        this.notificationBadge = document.getElementById('ai-notification');
    }

    attachEventListeners() {
        // Chat bubble interactions
        this.chatBubble.addEventListener('click', () => this.toggleChat());
        this.chatBubble.addEventListener('keypress', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.toggleChat();
            }
        });

        // Window controls
        this.closeButton?.addEventListener('click', () => this.closeChat());
        this.minimizeButton?.addEventListener('click', () => this.toggleMinimize());

        // Message sending
        this.sendButton?.addEventListener('click', () => this.sendMessage());
        this.chatInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Input monitoring
        this.chatInput?.addEventListener('input', () => this.updateCharCounter());

        // Quick actions
        document.querySelectorAll('.ai-quick-action').forEach((button) => {
            button.addEventListener('click', (e) => {
                const action = e.currentTarget.getAttribute('data-action');
                this.handleQuickAction(action);
            });
        });

        // Click outside to close
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.chatWindow.contains(e.target) && !this.chatBubble.contains(e.target)) {
                this.closeChat();
            }
        });

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                if (this.isMinimized) {
                    this.toggleMinimize();
                } else {
                    this.closeChat();
                }
            }
        });
    }

    toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }

    openChat() {
        this.isOpen = true;
        this.isMinimized = false;
        this.container.classList.add('open');
        this.container.classList.remove('minimized');
        this.chatBubble.classList.add('open');
        this.chatWindow.classList.remove('hidden');
        
        requestAnimationFrame(() => {
            this.chatWindow.classList.add('active');
            this.chatWindow.setAttribute('aria-modal', 'true');
            this.chatBubble.setAttribute('aria-expanded', 'true');
        });

        this.hideNotification();
        this.chatInput?.focus();
        
        // Load conversation history if exists
        if (this.conversationHistory.length === 0 && this.messages.length === 0) {
            this.addWelcomeMessage();
        } else {
            this.renderConversationHistory();
        }
    }

    closeChat() {
        this.isOpen = false;
        this.isMinimized = false;
        this.chatWindow.classList.remove('active');
        this.chatWindow.setAttribute('aria-modal', 'false');
        this.chatBubble.setAttribute('aria-expanded', 'false');
        this.chatBubble.classList.remove('open');
        this.container.classList.remove('open', 'minimized');
        
        setTimeout(() => {
            this.chatWindow.classList.add('hidden');
        }, 400);
    }

    toggleMinimize() {
        this.isMinimized = !this.isMinimized;
        this.container.classList.toggle('minimized', this.isMinimized);
        
        if (!this.isMinimized) {
            this.chatInput?.focus();
        }
    }

    showWelcomeNotification() {
        setTimeout(() => {
            this.notificationBadge.classList.add('show');
        }, 2000);
    }

    hideNotification() {
        this.notificationBadge.classList.remove('show');
    }

    addWelcomeMessage() {
        const welcomeMessage = {
            type: 'bot',
            content: 'Cześć! 👋 Jestem Twoim asystentem AI do spraw nieruchomości. Mogę pomóc Ci w:\n\n• Dodawaniu i zarządzaniu ogłoszeniami\n• Wyszukiwaniu idealnych nieruchomości\n• Analizie cen rynkowych\n• Odpowiadaniu na pytania dotyczące platformy\n\nJak mogę Ci dziś pomóc?',
            timestamp: new Date()
        };
        this.addMessageToHistory(welcomeMessage);
        this.renderMessage(welcomeMessage);
    }

    addMessageToHistory(message) {
        this.conversationHistory.push(message);
        // Keep only last 50 messages
        if (this.conversationHistory.length > 50) {
            this.conversationHistory.shift();
        }
    }

    renderConversationHistory() {
        this.chatMessages.innerHTML = '';
        this.conversationHistory.forEach(message => this.renderMessage(message));
        this.scrollToBottom();
    }

    addUserMessage(content) {
        const message = {
            type: 'user',
            content,
            timestamp: new Date()
        };
        this.addMessageToHistory(message);
        this.renderMessage(message);
    }

    addBotMessage(content) {
        const message = {
            type: 'bot',
            content,
            timestamp: new Date()
        };
        this.addMessageToHistory(message);
        this.renderMessage(message);
    }

    renderMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.className = `ai-message ai-message-${message.type}`;

        const avatar = document.createElement('div');
        avatar.className = 'ai-message-avatar';
        avatar.innerHTML = message.type === 'bot' ? 'AI' : 'TY';

        const content = document.createElement('div');
        content.className = 'ai-message-content';

        const text = document.createElement('p');
        text.textContent = message.content;
        content.appendChild(text);

        const time = document.createElement('div');
        time.className = 'ai-message-time';
        time.textContent = this.formatTime(message.timestamp);
        content.appendChild(time);

        messageElement.appendChild(avatar);
        messageElement.appendChild(content);

        this.chatMessages.appendChild(messageElement);
        this.scrollToBottom();
    }

    formatTime(date) {
        return date.toLocaleTimeString('pl-PL', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }

    async sendMessage() {
        const message = this.chatInput?.value.trim();
        if (!message) return;

        this.addUserMessage(message);
        this.chatInput.value = '';
        this.updateCharCounter();
        this.showTypingIndicator();

        // Simulate AI response delay
        setTimeout(() => {
            this.removeTypingIndicator();
            const response = this.generateAIResponse(message);
            this.addBotMessage(response);
        }, 1000 + Math.random() * 1500);
    }

    showTypingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'ai-message ai-message-bot ai-typing-indicator';
        indicator.id = 'typing-indicator';

        indicator.innerHTML = `
            <div class="ai-message-avatar">AI</div>
            <div class="ai-message-content">
                <div class="ai-typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;

        this.chatMessages.appendChild(indicator);
        this.scrollToBottom();
    }

    removeTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        indicator?.remove();
    }

    handleQuickAction(action) {
        const actions = {
            'add-property': {
                message: 'Jak dodać nową nieruchomość?',
                response: 'Aby dodać nową nieruchomość:\n\n1. Kliknij przycisk "Dodaj nieruchomość" w panelu użytkownika\n2. Wypełnij formularz z danymi nieruchomości\n3. Dodaj wysokiej jakości zdjęcia (do 10 zdjęć)\n4. Ustaw główne zdjęcie przedstawiające nieruchomość\n5. Opublikuj ogłoszenie\n\nCzy chcesz, aby przeprowadzić Cię przez konkretny krok?'
            },
            'search-help': {
                message: 'Potrzebuję pomocy w wyszukiwaniu',
                response: 'Mogę pomóc Ci znaleźć idealną nieruchomość! 🏠\n\n• Użyj zaawansowanych filtrów w wyszukiwarce\n• Zapisz wyszukiwania, aby otrzymywać powiadomienia\n• Sprawdź mapę dla lokalizacji\n• Przeglądaj podobne oferty\n\nPowiedz mi czego szukasz (mieszkanie, dom, lokal), a pomogę Ci zawęzić wyniki!'
            },
            'contact-support': {
                message: 'Chcę skontaktować się z supportem',
                response: 'Oto sposoby kontaktu z naszym zespołem wsparcia:\n\n📞 Telefon: +48 123 456 789\n✉️ Email: support@nieruchomosci.pl\n💬 Czat live: Dostępny w godzinach 8:00-20:00\n\nNasz zespół pomoże rozwiązać każdy problem!'
            }
        };

        const actionConfig = actions[action];
        if (actionConfig) {
            this.addUserMessage(actionConfig.message);
            this.showTypingIndicator();
            
            setTimeout(() => {
                this.removeTypingIndicator();
                this.addBotMessage(actionConfig.response);
            }, 800 + Math.random() * 800);
        }
    }

    generateAIResponse(userMessage) {
        const responses = {
            'dodaj': 'Aby dodać ogłoszenie:\n\n1. Przejdź do panelu użytkownika\n2. Kliknij "Dodaj ogłoszenie"\n3. Wypełnij wszystkie wymagane pola\n4. Dodaj zdjęcia (zalecane min. 3)\n5. Opublikuj\n\nPotrzebujesz pomocy z konkretnym krokiem?',
            'kontakt': 'Możesz kontaktować się z właścicielami przez:\n• Czat na platformie\n• Telefon (jeśli podany)\n• Formularz kontaktowy\n\nWszystkie rozmowy są zapisywane w Twoim panelu.',
            'cena': 'Ceny nieruchomości zależą od:\n• Lokalizacji\n• Metrażu\n• Stanu technicznego\n• Rynku\n\nMogę pomóc w analizie cenowej konkretnej oferty!',
            'ulubione': 'Aby zapisać ofertę:\n1. Znajdź interesującą ofertę\n2. Kliknij ikonę serca\n3. Dostęp do ulubionych w panelu\n\nMożesz tworzyć różne kategorie!',
            'default': `Dziękuję za pytanie! "${userMessage}"\n\nJestem tutaj, aby pomóc Ci w:\n• Zarządzaniu nieruchomościami\n• Wyszukiwaniu ofert\n• Kontaktach z właścicielami\n• Analizie rynku\n\nCzy możesz sprecyzować, z czym dokładnie potrzebujesz pomocy? 🤗`
        };

        const lowerMessage = userMessage.toLowerCase();
        for (const [key, response] of Object.entries(responses)) {
            if (key !== 'default' && lowerMessage.includes(key)) {
                return response;
            }
        }

        return responses.default;
    }

    updateCharCounter() {
        if (!this.chatInput || !this.charCounter) return;

        const count = this.chatInput.value.length;
        this.charCounter.textContent = `${count}/500`;

        if (count > 450) {
            this.charCounter.style.color = '#ef4444';
        } else if (count > 400) {
            this.charCounter.style.color = '#f59e0b';
        } else {
            this.charCounter.style.color = '#64748b';
        }

        // Update send button state
        this.sendButton.disabled = count === 0;
    }

    scrollToBottom() {
        if (this.chatMessages) {
            this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
        }
    }
}

// Initialize AI Assistant when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AIAssistant();
});
</script>