<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Bloqueio de Acesso para não-super_admin
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit;
}

// Dados do Usuário (para o header)
$sqlUser = "SELECT nome, cargo FROM usuarios WHERE id = ? LIMIT 1";
$stmtUser = mysqli_prepare($conn, $sqlUser);
$userRef = $_SESSION['usuario_id'];
mysqli_stmt_bind_param($stmtUser, "i", $userRef);
mysqli_stmt_execute($stmtUser);
$resUser = mysqli_stmt_get_result($stmtUser);
$userData = mysqli_fetch_assoc($resUser);

$nomeUsuario = $userData['nome'] ?? 'Usuário';
$cargoUsuario = ucfirst($userData['cargo'] ?? 'Visitante');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Cursos | EPI Guard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/dark.css">
    <link rel="stylesheet" href="../css/transitions.css">
    <link rel="stylesheet" href="../css/gestao.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../js/Dark.js"></script>
    <script src="../js/transitions.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <main class="main-content">
        <header class="header">
            <div class="page-title">
                <h1>Gestão de Cursos</h1>
                <p>Administração de Cursos e Turmas do Sistema</p>
            </div>
            
            <div class="header-actions">
                <a href="configuracoes.php" class="btn-header-action" title="Configurações">
                    <i data-lucide="settings"></i>
                </a>
                
                <div class="user-profile-trigger" id="profileTrigger" onclick="toggleInstructorCard()">
                    <div class="user-info-mini">
                        <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($cargoUsuario); ?></span>
                    </div>
                    <div class="user-avatar"><?php echo strtoupper(substr($nomeUsuario, 0, 2)); ?></div>
                </div>
            </div>

            <!-- Card de Perfil -->
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

        <section class="gestao-container">
            <div class="gestao-header">
                <div class="search-wrapper">
                    <i data-lucide="search"></i><input type="text" id="searchCurso" class="search-input" placeholder="Buscar curso por nome...">
                </div>
                <button class="btn-gestao btn-add" onclick="openModal('modalCurso')">
                    <i data-lucide="plus"></i> Novo Curso
                </button>
            </div>

            <div class="card-gestao">
                <table class="gestao-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome do Curso / Laboratório</th>
                            <th>Capacidade (Alunos)</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableCursos">
                        <!-- Conteúdo via JS -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Modal Novo/Editar Curso -->
    <div class="modal-gestao" id="modalCurso">
        <div class="modal-content-gestao">
            <div class="modal-header-gestao">
                <h2 id="modalLabel">Novo Curso</h2>
                <button class="btn-close-modal" onclick="closeModal('modalCurso')">&times;</button>
            </div>
            <form id="formCurso">
                <input type="hidden" name="id" id="cursoId">
                <div class="form-group">
                    <label>Nome do Curso</label>
                    <input type="text" name="nome" id="cursoNome" class="form-input" required placeholder="Ex: Téc. Mecatrônica">
                </div>
                <div class="form-group">
                    <label>Quantidade de Alunos</label>
                    <input type="number" name="vagas" id="cursoVagas" class="form-input" required placeholder="Ex: 30" min="1">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-gestao" onclick="closeModal('modalCurso')" style="flex:1; background: #eee; color: #333;">Cancelar</button>
                    <button type="submit" class="btn-gestao btn-add" style="flex:1;">Salvar Curso</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Confirmação Customizado -->
    <div class="modal-confirm" id="modalConfirm" style="z-index: 9999;">
        <div class="confirm-content">
            <div class="confirm-icon">
                <i data-lucide="alert-triangle"></i>
            </div>
            <h2 class="confirm-title" id="confirmTitle">Confirmar Exclusão</h2>
            <p class="confirm-text" id="confirmText">Tem certeza que deseja realizar esta ação?</p>
            <div class="confirm-actions">
                <button class="btn-confirm btn-cancel-confirm" onclick="closeConfirmModal()">Cancelar</button>
                <button class="btn-confirm btn-danger-confirm" onclick="handleConfirmAction()">Confirmar e Excluir</button>
            </div>
        </div>
    </div>

    <div id="notification-container"></div>

    <script src="../js/global.js"></script>
    <script src="../js/notifications.js" defer></script>
    <script src="../js/gestao_cursos.js" defer></script>
</body>
</html>
