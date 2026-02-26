<?php
// Mantendo a estrutura de autenticação e banco de dados
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Monitoramento</title>
    <link rel="stylesheet" href="../css/Ocorrencia.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* ==========================================
           TEMA CLARO - ESTILO APPLE (CLEAN & MINIMAL)
           ========================================== */
        
        .meet-wrapper {
            background-color: #f5f5f7; /* Fundo cinza super claro (padrão Apple) */
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1d1d1f; /* Texto escuro suave */
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            height: calc(100vh - 120px);
            margin-top: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); /* Sombra difusa e suave */
            border: 1px solid rgba(255, 255, 255, 0.6);
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .meet-header-info {
            height: 54px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            font-size: 14px;
            font-weight: 500;
            background: #ffffff;
            border-bottom: 1px solid #e5e5ea;
        }

        .meet-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #515154;
        }

        .meet-main {
            flex: 1;
            display: flex;
            padding: 16px;
            gap: 16px;
            overflow: hidden;
            background-color: #f5f5f7;
            transition: all 0.3s ease;
        }

        /* Área de Vídeo */
        .meet-presentation {
            flex: 3;
            background: #ffffff;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            transition: flex 0.4s ease;
        }

        .editor-header {
            height: 40px;
            background: transparent;
            display: flex;
            align-items: center;
            padding: 0 20px;
            font-size: 13px;
            font-weight: 600;
            color: #86868b;
            border-bottom: 1px solid #f0f0f2;
        }

        .editor-content {
            flex: 1;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000; /* Fundo do vídeo se mantém preto para contraste da imagem */
            overflow: hidden;
        }

        .editor-content img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Painel Lateral - Chat e Logs (Expandido) */
        .meet-right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 400px;
            transition: all 0.4s ease;
        }

        .chat-panel {
            flex: 1;
            background: #ffffff;
            border-radius: 20px;
            color: #1d1d1f;
            display: flex;
            flex-direction: column;
            padding: 24px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        }

        .chat-header {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-subtitle {
            font-size: 12px;
            color: #34c759; /* Verde estilo iOS */
            font-weight: 500;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .chat-subtitle::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #34c759;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 199, 89, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(52, 199, 89, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 199, 89, 0); }
        }

        .chat-logs {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            overflow-y: auto;
        }

        .chat-msg {
            background: #f5f5f7;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.5;
            color: #1d1d1f;
            border: 1px solid #e5e5ea;
        }

        .msg-alert {
            border-left: 4px solid #ff3b30; /* Vermelho alerta Apple */
        }

        /* Controles Inferiores */
        .meet-footer {
            height: 88px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            background: #ffffff;
            border-top: 1px solid #e5e5ea;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }

        .meeting-details {
            font-size: 15px;
            font-weight: 500;
            color: #515154;
        }

        .controls {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .btn-meet {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 1px solid #e5e5ea;
            background: #ffffff;
            color: #1d1d1f;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }

        .btn-meet:hover {
            background: #f5f5f7;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }

        .btn-end {
            background: #ff3b30; /* Vermelho vibrante */
            color: white;
            width: 72px;
            border-radius: 24px;
            border: none;
        }

        .btn-end:hover {
            background: #d32f2f;
        }

        /* Ferramentas do lado direito do Footer */
        .right-tools {
            display: flex;
            gap: 20px;
            color: #515154;
            align-items: center;
        }

        .right-tools i {
            cursor: pointer;
            transition: color 0.2s;
        }

        .right-tools i:hover {
            color: #007aff; /* Azul Apple */
        }

        /* Menu de Opções de Layout (Dropdown) */
        .layout-menu-container {
            position: relative;
        }
        
        
        .layout-dropdown {
            position: absolute;
            bottom: 60px;
            left: 50%;
            transform: translateX(-50%) translateY(10px);
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            padding: 8px;
            width: 200px;
            display: flex;
            flex-direction: column;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            border: 1px solid #e5e5ea;
            z-index: 100;
        }

        .layout-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        .layout-option {
            padding: 12px 16px;
            font-size: 14px;
            color: #1d1d1f;
            cursor: pointer;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s;
        }

        .layout-option:hover {
            background: #f5f5f7;
        }

        .layout-option.selected {
            color: #007aff;
            font-weight: 500;
            background: #f0f8ff;
        }

        /* Estilo Dinâmico via JS (Modo Expandido) */
        .meet-wrapper.layout-expanded .meet-right-panel {
            display: none; /* Esconde o log de sistema */
        }
        .meet-wrapper.layout-expanded .meet-presentation {
            flex: 1; /* Câmera ocupa a tela toda */
        }
        /* ==========================================
           CARDS DE INFRAÇÃO (COM SOMBRA FORTE)
           ========================================== */
        .chat-logs {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
            overflow-y: auto;
            padding-right: 5px; /* Espaço para a barra de rolagem */
        }

        /* Estilizando a barra de rolagem para ficar mais limpa */
        .chat-logs::-webkit-scrollbar {
            width: 6px;
        }
        .chat-logs::-webkit-scrollbar-thumb {
            background-color: #d1d1d6;
            border-radius: 10px;
        }

        .infraction-card {
            background: #ffffff;
            border-left: 4px solid #ff3b30; /* Vermelho alerta */
            /* SOMBRA FORTE SOLICITADA */
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.4), 0 4px 12px -2px rgba(0, 0, 0, 0.3); 
            border-radius: 12px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0; /* Impede que o card seja esmagado */
            animation: dropIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .infraction-icon {
            color: #ff3b30;
            background: #ffe5e5;
            padding: 8px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px -2px rgba(255, 59, 48, 0.3);
        }

        .infraction-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .infraction-title {
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 2px;
            color: #1d1d1f;
        }

        .infraction-message {
            font-size: 12px;
            color: #515154;
        }

        .infraction-time {
            font-size: 11px;
            font-weight: 600;
            color: #ff3b30;
            margin-top: 4px;
        }

        @keyframes dropIn {
            0% { transform: translateY(-20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
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

              EPI <span>GUARD</span>
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

            <a class="nav-item" href="controleSala.php">
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
            <a class="nav-item " href="monitoramento.php">
                <i data-lucide="monitor"></i>
                <span>Monitoramento</span>
            </a>

        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <div class="page-title">
                <h1>Monitoramento de Laboratório</h1>
                <p>Laboratório B • Câmera Ao Vivo</p>
            </div>
            <div class="header-actions">
                <div class="user-profile-trigger" id="profileTrigger" onclick="toggleInstructorCard()">
                    <div class="user-info-mini">
                        <span class="user-name">João Silva</span>
                        <span class="user-role">Téc. Segurança</span>
                    </div>
                    <div class="user-avatar">JS</div>
                </div>
            </div>
            </header>

        <div class="meet-wrapper" id="meetWrapper">
            <div class="meet-header-info">
                <div class="meet-user-info">
                    <i data-lucide="shield-check" style="color: #34c759; width: 18px;"></i>
                    Visualizando como: <strong>Professor Logado</strong>
                </div>
            </div>

            <div class="meet-main">
             <section class="meet-presentation">
                    <div class="editor-header">
                        Câmera Principal - Lab B
                    </div>
                    <div class="editor-content" style="position: relative;">
                        <div id="camera-off-text" style="display: none; position: absolute; flex-direction: column; align-items: center; color: white; z-index: 1;">
                            <i data-lucide="video-off" size="48" style="color: #ff3b30; margin-bottom: 10px;"></i>
                            <h2 style="margin: 0; font-size: 24px; font-weight: 500;">Câmera Desligada</h2>
                            <p style="color: #86868b; font-size: 14px; margin-top: 5px;">Conexão de vídeo interrompida.</p>
                        </div>
                        
                        <img id="camera-feed" src="http://localhost:5000/video_feed" alt="Câmera do Python Ao Vivo" style="position: relative; z-index: 2; transition: opacity 0.3s ease;">
                    </div>
                </section>

              <aside class="meet-right-panel">
                    <div class="chat-panel">
                        <div class="chat-header">
                            Infrações Recentes
                            <i data-lucide="alert-triangle" size="18" style="color: #ff3b30;"></i>
                        </div>
                        <div class="chat-subtitle">
                            Monitoramento IA Contínuo Ativado
                        </div>
                        
                        <div class="chat-logs" id="notification-container">
                            </div>
                    </div>
                </aside>
            </div>

          <footer class="meet-footer">
    <div class="meeting-details">14:58 | lab-b-cam-01</div>

    <div class="controls">
        <div class="layout-menu-container">
            <button class="btn-meet" onclick="toggleLayoutMenu()">
                <i data-lucide="layout" size="20"></i>
            </button>
            <div class="layout-dropdown" id="layoutDropdown">
                <div class="layout-option selected" id="opt-default" onclick="setLayout('default')">
                    <i data-lucide="sidebar" size="16"></i> Modo Padrão
                </div>
                <div class="layout-option" id="opt-expanded" onclick="setLayout('expanded')">
                    <i data-lucide="maximize" size="16"></i> Câmera Expandida
                </div>
            </div>
        </div>

        <button class="btn-meet btn-end" id="btn-camera" onclick="toggleCamera()">
            <i data-lucide="video-off" size="20"></i>
        </button>
    </div>

    <div class="right-tools">
        <i data-lucide="settings" size="20"></i>
        <i data-lucide="activity" size="20"></i>
    </div>
</footer>
        </div>

    </main>

    <script src="../js/ocorrencias.js" defer></script>
    <script>
        // Inicializa os ícones do Lucide
        lucide.createIcons();

       // Controle do Dropdown de Layout
function toggleLayoutMenu() {
    const dropdown = document.getElementById('layoutDropdown');
    dropdown.classList.toggle('active');
}

// Função para alterar o Layout (Padrão vs Expandido)
function setLayout(mode) {
    const wrapper = document.getElementById('meetWrapper');
    const optDefault = document.getElementById('opt-default');
    const optExpanded = document.getElementById('opt-expanded');

    if (mode === 'expanded') {
        wrapper.classList.add('layout-expanded');
        optExpanded.classList.add('selected');
        optDefault.classList.remove('selected');
    } else {
        wrapper.classList.remove('layout-expanded');
        optDefault.classList.add('selected');
        optExpanded.classList.remove('selected');
    }
    
    // Fecha o menu após clicar
    document.getElementById('layoutDropdown').classList.remove('active');
}

// Fecha o dropdown se clicar fora dele
window.addEventListener('click', function(e) {
    const container = document.querySelector('.layout-menu-container');
    if (container && !container.contains(e.target)) {
        document.getElementById('layoutDropdown').classList.remove('active');
    }
});
        // <------------------------------------------>
        // LÓGICA DE NOTIFICAÇÕES (BANCO DE DADOS)
        // <------------------------------------------>
        let ultimoIdNotificacao = 0;

        function mostrarNotificacao(aluno, epi_nome, hora_banco) {
            const container = document.getElementById('notification-container');
            const card = document.createElement('div');
            card.className = 'infraction-card';

            // Tratamento da hora (caso o banco não envie, pega a hora atual do PC)
            let horaExibicao = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            if (hora_banco) {
                // Tenta formatar a data_hora vinda do banco (ex: "2023-10-25 14:30:00")
                const dataObj = new Date(hora_banco);
                if (!isNaN(dataObj.getTime())) {
                    horaExibicao = dataObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
            }

            // Constrói o HTML do card
            card.innerHTML = `
                <div class="infraction-icon">
                    <i data-lucide="alert-circle" width="20" height="20"></i>
                </div>
                <div class="infraction-content">
                    <div class="infraction-title">Alerta de EPI</div>
                    <div class="infraction-message"><b>${aluno}</b> • ${epi_nome}</div>
                    <span class="infraction-time">${horaExibicao}</span>
                </div>
            `;

            // Usa prepend para colocar a notificação mais recente no TOPO da lista
            container.prepend(card);
            
            // Renderiza o ícone do lucide no card recém-criado
            lucide.createIcons({ root: card });
        }

       function verificarNovasOcorrencias() {
            // Adicionado as crases (`) em volta da URL
            fetch(`../php/check_notificacoes.php?last_id=${ultimoIdNotificacao}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
            .then(res => res.json())
            .then(data => {
                console.log("RETORNO COMPLETO:", data);

                if (data.status === 'init') {
                    ultimoIdNotificacao = data.last_id;
                    return;
                }

                if (data.status === 'success' && data.dados.length > 0) {
                    data.dados.forEach(ocorrencia => {
                        mostrarNotificacao(
                            ocorrencia.aluno,          
                            ocorrencia.epi_nome,       
                            ocorrencia.data_hora       
                        );
                        // Atualiza o último ID processado
                        ultimoIdNotificacao = ocorrencia.id;
                    });
                }
            })
            .catch(err => console.error("Erro na verificação de ocorrências:", err));
        }

        // Executa a cada 5 segundos
        setInterval(verificarNovasOcorrencias, 5000);



        // Função para Ligar/Desligar a Câmera
        function toggleCamera() {
            const cameraFeed = document.getElementById('camera-feed');
            const btnCamera = document.getElementById('btn-camera');
            const icone = btnCamera.querySelector('i');
            const textOff = document.getElementById('camera-off-text');

            if (cameraFeed.src.includes('video_feed')) {
                // DESLIGAR (Fica Verde com ícone de Câmera normal)
                cameraFeed.src = ""; // Remove o link, cortando a conexão com o Python
                cameraFeed.style.opacity = "0"; // Esconde a imagem
                textOff.style.display = "flex"; // Mostra o texto de câmera desligada
                
                btnCamera.style.background = "#34c759"; // Botão fica verde
                icone.setAttribute('data-lucide', 'video'); // Ícone de ligar a câmera
            } else {
                // LIGAR (Fica Vermelho com ícone de Câmera cortada)
                // O "?t=" evita que o navegador pegue a imagem em cache
                cameraFeed.src = "http://localhost:5000/video_feed?t=" + new Date().getTime();
                cameraFeed.style.opacity = "1"; // Mostra a imagem
                textOff.style.display = "none"; // Esconde o texto
                
                btnCamera.style.background = "#ff3b30"; // Botão volta a ficar vermelho
                icone.setAttribute('data-lucide', 'video-off'); // Ícone de desligar a câmera
            }
            
            // Atualiza os ícones na tela
            lucide.createIcons({ root: btnCamera });
            lucide.createIcons({ root: textOff });
        }
    </script>

</body>
</html>