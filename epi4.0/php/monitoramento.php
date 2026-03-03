<?php
// Mantendo a estrutura de autenticação e banco de dados
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// DADOS DO USUÁRIO
$sqlUser = "SELECT nome, cargo FROM usuarios WHERE id = ? LIMIT 1";
$stmtUser = mysqli_prepare($conn, $sqlUser);
$userRef = $_SESSION['usuario_id'];
mysqli_stmt_bind_param($stmtUser, "i", $userRef);
mysqli_stmt_execute($stmtUser);
$resUser = mysqli_stmt_get_result($stmtUser);
$userData = mysqli_fetch_assoc($resUser);

$nomeUsuario = $userData['nome'] ?? ($_SESSION['nome'] ?? 'Usuário');
$cargoUsuario = ucfirst($userData['cargo'] ?? ($_SESSION['cargo'] ?? 'Visitante'));
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Monitoramento</title>
    <link rel="stylesheet" href="../css/Ocorrencia.css">
    <link rel="stylesheet" href="../css/monitoramento.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/dark.css">
    <link rel="stylesheet" href="../css/transitions.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../js/Dark.js"></script>
    <script src="../js/transitions.js"></script>
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
                <a href="configuracoes.php" class="btn-header-action" title="Configurações">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </a>

                <a href="infracoes.php" class="btn-header-action" title="Notificações">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="notif-badge" id="notifBadge">0</span>
                </a>

                <div class="user-profile-trigger" id="profileTrigger" onclick="toggleInstructorCard()">
                    <div class="user-info-mini">
                        <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($cargoUsuario); ?></span>
                    </div>
                    <div class="user-avatar"><?php echo strtoupper(substr($nomeUsuario, 0, 2)); ?></div>
                </div>
            </div>

            <div class="instructor-card" id="instructorCard">
                <div style="margin-bottom: 20px;">
                    <h3><?php echo htmlspecialchars($nomeUsuario); ?></h3>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cargo</span>
                    <span class="detail-value"><?php echo htmlspecialchars($cargoUsuario); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value" style="color:var(--success)">Online</span>
                </div>
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px; display: flex; gap: 10px;">
                    <button class="btn-close-card" onclick="toggleInstructorCard()" style="flex:1; background: #f3f4f6; color: #374151;">Fechar</button>
                    <a href="../config/logout.php" class="btn-close-card" style="flex:1; background: #fee2e2; color: #dc2626; text-decoration: none; text-align: center; line-height: 1.5;">Sair</a>
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
                        <div id="camera-off-text" style="display: flex; position: absolute; flex-direction: column; align-items: center; color: white; z-index: 1;">
                            <i data-lucide="video-off" size="48" style="color: #ff3b30; margin-bottom: 10px;"></i>
                            <h2 style="margin: 0; font-size: 24px; font-weight: 500;">Câmera Desligada</h2>
                            <p style="color: #86868b; font-size: 14px; margin-top: 5px;">Conexão de vídeo interrompida.</p>
                        </div>
                        
                        <img id="camera-feed" src="" alt="Câmera do Python Ao Vivo" style="position: relative; z-index: 2; transition: opacity 0.3s ease; opacity: 0;">
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

        <button class="btn-meet btn-end" id="btn-camera" onclick="toggleCamera()" style="background: #34c759;">
            <i data-lucide="video" size="20"></i>
        </button>
    </div>

    <div class="right-tools">
        <i data-lucide="settings" size="20"></i>
        <i data-lucide="activity" size="20"></i>
    </div>
</footer>
        </div>

    </main>

    <div id="notification-container"></div>

    <script src="../js/ocorrencias.js" ></script>
    <script src="../js/monitoramento.js" defer></script>
    <script src="../js/global.js" ></script>
    <script src="../js/notifications.js" defer></script>
    <script src="../js/configuracao.js"></script>
</body>
</html>