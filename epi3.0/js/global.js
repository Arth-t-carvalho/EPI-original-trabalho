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
window.addEventListener('click', function(e) {
    const card = document.getElementById('instructorCard');
    const trigger = document.getElementById('profileTrigger'); 
    
    // Se o clique não foi no card e nem no botão que o abre, feche-o.
    if (card && trigger && !card.contains(e.target) && !trigger.contains(e.target)) {
        card.classList.remove('active');
    }
}); 