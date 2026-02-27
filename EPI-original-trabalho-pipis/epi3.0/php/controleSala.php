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
        <link rel="stylesheet" href="../css/nav.css">
        <link rel="stylesheet" href="../css/dark.css">
        <link rel="stylesheet" href="../css/transitions.css">
        <script src="../js/Dark.js"></script>


  
</head>

<body>
   <?php include __DIR__ . '/../components/sidebar.php'; ?>


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
                        <input type="text" class="search-input" id="searchInput" placeholder="Buscar aluno...">
                    </div>
                    <select class="filter-select" id="statusFilter" name="statusFilter">
                        <option value="all">Todos os status</option>
                        <option value="Risk"> Risco Ativo</option>
                        <option value="Recurrent"> Reincidente</option>
                        <option value="History"> Histórico</option>
                        <option value="Safe"> Regular</option>
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
                <h4 style="">Checklist de EPIs:</h4>
                <div id="modalEpiList" class="epi-list"></div>
            </div>

            <div id="modalFooterActions"></div>
        </div>
    </div>

    <script src="../js/controleSala.js"></script>

    <script>
     
    </script>
</body>

</html>