// ==========================================
// SISTEMA DE TEMA (DARK MODE)
// ==========================================

/**
 * 1. Inicialização do Tema
 * Garante que o tema claro seja o padrão, a menos que o usuário tenha escolhido o escuro anteriormente.
 */
function loadTheme() {
    const savedTheme = localStorage.getItem('theme');
    const toggleCheckbox = document.getElementById('toggle-darkmode');

    // Se não houver tema salvo, o padrão será 'light'
    if (savedTheme === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
        document.body.classList.add('dark-mode');
        if (toggleCheckbox) {
            toggleCheckbox.checked = true;
        }
    } else {
        // Garantir que inicie no modo claro
        document.body.removeAttribute('data-theme');
        document.body.classList.remove('dark-mode');
        if (toggleCheckbox) {
            toggleCheckbox.checked = false;
        }
        // Se for o primeiro acesso, definimos explicitamente como light
        if (!savedTheme) {
            localStorage.setItem('theme', 'light');
        }
    }
}

/**
 * 2. Alternância de Tema
 * Chamada pelo botão de switch nas configurações
 */
window.toggleTheme = function () {
    const body = document.body;
    const isDark = body.getAttribute('data-theme') === 'dark' || body.classList.contains('dark-mode');
    const newTheme = isDark ? 'light' : 'dark';

    if (newTheme === 'dark') {
        body.setAttribute('data-theme', 'dark');
        body.classList.add('dark-mode');
    } else {
        body.removeAttribute('data-theme');
        body.classList.remove('dark-mode');
    }

    localStorage.setItem('theme', newTheme);

    // Opcional: Mostrar notificação
    if (typeof showThemeNotification === 'function') {
        showThemeNotification(newTheme);
    }
}

/**
 * 3. Notificação Toast
 */
function showThemeNotification(theme) {
    const container = document.getElementById('notification-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `
        <div class="toast-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 16v-4M12 8h.01"></path>
            </svg>
        </div>
        <div class="toast-content">
            <span class="toast-title">Tema ${theme === 'dark' ? 'Escuro' : 'Claro'} Ativado</span>
            <span class="toast-message">Aparência alterada com sucesso</span>
            <span class="toast-time">agora</span>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * 4. Sincronização entre abas
 */
window.addEventListener('storage', function (e) {
    if (e.key === 'theme') {
        loadTheme();
    }
});

// Inicializa quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', loadTheme);