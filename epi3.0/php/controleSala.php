<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php'; // Proteção de sessão

$cursoId = (isset($_SESSION['usuario_id_curso']) && (int)$_SESSION['usuario_id_curso'] > 0) ? (int)$_SESSION['usuario_id_curso'] : 1;
$nomeUsuario = $_SESSION['nome'] ?? 'Instrutor';
$cargoUsuario = $_SESSION['cargo'] ?? 'Supervisor';
$iniciais = strtoupper(substr($nomeUsuario, 0, 2));

// VERIFICA SE É SUPER ADMIN
$isSuperAdmin = (isset($_SESSION['cargo']) && $_SESSION['cargo'] === 'super_admin');

$listaCursos = [];
if ($isSuperAdmin) {
    $resCursosList = mysqli_query($conn, "SELECT id, nome FROM cursos ORDER BY nome ASC");
    while ($c = mysqli_fetch_assoc($resCursosList)) $listaCursos[] = $c;
}

// Busca informações do curso atual para o título
$sqlCurso = "SELECT nome FROM cursos WHERE id = ?";
$stmtCurso = mysqli_prepare($conn, $sqlCurso);
mysqli_stmt_bind_param($stmtCurso, "i", $cursoId);
mysqli_stmt_execute($stmtCurso);
$resCurso = mysqli_stmt_get_result($stmtCurso);
$cursoData = mysqli_fetch_assoc($resCurso);
$nomeCurso = $cursoData['nome'] ?? 'Geral';
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
        <script src="../js/transitions.js"></script>


  
</head>

<body>
   <?php include __DIR__ . '/../components/sidebar.php'; ?>


    <main class="main-content">
        <header class="header">
            <div class="page-title">
                <h1>Painel Geral</h1>
                <p>Curso: <?php echo htmlspecialchars($nomeCurso); ?> • Monitoramento em Tempo Real</p>
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
                        <option value="Safe"> Regular</option>
                    </select>

                    <?php if ($isSuperAdmin): ?>
                        <select class="filter-select" id="courseFilter" onchange="fetchStudents()">
                            <option value="todos">Todos os Cursos</option>
                            <?php foreach ($listaCursos as $c): ?>
                                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
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
    <script src="../js/notifications.js" defer></script>


    <script></script>
    <script src="../js/notifications.js" defer></script>
</body>

</html>