// Verifica se o JS carregou
console.log("Infracoes.js carregado com sucesso.");

// Seleciona elementos do Modal
const modal = document.getElementById('imageModal');
const modalImg = document.getElementById('modalImg');
const modalName = document.getElementById('modalName');
const modalDesc = document.getElementById('modalDesc');
const modalTime = document.getElementById('modalTime');
const btnAssinar = document.getElementById('btnAssinar');

// --- FUNÇÃO PRINCIPAL ---
// Chamada pelo onclick do PHP
function openModalPHP(imgUrl, nome, epi, horaTexto, dataCompleta) {
    console.log("Tentando abrir modal:", nome); // Debug

    if (!modal) {
        console.error("Erro: Modal não encontrado no HTML!");
        return;
    }

    // 1. Preenche os dados visuais
    if (modalImg) modalImg.src = imgUrl;
    if (modalName) modalName.innerText = nome;
    if (modalDesc) modalDesc.innerText = "Falta de: " + epi;
    if (modalTime) modalTime.innerText = "Horário: " + horaTexto;

    // 2. Configura o botão vermelho
    if (btnAssinar) {
        // Remove eventos antigos clonando o botão (opcional, mas evita cliques duplos)
        const novoBotao = btnAssinar.cloneNode(true);
        btnAssinar.parentNode.replaceChild(novoBotao, btnAssinar);

        novoBotao.onclick = function () {
            const params = new URLSearchParams({
                aluno: nome,
                epi: epi,
                data: dataCompleta
            });
            window.location.href = `ocorrencias.php?${params.toString()}`;
        };
    }

    // 3. Mostra o modal
    modal.classList.add('active');
}

// Fecha ao clicar fora (no fundo escuro)
function closeModal(e) {
    if (e.target === modal) {
        modal.classList.remove('active');
    }
}

// Fecha ao clicar no X
function forceClose() {
    if (modal) modal.classList.remove('active');
}

// --- OUTROS ---
// Função de Transição de Página (Sidebar)
document.addEventListener("DOMContentLoaded", () => {
    const links = document.querySelectorAll('a.nav-item');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('javascript:')) return;
            e.preventDefault();
            document.body.classList.add('page-exit');
            setTimeout(() => { window.location.href = href; }, 300);
        });
    });
});

function openModalPHP(src, nome, epi, hora, dataCompleta) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');
    const modalName = document.getElementById('modalName');
    const modalDesc = document.getElementById('modalDesc');
    const modalTime = document.getElementById('modalTime');

    // Define os valores no modal
    modalImg.src = src;
    modalName.innerText = nome;
    modalDesc.innerText = "Infração: " + epi;
    modalTime.innerText = "Horário: " + hora + " | Data: " + dataCompleta;

    // Exibe o modal
    modal.classList.add('active');
}

function closeModal(event) {
    if (event.target.id === 'imageModal') {
        forceClose();
    }
}

function forceClose() {
    const modal = document.getElementById('imageModal');
    modal.classList.remove('active');
    // Limpa a imagem para não aparecer a anterior ao abrir um novo card
    document.getElementById('modalImg').src = "";
}