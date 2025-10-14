<div class="ai-assistant-widget" aria-live="polite">
    <button id="ai-assistant-toggle" class="ai-assistant-bubble" aria-expanded="false" aria-controls="ai-assistant-panel">
        <span class="ai-assistant-icon" aria-hidden="true">🤖</span>
        <span class="ai-assistant-label">Porozmawiaj z AI</span>
    </button>
    <section id="ai-assistant-panel" class="ai-assistant-panel" aria-hidden="true">
        <header class="ai-assistant-panel__header">
            <div>
                <p class="ai-assistant-panel__title">Asystent AI</p>
                <p class="ai-assistant-panel__subtitle">Wkrótce połączymy Cię z inteligentnym doradcą.</p>
            </div>
            <button type="button" class="ai-assistant-close" id="ai-assistant-close" aria-label="Zamknij okno asystenta">
                &times;
            </button>
        </header>
        <div class="ai-assistant-panel__body">
            <p>
                Już niedługo w tym miejscu pojawi się możliwość bezpośredniego kontaktu z asystentem AI,
                który pomoże Ci znaleźć idealną ofertę najmu.
            </p>
            <ul class="ai-assistant-panel__list">
                <li>Odpowie na pytania dotyczące ofert.</li>
                <li>Pomoże w procesie wynajmu krok po kroku.</li>
                <li>Przygotuje propozycje dopasowane do Twoich potrzeb.</li>
            </ul>
            <p class="ai-assistant-panel__footer">Kliknij w dymek, aby ukryć lub pokazać panel.</p>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('ai-assistant-toggle');
    const panel = document.getElementById('ai-assistant-panel');
    const closeButton = document.getElementById('ai-assistant-close');

    if (!toggleButton || !panel) {
        return;
    }

    const hidePanel = () => {
        panel.classList.remove('ai-assistant-panel--open');
        toggleButton.setAttribute('aria-expanded', 'false');
        panel.setAttribute('aria-hidden', 'true');
    };

    const showPanel = () => {
        panel.classList.add('ai-assistant-panel--open');
        toggleButton.setAttribute('aria-expanded', 'true');
        panel.setAttribute('aria-hidden', 'false');
    };

    toggleButton.addEventListener('click', () => {
        if (panel.classList.contains('ai-assistant-panel--open')) {
            hidePanel();
        } else {
            showPanel();
        }
    });

    if (closeButton) {
        closeButton.addEventListener('click', hidePanel);
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && panel.classList.contains('ai-assistant-panel--open')) {
            hidePanel();
            toggleButton.focus();
        }
    });
});
</script>
