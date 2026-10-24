document.addEventListener('DOMContentLoaded', function () {
    const config = window.hotelGuestTools || {};
    const root = document.documentElement;

    const a11yToggle = document.getElementById('hotelAccessibilityToggle');
    const a11yPanel = document.getElementById('hotelAccessibilityPanel');
    const chatToggle = document.getElementById('hotelChatbotToggle');
    const chatPanel = document.getElementById('hotelChatbotPanel');

    function togglePanel(panel) {
        if (!panel) return;
        panel.hidden = !panel.hidden;
    }

    a11yToggle?.addEventListener('click', () => togglePanel(a11yPanel));
    chatToggle?.addEventListener('click', () => togglePanel(chatPanel));

    document.querySelectorAll('[data-close-tool]').forEach(button => {
        button.addEventListener('click', function () {
            const panel = document.getElementById(button.dataset.closeTool);
            if (panel) panel.hidden = true;
        });
    });

    let fontScale = Number(
        localStorage.getItem('hotelA11yFontScale')
        || config.defaultFontScale
        || 100
    );

    function applyFontScale() {
        root.style.fontSize = fontScale + '%';
        localStorage.setItem('hotelA11yFontScale', String(fontScale));
    }

    applyFontScale();

    document.querySelectorAll('[data-access-action]').forEach(button => {
        button.addEventListener('click', function () {
            const action = button.dataset.accessAction;

            if (action === 'font-up') {
                fontScale = Math.min(140, fontScale + 10);
                applyFontScale();
            }

            if (action === 'font-down') {
                fontScale = Math.max(80, fontScale - 10);
                applyFontScale();
            }

            if (action === 'contrast') {
                document.body.classList.toggle('a11y-high-contrast');
            }

            if (action === 'grayscale') {
                document.body.classList.toggle('a11y-grayscale');
            }

            if (action === 'links') {
                document.body.classList.toggle('a11y-links');
            }

            if (action === 'read' && 'speechSynthesis' in window) {
                window.speechSynthesis.cancel();

                const utterance = new SpeechSynthesisUtterance(
                    document.querySelector('main')?.innerText
                    || document.body.innerText
                );

                window.speechSynthesis.speak(utterance);
            }

            if (action === 'stop-read' && 'speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }

            if (action === 'reset') {
                fontScale = Number(config.defaultFontScale || 100);
                applyFontScale();

                document.body.classList.remove(
                    'a11y-high-contrast',
                    'a11y-grayscale',
                    'a11y-links'
                );

                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                }
            }
        });
    });

    const form = document.getElementById('hotelChatbotForm');
    const input = document.getElementById('hotelChatbotInput');
    const messages = document.getElementById('hotelChatbotMessages');

    function appendMessage(text, who) {
        if (!messages) return;

        const div = document.createElement('div');
        div.className = 'chat-message ' + who;
        div.textContent = text;

        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        const message = (input?.value || '').trim();

        if (!message) return;

        appendMessage(message, 'user');
        input.value = '';

        try {
            const response = await fetch(config.chatbotUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': config.csrf,
                },
                body: JSON.stringify({ message }),
            });

            const data = await response.json();

            appendMessage(
                data.reply || 'Please contact hotel reception for assistance.',
                'bot'
            );
        } catch (error) {
            appendMessage(
                'The hotel assistant is temporarily unavailable. Please contact reception.',
                'bot'
            );
        }
    });
});
