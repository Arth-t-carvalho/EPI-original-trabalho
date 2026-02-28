/**
 * Sistema de Notificações e Ações do Header
 */
function updateNotificationBadge() {
    const lastSeenId = localStorage.getItem('last_seen_id') || 0;
    const lastNotifiedId = localStorage.getItem('last_notified_id') || lastSeenId;
    const isPageInfracoes = window.location.pathname.includes('infracoes.php');

    // Passamos o Menor ID entre os dois para garantir que o banco nos mande tudo que precisamos
    // Porém a lógica de EXIBIÇÃO vai filtrar o que já foi notificado.
    const queryId = lastSeenId;

    // Busca a contagem de novas notificações (ID > lastSeenId)
    // Passamos details=1 para receber os dados dos novos alunos
    fetch(`../apis/api.php?action=notification_count&seen_id=${queryId}&details=1`)
        .then(res => res.json())
        .then(data => {
            // Se estivermos na página de infrações, marcamos tudo como visto
            // Fazemos isso ANTES do check do badge para garantir que o localStorage atualize
            // mesmo em páginas que não tem o ícone de sino (como a própria infracoes.php)
            if (isPageInfracoes && data.max_id > lastSeenId) {
                localStorage.setItem('last_seen_id', data.max_id);
                localStorage.setItem('last_notified_id', data.max_id);
                // Não damos return aqui para que os Toasts ainda possam aparecer se o usuário quis
            }

            const badge = document.getElementById('notifBadge');

            // Exibe Toasts apenas para o que ainda NÃO foi notificado
            if (data.new_items && data.new_items.length > 0) {
                const soundEnabled = localStorage.getItem('soundEnabled') !== 'false';
                let alertPlayed = false;
                let maxIdNesteLote = lastNotifiedId;

                data.new_items.forEach(item => {
                    if (item.id > lastNotifiedId) {
                        const toastId = `toast-${item.id}`;
                        if (!document.getElementById(toastId)) {
                            showToast(item.aluno, item.epi, item.id);

                            if (item.id > maxIdNesteLote) maxIdNesteLote = item.id;

                            // Toca o som apenas uma vez por lote de notificações
                            if (soundEnabled && !alertPlayed) {
                                // Tenta carregar o som de forma flexível (relativo à página em /php/)
                                const som = new Audio('../som/notificacao.mp3');
                                som.volume = 1.0;
                                som.load();
                                som.play().catch(e => {
                                    console.warn("Áudio da notificação bloqueado pelo navegador. É necessário interação prévia com a página.");
                                });
                                alertPlayed = true;
                            }
                        }
                    }
                });

                // Atualiza o rastreador de notificações para não repetir o Pop-up
                if (maxIdNesteLote > lastNotifiedId) {
                    localStorage.setItem('last_notified_id', maxIdNesteLote);
                }
            }

            // Atualiza o Badge se ele existir na página atual
            if (badge) {
                if (data.count > 0 && !isPageInfracoes) {
                    badge.style.display = 'flex';
                    badge.innerText = data.count > 9 ? '9+' : data.count;
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(err => console.error('Erro ao buscar notificações:', err));
}

function showToast(aluno, epi, id) {
    // Cria container se não existir
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.id = `toast-${id}`;

    toast.innerHTML = `
        <div class="toast-icon">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <div class="toast-content">
            <div class="toast-title">Nova Infração Detectada</div>
            <div class="toast-msg"><strong>${aluno}</strong> está sem <strong>${epi}</strong></div>
        </div>
    `;

    toast.onclick = () => {
        window.location.href = 'infracoes.php';
    };

    container.appendChild(toast);

    // Remove após 6 segundos
    setTimeout(() => {
        toast.classList.add('toast-out');
        setTimeout(() => toast.remove(), 400);
    }, 6000);
}

// Inicializa e define intervalo de 10 segundos
document.addEventListener('DOMContentLoaded', () => {
    updateNotificationBadge();
    setInterval(updateNotificationBadge, 10000);
});

// Funções utilitárias
function testNotificationSound() {
    const som = new Audio('../som/notificacao.mp3');
    som.volume = 1.0;
    som.load();
    som.play().catch(e => alert("O navegador bloqueou o som. Clique na página e tente de novo."));
}

