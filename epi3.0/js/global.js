// ==========================================
// ARQUIVO GLOBAL - Funções Compartilhadas
// ==========================================

document.addEventListener("DOMContentLoaded", () => {
    // 1. Inicializa os ícones do Lucide automaticamente em todas as páginas
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// 2. Funções do Cabeçalho (Header) e Perfil
function toggleInstructorCard() {
    const card = document.getElementById('instructorCard');
    if (card) {
        card.classList.toggle('active');
    }
}

// 3. Exportar dados genérico
function exportData() {
    alert("Exportando dados...");
}

// 4. Função global de Sair/Logout
function sair() {
    window.location.href = "index.php";
}

// 5. Fecha os dropdowns (como o card de perfil) ao clicar fora deles
window.addEventListener('click', function (e) {
    const card = document.getElementById('instructorCard');
    const trigger = document.getElementById('profileTrigger');

    // Se o clique não foi no card e nem no botão que o abre, feche-o.
    if (card && trigger && !card.contains(e.target) && !trigger.contains(e.target)) {
        card.classList.remove('active');
    }
});

/**
 * 6. Controle de Configurações Visuais (KPIs, Porcentagens, Status)
 * Aplica globalmente baseado na preferência salva no LocalStorage
 */
function applyGlobalSettings() {
    const showPercentages = localStorage.getItem('showPercentages') !== 'false';
    const showStatus = localStorage.getItem('showStatusBadges') !== 'false';

    // 1. Porcentagens (Badges Up/Down)
    const badges = document.querySelectorAll('.badge.up, .badge.down, #badgeDia, #badgeSemana, #badgeMes');
    badges.forEach(b => {
        b.style.display = showPercentages ? 'inline-block' : 'none';
    });

    // 2. Centralização de Números nos KPIs (Se tirar % deve centralizar)
    const kpiValues = document.querySelectorAll('.kpi-value');
    kpiValues.forEach(kv => {
        if (!showPercentages) {
            kv.style.justifyContent = 'center';
        } else {
            // Se as porcentagens voltarem, alinhar à esquerda (ou base) depende do design
            kv.style.justifyContent = 'flex-start';
        }
    });

    // 3. Status de Conformidade (🚨 CRÍTICO, etc)
    const statusBadges = document.querySelectorAll('.status-badge');
    statusBadges.forEach(sb => {
        sb.style.display = showStatus ? 'inline-block' : 'none';
    });
}

// Alias para manter compatibilidade com arquivos que já chamam a anterior
function applyPercentageVisibility() { applyGlobalSettings(); }

// Inicializa no carregamento global
document.addEventListener('DOMContentLoaded', applyGlobalSettings);

// --- Modal de Confirmação Customizado ---
let confirmationCallback = null;

window.openConfirmModal = function (title, text, callback) {
    const modal = document.getElementById('modalConfirm');
    if (!modal) {
        console.error("Erro: Elemento 'modalConfirm' não encontrado.");
        return;
    }

    const titleEl = document.getElementById('confirmTitle');
    const textEl = document.getElementById('confirmText');

    if (titleEl) titleEl.innerText = title;
    if (textEl) textEl.innerText = text;

    confirmationCallback = callback;
    modal.classList.add('active');
    modal.style.display = 'flex'; // Forçar visibilidade

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
};

window.closeConfirmModal = function () {
    const modal = document.getElementById('modalConfirm');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
    confirmationCallback = null;
};

window.handleConfirmAction = function () {
    if (confirmationCallback) {
        confirmationCallback();
    }
    closeConfirmModal();
};