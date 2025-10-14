<div id="ai-assistant" class="ai-assistant">
    <div class="ai-widget-shell" aria-hidden="true"></div>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </div>
            <span class="ai-bubble-text">Potrzebujesz pomocy?</span>
        </div>
        <div class="ai-pulse" aria-hidden="true"></div>
    </div>

    <div id="ai-chat-window" class="ai-chat-window hidden" role="dialog" aria-modal="false" aria-live="polite">
        <div class="ai-chat-header">
            <div class="ai-header-content">
                <div class="ai-header-avatar" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div class="ai-header-info">
                    <h3 class="ai-header-title">Asystent AI</h3>
                    <p class="ai-header-status">Online • Gotowy do pomocy</p>
                </div>
            </div>
            <button id="ai-close-chat" class="ai-close-btn" type="button" aria-label="Zamknij czat">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="ai-chat-messages" class="ai-chat-messages">
            <div class="ai-message ai-message-bot">
                <div class="ai-message-avatar" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div class="ai-message-content">
                    <p>Cześć! Jestem Twoim asystentem AI. Jak mogę Ci pomóc w sprawach związanych z nieruchomościami?</p>
                    <div class="ai-quick-questions" role="list">
                        <button class="ai-quick-question" data-question="Jak dodać nowe ogłoszenie?">
                            Jak dodać nowe ogłoszenie?
                        </button>
                        <button class="ai-quick-question" data-question="Jak skontaktować się z właścicielem?">
                            Jak skontaktować się z właścicielem?
                        </button>
                        <button class="ai-quick-question" data-question="Jak zapisać ofertę do ulubionych?">
                            Jak zapisać ofertę do ulubionych?
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="ai-chat-input-container">
            <div class="ai-chat-input-wrapper">
                <input
                    type="text"
                    id="ai-chat-input"
                    class="ai-chat-input"
                    placeholder="Napisz wiadomość..."
                    maxlength="500"
                    autocomplete="off"
                >
                <button id="ai-send-message" class="ai-send-btn" type="button" aria-label="Wyślij wiadomość">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>
            <div class="ai-chat-footer">
                <span class="ai-char-counter" aria-live="polite">0/500</span>
                <span class="ai-powered-by">Powered by AI</span>
            </div>
        </div>
    </div>
</div>

<style>
.ai-assistant {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1000;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Chat Bubble */
.ai-chat-bubble {
    position: relative;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    outline: none;
}

.ai-chat-bubble:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 40px rgba(102, 126, 234, 0.6);
}

.ai-chat-bubble.open {
    transform: scale(0.9);
    opacity: 0;
    visibility: hidden;
}

.ai-bubble-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.ai-avatar {
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-bubble-text {
    position: absolute;
    top: -40px;
    right: 0;
    background: white;
    color: #374151;
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease;
    pointer-events: none;
}

.ai-chat-bubble:hover .ai-bubble-text {
    opacity: 1;
    transform: translateY(0);
}

.ai-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    animation: pulse-ring 2s ease-out infinite;
    opacity: 0.6;
}

@keyframes pulse-ring {
    0% { transform: scale(1); opacity: 0.6; }
    100% { transform: scale(1.4); opacity: 0; }
}

/* Chat Window */
.ai-chat-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 600px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.ai-chat-window.active {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.ai-chat-window.hidden {
    display: none;
}

/* Header */
.ai-chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: between;
}

.ai-header-content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.ai-header-avatar {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
}

.ai-header-info {
    flex: 1;
}

.ai-header-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 2px 0;
}

.ai-header-status {
    font-size: 12px;
    opacity: 0.9;
    margin: 0;
}

.ai-close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
    padding: 8px;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
}

.ai-close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

/* Messages */
.ai-chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.ai-message {
    display: flex;
    gap: 12px;
    max-width: 100%;
}

.ai-message-bot {
    align-self: flex-start;
}

.ai-message-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.ai-message-avatar {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ai-message-bot .ai-message-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.ai-message-user .ai-message-avatar {
    background: #e2e8f0;
    color: #64748b;
}

.ai-message-content {
    background: white;
    padding: 12px 16px;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    max-width: 280px;
    word-wrap: break-word;
}

.ai-message-bot .ai-message-content {
    border-bottom-left-radius: 4px;
    background: white;
}

.ai-message-user .ai-message-content {
    border-bottom-right-radius: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.ai-message-content p {
    margin: 0;
    line-height: 1.5;
    font-size: 14px;
}

/* Quick Questions */
.ai-quick-questions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 12px;
}

.ai-quick-question {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px 12px;
    font-size: 13px;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
    outline: none;
}

.ai-quick-question:hover {
    background: #e2e8f0;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

/* Input Area */
.ai-chat-input-container {
    padding: 16px 20px 20px;
    background: white;
    border-top: 1px solid #f1f5f9;
}

.ai-chat-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    transition: all 0.2s ease;
}

.ai-chat-input-wrapper:focus-within {
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.ai-chat-input {
    flex: 1;
    border: none;
    background: none;
    padding: 12px 16px;
    font-size: 14px;
    outline: none;
    color: #374151;
}

.ai-chat-input::placeholder {
    color: #9ca3af;
}

.ai-send-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    color: white;
    cursor: pointer;
    padding: 8px;
    margin: 4px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-send-btn:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.ai-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.ai-chat-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
}

.ai-char-counter {
    font-size: 12px;
    color: #64748b;
}

.ai-powered-by {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
}

/* Typing Indicator */
.ai-typing-indicator .ai-message-content {
    background: transparent;
    box-shadow: none;
    padding: 0;
}

.ai-typing-dots {
    display: flex;
    gap: 4px;
    padding: 8px 0;
}

.ai-typing-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #cbd5e1;
    animation: typing-dot 1.4s ease-in-out infinite both;
}

.ai-typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.ai-typing-dots span:nth-child(2) { animation-delay: -0.16s; }
.ai-typing-dots span:nth-child(3) { animation-delay: 0s; }

@keyframes typing-dot {
    0%, 80%, 100% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Scrollbar */
.ai-chat-messages::-webkit-scrollbar {
    width: 4px;
}

.ai-chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

.ai-chat-messages::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.ai-chat-messages::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive */
@media (max-width: 480px) {
    .ai-assistant {
        bottom: 16px;
        right: 16px;
    }
    
    .ai-chat-window {
        width: calc(100vw - 32px);
        height: 70vh;
        right: 0;
        bottom: 70px;
    }
}
</style>

<script>
// Tutaj wklej dokładnie ten sam kod JavaScript, który był w oryginalnym pliku
// (zachowuję go bez zmian, ponieważ prosiłeś tylko o poprawienie wyglądu)
class AIAssistant {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.ensureWidgetInBody();
        this.container = document.getElementById('ai-assistant');
        this.initializeElements();
        this.attachEventListeners();
        this.addWelcomeMessage();
    }

    ensureWidgetInBody() {
        const assistantContainer = document.getElementById('ai-assistant');
        if (!assistantContainer || assistantContainer.parentElement === document.body) {
            return;
        }

        document.body.appendChild(assistantContainer);
    }

    initializeElements() {
        this.chatBubble = document.getElementById('ai-chat-bubble');
        this.chatWindow = document.getElementById('ai-chat-window');
        this.closeButton = document.getElementById('ai-close-chat');
        this.chatInput = document.getElementById('ai-chat-input');
        this.sendButton = document.getElementById('ai-send-message');
        this.chatMessages = document.getElementById('ai-chat-messages');
        this.charCounter = document.querySelector('.ai-char-counter');
    }

    attachEventListeners() {
        if (!this.chatBubble || !this.chatWindow) {
            return;
        }

        this.chatBubble.addEventListener('click', () => this.toggleChat());
        this.chatBubble.addEventListener('keypress', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.toggleChat();
            }
        });
        this.closeButton?.addEventListener('click', () => this.closeChat());

        this.sendButton?.addEventListener('click', () => this.sendMessage());
        this.chatInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        this.chatInput?.addEventListener('input', () => this.updateCharCounter());

        document.querySelectorAll('.ai-quick-question').forEach((button) => {
            button.addEventListener('click', (e) => {
                const question = e.currentTarget.getAttribute('data-question');
                if (!question) {
                    return;
                }
                this.addUserMessage(question);
                this.handleQuickQuestion(question);
            });
        });

        document.addEventListener('click', (e) => {
            if (
                this.isOpen &&
                this.chatWindow &&
                !this.chatWindow.contains(e.target) &&
                !this.chatBubble.contains(e.target)
            ) {
                this.closeChat();
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
        this.container?.classList.add('open');
        this.chatBubble?.classList.add('open');
        this.chatWindow.classList.remove('hidden');
        requestAnimationFrame(() => {
            this.chatWindow.classList.add('active');
            this.chatWindow.setAttribute('aria-modal', 'true');
            this.chatBubble.setAttribute('aria-expanded', 'true');
        });
        this.chatInput?.focus();
    }

    closeChat() {
        this.isOpen = false;
        this.chatWindow.classList.remove('active');
        this.chatWindow.setAttribute('aria-modal', 'false');
        this.chatBubble.setAttribute('aria-expanded', 'false');
        this.chatBubble?.classList.remove('open');
        this.container?.classList.remove('open');
        setTimeout(() => {
            this.chatWindow.classList.add('hidden');
        }, 300);
    }

    addWelcomeMessage() {
        const welcomeMessage = {
            type: 'bot',
            content: 'Cześć! Jestem Twoim asystentem AI. Jak mogę Ci pomóc w sprawach związanych z nieruchomościami?',
            timestamp: new Date(),
        };
        this.messages.push(welcomeMessage);
    }

    addUserMessage(content) {
        const message = {
            type: 'user',
            content,
            timestamp: new Date(),
        };
        this.messages.push(message);
        this.renderMessage(message);
    }

    addBotMessage(content) {
        const message = {
            type: 'bot',
            content,
            timestamp: new Date(),
        };
        this.messages.push(message);
        this.renderMessage(message);
    }

    renderMessage(message) {
        if (!this.chatMessages) {
            return;
        }

        const messageElement = document.createElement('div');
        messageElement.className = `ai-message ai-message-${message.type}`;

        const avatar = document.createElement('div');
        avatar.className = 'ai-message-avatar';

        const content = document.createElement('div');
        content.className = 'ai-message-content';

        if (message.type === 'bot') {
            avatar.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            `;
        } else {
            avatar.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            `;
        }

        const text = document.createElement('p');
        text.textContent = message.content;
        content.appendChild(text);

        messageElement.appendChild(avatar);
        messageElement.appendChild(content);

        this.chatMessages.appendChild(messageElement);
        this.scrollToBottom();
    }

    async sendMessage() {
        if (!this.chatInput) {
            return;
        }

        const message = this.chatInput.value.trim();
        if (!message) {
            return;
        }

        this.addUserMessage(message);
        this.chatInput.value = '';
        this.updateCharCounter();

        this.showTypingIndicator();

        window.setTimeout(() => {
            this.removeTypingIndicator();
            const response = this.generateAIResponse(message);
            this.addBotMessage(response);
        }, 1000 + Math.random() * 1000);
    }

    showTypingIndicator() {
        if (!this.chatMessages) {
            return;
        }

        const indicator = document.createElement('div');
        indicator.className = 'ai-message ai-message-bot ai-typing-indicator';
        indicator.id = 'typing-indicator';

        indicator.innerHTML = `
            <div class="ai-message-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </div>
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

    handleQuickQuestion(question) {
        this.showTypingIndicator();

        window.setTimeout(() => {
            this.removeTypingIndicator();
            const response = this.generateAIResponse(question);
            this.addBotMessage(response);
        }, 1000 + Math.random() * 1000);
    }

    generateAIResponse(userMessage) {
        const responses = {
            'jak dodać nowe ogłoszenie?': 'Aby dodać nowe ogłoszenie:\n\n1. Kliknij "Dodaj ogłoszenie" w panelu użytkownika\n2. Wypełnij formularz z danymi nieruchomości\n3. Dodaj zdjęcia (maksymalnie 5)\n4. Ustaw główne zdjęcie\n5. Opublikuj ogłoszenie\n\nCzy chcesz, aby przeprowadzić Cię przez ten proces krok po kroku?',
            'jak skontaktować się z właścicielem?': 'Aby skontaktować się z właścicielem:\n\n1. Znajdź interesującą ofertę\n2. Kliknij "Kontakt z właścicielem"\n3. Napisz wiadomość w otwartym czacie\n4. Czekaj na odpowiedź\n\nWszystkie rozmowy są zapisywane w Twoim panelu wiadomości.',
            'jak zapisać ofertę do ulubionych?': 'Aby zapisać ofertę do ulubionych:\n\n1. Znajdź ofertę, którą chcesz zapisać\n2. Kliknij przycisk "Zapisz" (ikona serca)\n3. Oferta pojawi się w zakładce "Ulubione"\n\nMożesz później łatwo wrócić do zapisanych ofert w swoim panelu.',
            default: `Rozumiem, że potrzebujesz pomocy z: "${userMessage}". Niestety, jestem jeszcze w fazie rozwoju i moje możliwości są ograniczone. W przyszłości będę mógł pomóc Ci w:\n\n• Wyszukiwaniu idealnych nieruchomości\n• Analizie cen rynkowych\n• Negocjacji warunków\n• Organizacji oglądania\n\nNa razie mogę odpowiedzieć na podstawowe pytania dotyczące funkcjonalności strony.`,
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
        if (!this.chatInput || !this.charCounter) {
            return;
        }

        const count = this.chatInput.value.length;
        this.charCounter.textContent = `${count}/500`;

        if (count > 450) {
            this.charCounter.style.color = '#ef4444';
        } else if (count > 400) {
            this.charCounter.style.color = '#f59e0b';
        } else {
            this.charCounter.style.color = '#64748b';
        }
    }

    scrollToBottom() {
        if (!this.chatMessages) {
            return;
        }
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new AIAssistant();
});
</script>