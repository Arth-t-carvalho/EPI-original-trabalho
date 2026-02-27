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
    <link rel="stylesheet" href="../css/monitoramento.css">
    <link rel="stylesheet" href="../css/transitions.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

    <?php include __DIR__ . '/../components/sidebar.php'; ?>


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
    <script src="../js/global.js" defer></script>
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
            let horaExibicao = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            if (hora_banco) {
                // Tenta formatar a data_hora vinda do banco (ex: "2023-10-25 14:30:00")
                const dataObj = new Date(hora_banco);
                if (!isNaN(dataObj.getTime())) {
                    horaExibicao = dataObj.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
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
            lucide.createIcons({
                root: card
            });
        }

        function verificarNovasOcorrencias() {
            // Adicionado as crases (`) em volta da URL
            fetch(`../php/check_notificacoes.php?last_id=${ultimoIdNotificacao}`, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
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
            lucide.createIcons({
                root: btnCamera
            });
            lucide.createIcons({
                root: textOff
            });
        }
    </script>

</body>

</html>