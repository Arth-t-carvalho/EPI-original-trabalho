// ==========================================
// ARQUIVO GLOBAL - Funções Compartilhadas
// ==========================================

document.addEventListener("DOMContentLoaded", () => {
    // 1. Inicializa os ícones do Lucide automaticamente em todas as páginas
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // ==========================================
    // ANIMAÇÃO DE TRANSIÇÃO ENTRE PÁGINAS
    // ==========================================

    const mainEl = document.querySelector('main');

    // 2. Animação de ENTRADA: assim que a página carrega,
    //    o conteúdo desliza suavemente da direita para o centro.
    if (mainEl) {
        mainEl.classList.add('page-enter');
        // Remove a classe após a animação terminar para não interferir com outros estilos
        mainEl.addEventListener('animationend', () => {
            mainEl.classList.remove('page-enter');
        }, { once: true });
    }

    // 3. Animação de SAÍDA: intercepta cliques nos links da sidebar.
    //    Antes de navegar, faz o conteúdo deslizar para a esquerda.
    const navLinks = document.querySelectorAll('.nav-item');
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            // Ignora links sem href, âncoras (#), javascript: e a página atual
            if (!href || href.startsWith('#') || href.startsWith('javascript')) return;

            // Verifica se o link aponta para a página atual (sem recarregar)
            const currentPage = window.location.pathname.split('/').pop();
            const targetPage = href.split('/').pop();
            if (currentPage === targetPage) return;

            e.preventDefault();

            if (mainEl) {
                mainEl.classList.add('page-exit');
                mainEl.addEventListener('animationend', () => {
                    window.location.href = href;
                }, { once: true });
            } else {
                // Fallback: navega diretamente se não houver <main>
                window.location.href = href;
            }
        });
    });
});

// 4. Funções do Cabeçalho (Header) e Perfil
function toggleInstructorCard() {
    const card = document.getElementById('instructorCard');
    if (card) {
        card.classList.toggle('active');
    }
}

// 5. Exportar dados genérico
function exportData() {
    alert("Exportando dados...");
}

// 6. Função global de Sair/Logout
function sair() {
    window.location.href = "index.php";
}

// 7. Fecha os dropdowns (como o card de perfil) ao clicar fora deles
window.addEventListener('click', function (e) {
    const card = document.getElementById('instructorCard');
    const trigger = document.getElementById('profileTrigger');

    // Se o clique não foi no card e nem no botão que o abre, feche-o.
    if (card && trigger && !card.contains(e.target) && !trigger.contains(e.target)) {
        card.classList.remove('active');
    }
}); 