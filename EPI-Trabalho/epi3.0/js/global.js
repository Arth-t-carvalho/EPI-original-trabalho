// ==========================================
// ARQUIVO GLOBAL - Funções Compartilhadas
// ==========================================

document.addEventListener("DOMContentLoaded", () => {
    // 1. Inicializa os ícones do Lucide automaticamente em todas as páginas
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // 2. Animação da Sidebar: animar apenas no primeiro acesso
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        if (!sessionStorage.getItem('sidebarEntered')) {
            sidebar.classList.add('sidebar-enter');
            sessionStorage.setItem('sidebarEntered', 'true');
        } else {
            sidebar.classList.remove('sidebar-enter');
        }
    }

    // 3. Page Enter Animation
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.classList.add('page-enter');
    }

    // 4. Page Exit Animation & Sound na troca de abas pela sidebar
    const navItems = document.querySelectorAll('.nav-item');
    const transitionSound = new Audio('../som/troca_pagina.mp3');
    transitionSound.volume = 0.2;

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            const destination = this.getAttribute('href');
            
            // Só atua se for um link válido e não for a página onde já estamos
            if (destination && destination !== '#' && !this.classList.contains('active')) {
                e.preventDefault();

                // Toca som de transição
                const playPromise = transitionSound.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        console.warn("Som de transição bloqueado ou não encontrado.");
                    });
                }
                
                if (mainContent) {
                    mainContent.classList.remove('page-enter');
                    mainContent.classList.add('page-exit');
                }
                
                // Redireciona após 400ms (tempo da animação)
                setTimeout(() => {
                    window.location.href = destination;
                }, 400); 
            }
        });
    });
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
window.addEventListener('click', function(e) {
    const card = document.getElementById('instructorCard');
    const trigger = document.getElementById('profileTrigger'); 
    
    // Se o clique não foi no card e nem no botão que o abre, feche-o.
    if (card && trigger && !card.contains(e.target) && !trigger.contains(e.target)) {
        card.classList.remove('active');
    }
}); 