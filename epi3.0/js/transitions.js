// --- transitions.js Optimized ---

(function () {
    function initTransitions() {
        const mainContent = document.querySelector('.main-content');
        if (!mainContent) return;

        // 1. Entrada Fluida: Adiciona a classe de entrada com um micro-delay
        // Isso garante que o navegador renderizou o estado inicial (opacity 0)
        requestAnimationFrame(() => {
            mainContent.style.opacity = "1";
            mainContent.style.transform = "translateX(0)";
            mainContent.classList.add('page-enter-active');
        });

        // 2. Interceptador de Cliques Global
        document.addEventListener('click', (e) => {
            // Busca o link mais próximo (caso clique no ícone/span dentro do <a>)
            const link = e.target.closest('a');

            if (link) {
                const href = link.getAttribute('href');

                // Critérios para aplicar a transição:
                // - Href existe e não é uma âncora (#)
                // - É um link interno (mesmo domínio ou relativo)
                // - Não abre em nova aba (_blank)
                const isInternal = href && (!href.startsWith('http') || (href.includes(window.location.host)));

                if (isInternal && !href.startsWith('#') && !href.startsWith('javascript:') &&
                    !link.hasAttribute('target') && !link.classList.contains('no-transition') &&
                    !href.includes('logout.php')) {

                    e.preventDefault();
                    performTransition(href);
                }
            }
        });
    }

    //eu alterei essa função aqui - Pirra
    function performTransition(url) {
        const mainContent = document.querySelector('.main-content');
        const navSoundEnabled = localStorage.getItem('navSoundEnabled') !== 'false';
        const transitionSound = new Audio('../som/troca_pagina.mp3');
        transitionSound.volume = 0.2;

        if (mainContent) {

            if (navSoundEnabled) {
                transitionSound.play().catch(e => console.warn("Erro ao tocar som de transição:", e));
            }

            // Aplica estado de saída
            mainContent.classList.remove('page-enter-active');
            mainContent.classList.add('page-exit');

            // Navega após um tempo menor (250ms em vez de 400ms) para ser mais "snap"
            setTimeout(() => {
                window.location.href = url;
            }, 300);
        } else {
            window.location.href = url;
        }
    }

    // Função Global Exportada
    window.navigateTo = performTransition;

    // Inicializa
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTransitions);
    } else {
        initTransitions();
    }
})();
