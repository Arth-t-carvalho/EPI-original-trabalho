// ==========================================
// DARK.JS – Funções de Toggle de Tema
// A leitura e aplicação do tema salvo é feita
// pelo global.js (presente em todas as páginas).
// Este arquivo só precisa existir em configuracoes.php
// e dashboard.php para fornecer a função toggleTheme().
// ==========================================

/**
 * Alterna entre tema claro e escuro.
 * Salva a preferência no localStorage e atualiza o body.
 * Chamada pelo switch na página de configurações.
 */
window.toggleTheme = function () {
    const isDark = document.body.getAttribute('data-theme') === 'dark';
    const newTheme = isDark ? 'light' : 'dark';

    // Aplica no <html> e no <body> (dual apply para consistência)
    if (newTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.body.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
        document.body.removeAttribute('data-theme');
    }

    // Persiste no navegador
    localStorage.setItem('theme', newTheme);

    // Atualiza o estado do switch na configuracoes.php
    const toggle = document.getElementById('toggle-darkmode');
    if (toggle) toggle.checked = (newTheme === 'dark');

    // Exibe toast de confirmação (se o container existir)
    showThemeNotification(newTheme);
};

/**
 * Exibe um toast de confirmação da mudança de tema.
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
            <span class="toast-title">Tema ${theme === 'dark' ? 'escuro' : 'claro'} ativado</span>
            <span class="toast-message">Aparência alterada com sucesso</span>
            <span class="toast-time">agora</span>
        </div>
    `;

    container.appendChild(toast);

    // Remove o toast após 3 segundos
    setTimeout(() => {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Sincroniza o tema se o usuário mudar em outra aba aberta
window.addEventListener('storage', function (e) {
    if (e.key === 'theme') {
        const toggle = document.getElementById('toggle-darkmode');
        if (e.newValue === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.setAttribute('data-theme', 'dark');
            if (toggle) toggle.checked = true;
        } else {
            document.documentElement.removeAttribute('data-theme');
            document.body.removeAttribute('data-theme');
            if (toggle) toggle.checked = false;
        }
    }
});