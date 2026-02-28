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
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/Dark.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../js/Dark.js"></script>
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
    <script src="../js/monitoramento.js"></script>
    <script src="../js/global.js"></script>

</body>
</html>