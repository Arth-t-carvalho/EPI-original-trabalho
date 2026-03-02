// ai_assistant.js

function toggleAIChat() {
    const window = document.getElementById('aiChatWindow');
    const btn = document.getElementById('aiToggleBtn');

    if (window.classList.contains('open')) {
        window.classList.remove('open');
        btn.style.display = 'flex';
    } else {
        window.classList.add('open');
        btn.style.display = 'none';
        document.getElementById('aiInput').focus();
    }
}

function handleAIKey(event) {
    if (event.key === 'Enter') {
        sendAIMessage();
    }
}

async function sendAIMessage() {
    const input = document.getElementById('aiInput');
    const message = input.value.trim();
    if (!message) return;

    appendMessage('user', message);
    input.value = '';

    const typingDiv = document.createElement('div');
    typingDiv.className = 'ai-message bot typing';
    typingDiv.innerText = 'Pensando...';
    document.getElementById('aiChatMessages').appendChild(typingDiv);
    scrollToBottom();

    // Verifica se é a primeira mensagem (pedido de chave)
    const isFirstTime = !localStorage.getItem('ai_configured');

    try {
        const response = await fetch('../apis/api_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message,
                include_summary: isFirstTime // Pede resumo se for a primeira vez
            })
        });

        const data = await response.json();
        typingDiv.remove();

        if (data.error) {
            appendMessage('bot', 'Erro: ' + data.error);
        } else {
            appendMessage('bot', data.reply);
            if (isFirstTime) {
                localStorage.setItem('ai_configured', 'true');
            }
        }
    } catch (error) {
        typingDiv.remove();
        appendMessage('bot', 'Erro ao conectar com o assistente.');
    }
}

function appendMessage(sender, text) {
    const container = document.getElementById('aiChatMessages');
    const div = document.createElement('div');
    div.className = `ai-message ${sender}`;
    div.innerText = text;
    container.appendChild(div);
    scrollToBottom();
}

function scrollToBottom() {
    const container = document.getElementById('aiChatMessages');
    container.scrollTop = container.scrollHeight;
}
