<?php
require_once __DIR__ . '/../config/database.php';
// Se usar sistema de login, mantenha a linha abaixo
// require_once __DIR__ . '/../config/auth.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Instrutor';
$cargoUsuario = $_SESSION['usuario_cargo'] ?? 'Supervisor';
$iniciais = strtoupper(substr($nomeUsuario, 0, 2));
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Controle de Sala</title>
    <link rel="stylesheet" href="../css/controleSala.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Container dos botões no rodapé do modal */
        /* Container dos botões no rodapé do modal */
        #modalFooterActions {
            display: flex;
            flex-direction: column;
            /* ALTERADO: agora empilha os botões */
            gap: 10px;
            /* Espaçamento entre um botão e outro */
            padding: 20px;
            border-top: 1px solid #eee;
            background: #fff;
            border-radius: 0 0 12px 12px;
        }

        /* Garante que ambos os botões ocupem a largura total */
        /* BOTÃO VER INFRAÇÕES - VERSÃO AMPLIADA */
    /* Estilo Base para AMBOS os botões (Garante tamanho igual) */
.btn-view-infracoes,
.btn-open-occurrence {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    
    /* Tamanho Padronizado */
    padding: 16px;            /* Espaçamento interno igual */
    min-height: 56px;         /* Altura mínima idêntica */
    border-radius: 12px;       /* Cantos arredondados iguais */
    
    /* Tipografia Padronizada */
    font-size: 15px;
    font-weight: 700;          /* Negrito igual */
    text-transform: uppercase;
    letter-spacing: 0.5px;
    
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;              /* Removemos bordas padrão */
}

/* Ícones Padronizados */
.btn-view-infracoes i,
.btn-open-occurrence i {
    width: 20px;
    height: 20px;
    stroke-width: 2.5px;      /* Ícones mais visíveis */
}

/* --- Estilos Individuais (Cores) --- */

/* 1. Botão Ver Infrações (Estilo Sóbrio/Clean) */
.btn-view-infracoes {
    background-color: #9b9ea1; /* Slate claro */
    color: #1e293b;            /* Slate escuro */
}

.btn-view-infracoes:hover {
    background-color: #e2e8f0;
    transform: translateY(-1px);
}

/* 2. Botão Abrir Ocorrência (Estilo Alerta/Vermelho) */
.btn-open-occurrence {
    background-color: #ef4444; /* Vermelho vibrante */
    color: white;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
}

.btn-open-occurrence:hover {
    background-color: #dc2626; /* Vermelho mais escuro */
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
}

        /* Botão Abrir Ocorrência (Estilo Primário/Alerta) */
        .btn-open-occurrence {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #DC2626;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-open-occurrence:hover {
            background-color: #B91C1C;
            transform: translateY(-1px);
        }

        /* Ajuste do Modal Overlay para alinhar ao centro */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-overlay.active {
            display: flex;
        }
    </style>
</head>

<body>
      <aside class="sidebar">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3"
                style="filter: drop-shadow(0 2px 4px rgba(227, 6, 19, 0.3));">
                <circle cx="12" cy="12" r="10" />
            </svg>

            &nbsp; EPI <span>GUARD</span>
        </div>

        <nav class="nav-menu">

            <a class="nav-item " href="dashboard.php">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>

            <a class="nav-item" href="infracoes.php">
                <i data-lucide="alert-triangle"></i>
                <span>Infrações</span>
            </a>

            <a class="nav-item active" href="controleSala.php">
                <i data-lucide="users"></i>
                <span>Controle de Sala</span>
            </a>

            <a class="nav-item" href="ocorrencias.php">
                <i data-lucide="file-text"></i>
                <span>Ocorrências</span>
            </a>

            <a class="nav-item" href="configuracoes.php">
                <i data-lucide="settings"></i>
                <span>Configurações</span>
            </a>
            <a class="nav-item" href="monitoramento.php">
                <i data-lucide="monitor"></i>
                <span>Monitoramento</span>
            </a>

        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <div class="page-title">
                <h1>Painel Geral</h1>
                <p>Laboratório B • Monitoramento em Tempo Real</p>
            </div>

            <div class="header-actions">
                <div class="user-profile-trigger" id="profileTrigger" onclick="toggleInstructorCard()">
                    <div class="user-info-mini">
                        <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($cargoUsuario); ?></span>
                    </div>
                    <div class="user-avatar"><?php echo $iniciais; ?></div>
                </div>
            </div>

            <div class="instructor-card" id="instructorCard">
                <div style="margin-bottom: 20px;">
                    <h3><?php echo htmlspecialchars($nomeUsuario); ?></h3>
                    <p style="color: #64748B; font-size: 13px;">ID: <?php echo $_SESSION['usuario_id'] ?? '0000'; ?></p>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color:green; font-weight:bold;">Online</span>
                </div>
                <hr style="margin: 15px 0; border:0; border-top:1px solid #eee;">
                <button class="btn-close-card" style="width:100%; padding:8px;"
                    onclick="location.href='../php/logout.php'">Sair</button>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="content-card">
                <div class="controls-bar">
                    <div class="search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" id="searchInput" placeholder="Buscar aluno...">
                    </div>
                    <select class="filter-select" id="statusFilter" name="statusFilter">
                        <option value="all">Todos os status</option>
                        <option value="Risk">🔴 Risco Ativo</option>
                        <option value="Recurrent">🟠 Reincidente</option>
                        <option value="History">🟡 Histórico</option>
                        <option value="Safe">🟢 Regular</option>
                    </select>
                </div>

                <div class="student-list" id="studentList">
                    <p style="text-align:center; padding: 20px; color: #666;">Carregando alunos...</p>
                </div>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 id="modalName" style="margin:0; font-size:18px;">Nome do Aluno</h2>
                    <small id="modalCourse" style="color:#666;">Curso...</small>
                </div>
                <button class="close-btn" onclick="closeModal()">✕</button>
            </div>

            <div style="padding: 20px;">
                <h4 style="margin-bottom: 10px; color: #333;">Checklist de EPIs:</h4>
                <div id="modalEpiList" class="epi-list"></div>
            </div>

            <div id="modalFooterActions"></div>
        </div>
    </div>

    <script src="../js/controleSala.js"></script>

    <script>
    // 1. CARREGAMENTO INICIAL (Para a Sidebar e menu lateral)
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    window.exibirDetalhesAluno = function (aluno) {
        console.log("Abrindo modal para o aluno:", aluno);

        document.getElementById('modalName').innerText = aluno.nome || 'Nome não informado';
        document.getElementById('modalCourse').innerText = aluno.curso || 'Curso não informado';

        const footer = document.getElementById('modalFooterActions');
        const nomeSeguro = (aluno.nome || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const alunoIdSafe = aluno.id || 0;

        // Inserindo os botões
        footer.innerHTML = `
            <button class="btn-view-infracoes" onclick="irParaInfracoes('${nomeSeguro}')">
                <i data-lucide="search"></i> Ver Infrações
            </button>
            <button class="btn-open-occurrence" onclick="abrirOcorrencia(${alunoIdSafe})">
                <i data-lucide="plus-circle"></i> Abrir Ocorrência
            </button>
        `;

        // 2. RE-RENDERIZAÇÃO DO MODAL (O segredo está no setTimeout)
        // Usamos um tempo de 0ms apenas para empurrar a execução para o final da fila do navegador
        setTimeout(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 10);

        const modal = document.getElementById('detailModal');
        modal.classList.add('active');
    };


    function irParaInfracoes(nomeAluno) {
        if (!nomeAluno) return;
        const nomeCodificado = encodeURIComponent(nomeAluno);
        window.location.href = `infracoes.php?periodo=todos&busca=${nomeCodificado}`;
    }

    function abrirOcorrencia(id) {
        if (!id) return;
        window.location.href = `ocorrencias.php?novo=true&aluno_id=${id}`;
    }
</script>
</body>

</html>