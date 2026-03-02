// Inicializa ícones do Lucide
lucide.createIcons();

// ==========================================
// 1. Interatividade (Links nos Cards)
// ==========================================
function toggleLinkAbility(checkboxElement) {
    // Pegamos direto o status do botão que foi clicado
    const linksEnabled = checkboxElement.checked;

    // Salva a preferência no navegador
    localStorage.setItem('linksEnabled', linksEnabled);

    // Adiciona ou remove a classe dos cards
    const cards = document.querySelectorAll('.card, .violation-card');
    cards.forEach(c => {
        if (linksEnabled) c.classList.add('clickable');
        else c.classList.remove('clickable');
    });
}

function handleCardClick(cardId) {
    // Verifica no storage se os links estão liberados
    const isEnabled = localStorage.getItem('linksEnabled') === 'true';
    if (isEnabled) {
        alert(`Redirecionando para detalhes de: ${cardId}`);
        // window.location.href = 'infracoes.php?filtro=' + cardId;
    }
}

// ==========================================
// 3 e 4. Interface (Porcentagem e Status)
// ==========================================
function toggleVisibility(selector) {
    const isChecked = document.getElementById('toggle-percent').checked;
    document.querySelectorAll(selector).forEach(el => {
        el.style.display = isChecked ? 'inline' : 'none';
    });
}

function toggleStatus() {
    const isChecked = document.getElementById('toggle-status').checked;
    document.querySelectorAll('.status-wrapper').forEach(el => {
        if (!isChecked) {
            el.style.background = 'transparent';
            el.style.border = 'none';
            el.style.color = 'var(--text-muted)';
            if (el.querySelector('svg')) el.querySelector('svg').style.display = 'none';
        } else {
            el.style.background = '';
            el.style.border = '';
            el.style.color = '';
            if (el.querySelector('svg')) el.querySelector('svg').style.display = 'inline';
        }
    });
}

// ==========================================
// 5 e 6. Gráficos (Tipo e Cor)
// ==========================================
function changeChartType(type) {
    document.getElementById('chart-donut').style.display = 'none';
    document.getElementById('chart-bar').style.display = 'none';
    document.getElementById('chart-line').style.display = 'none';

    if (type === 'donut') document.getElementById('chart-donut').style.display = 'flex';
    if (type === 'bar') document.getElementById('chart-bar').style.display = 'flex';
    if (type === 'line') document.getElementById('chart-line').style.display = 'block';

    // Opcional: Salvar o tipo de gráfico no LocalStorage também
    localStorage.setItem('chartType', type);
}

function changeChartColor(color) {
    // Muda a cor na hora
    document.documentElement.style.setProperty('--chart-main-color', color);

    // Salva a cor escolhida no navegador para não perder no F5
    localStorage.setItem('chartColor', color);
}

function toggleSound(checkbox) {
    localStorage.setItem('soundEnabled', checkbox.checked);
}

// Salva se o som de transição de página deve tocar - Josue
function toggleNavSound(checkbox) {
    localStorage.setItem('navSoundEnabled', checkbox.checked);
}

// Salva a preferência de receber alertas por e-mail no navegador - Josue
function toggleEmailNotification(checkbox) {
    localStorage.setItem('emailNotificationEnabled', checkbox.checked);
}

// Inicializa os checkboxes com os valores do localStorage
document.addEventListener('DOMContentLoaded', () => {
    const soundToggle = document.getElementById('toggle-sound');
    if (soundToggle) {
        const soundEnabled = localStorage.getItem('soundEnabled') !== 'false';
        soundToggle.checked = soundEnabled;
    }

    const navSoundToggle = document.getElementById('toggle-nav-sound');
    if (navSoundToggle) {
        // Recupera o estado do som de navegação salvo anteriormente - Josue
        const navSoundEnabled = localStorage.getItem('navSoundEnabled') !== 'false';
        navSoundToggle.checked = navSoundEnabled;
    }

    const emailToggle = document.getElementById('toggle-email-notification');
    if (emailToggle) {
        // Carrega se as notificações de e-mail estão ativas - Josue
        emailToggle.checked = localStorage.getItem('emailNotificationEnabled') !== 'false';
    }

    const linksToggle = document.getElementById('toggle-link');
    if (linksToggle) {
        linksToggle.checked = localStorage.getItem('linksEnabled') === 'true';
    }

    const percentToggle = document.getElementById('toggle-percent');
    if (percentToggle) {
        percentToggle.checked = localStorage.getItem('showPercentages') !== 'false';
    }

    const statusToggle = document.getElementById('toggle-status');
    if (statusToggle) {
        statusToggle.checked = localStorage.getItem('showStatusBadges') !== 'false';
    }

    // Inicializa Tipo de Gráfico
    const chartTypeSelect = document.querySelector('select[onchange="changeChartType(this.value)"]');
    if (chartTypeSelect) {
        chartTypeSelect.value = localStorage.getItem('chartType') || 'bar';
    }

    // Inicializa Cores
    const colorAll = document.getElementById('color-all');
    if (colorAll) colorAll.value = localStorage.getItem('chartColor_all') || '#E30613';

    const colorHelmet = document.getElementById('color-helmet');
    if (colorHelmet) colorHelmet.value = localStorage.getItem('chartColor_helmet') || '#1F2937';

    const colorGlasses = document.getElementById('color-glasses');
    if (colorGlasses) colorGlasses.value = localStorage.getItem('chartColor_glasses') || '#9CA3AF';

    // Inicializa visibilidade globalmente ao carregar a página
    if (typeof applyPercentageVisibility === 'function') applyPercentageVisibility();
});
