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
function togglePercentDisplay(checkbox) {
    localStorage.setItem('showPercentages', checkbox.checked);
    // Aplica na hora (função global em global.js)
    if (typeof applyPercentageVisibility === 'function') {
        applyPercentageVisibility();
    }
}

function toggleStatus() {
    const isChecked = document.getElementById('toggle-status').checked;
    localStorage.setItem('showStatusBadges', isChecked);
    // Aplica na hora (função global em global.js)
    if (typeof applyGlobalSettings === 'function') {
        applyGlobalSettings();
    }
}

// ==========================================
// 5 e 6. Gráficos (Tipo e Cor)
// ==========================================
function changeChartType(type) {
    localStorage.setItem('chartType', type);
}

function changeIndividualChartColor(type, value) {
    localStorage.setItem(`chartColor_${type}`, value);
}

function resetChartColors() {
    if (!confirm("Deseja restaurar as cores originais dos gráficos?")) return;

    localStorage.removeItem('chartColor_all');
    localStorage.removeItem('chartColor_helmet');
    localStorage.removeItem('chartColor_glasses');

    // Atualiza os inputs na tela
    document.getElementById('color-all').value = '#E30613';
    document.getElementById('color-helmet').value = '#1F2937';
    document.getElementById('color-glasses').value = '#9CA3AF';
}

function changeChartColor(color) {
    document.documentElement.style.setProperty('--chart-main-color', color);
    localStorage.setItem('chartColor', color);
}

function toggleSound(checkbox) {
    localStorage.setItem('soundEnabled', checkbox.checked);
}

// Inicializa os checkboxes com os valores do localStorage
document.addEventListener('DOMContentLoaded', () => {
    const soundToggle = document.getElementById('toggle-sound');
    if (soundToggle) {
        const soundEnabled = localStorage.getItem('soundEnabled') !== 'false';
        soundToggle.checked = soundEnabled;
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
