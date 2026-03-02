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

// Busca Cursos para o Modal
$sqlCursos = "SELECT id, nome FROM cursos ORDER BY nome ASC";
$resCursos = mysqli_query($conn, $sqlCursos);
$listaCursos = mysqli_fetch_all($resCursos, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Alunos | EPI Guard</title>
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
                <h1>Gestão de Alunos</h1>
                <p>Administração de Alunos e Matrículas</p>
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
                    <i data-lucide="search"></i>
                    <input type="text" id="searchAluno" class="search-input" placeholder="Buscar aluno por nome...">
                </div>
                <button class="btn-gestao btn-add" onclick="openModal('modalAluno')">
                    <i data-lucide="plus"></i> Novo Aluno
                </button>
            </div>

            <div class="card-gestao">
                <table class="gestao-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome Completo</th>
                            <th>Curso / Turma</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableAlunos">
                        <!-- Conteúdo via JS -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Modal Novo/Editar Aluno -->
    <div class="modal-gestao" id="modalAluno">
        <div class="modal-content-gestao">
            <div class="modal-header-gestao">
                <h2 id="modalLabel">Novo Aluno</h2>
                <button class="btn-close-modal" onclick="closeModal('modalAluno')">&times;</button>
            </div>
            <form id="formAluno" enctype="multipart/form-data">
                <input type="hidden" name="id" id="alunoId">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" id="alunoNome" class="form-input" required placeholder="Ex: Arthur Silva">
                </div>
                <div class="form-group">
                    <label>Curso / Turma</label>
                    <select name="curso_id" id="alunoCurso" class="form-select" required>
                        <option value="">Selecione um curso</option>
                        <?php foreach($listaCursos as $curso): ?>
                            <option value="<?= $curso['id']; ?>"><?= htmlspecialchars($curso['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Foto do Aluno (Opcional)</label>
                    <input type="file" name="foto" id="alunoFoto" class="form-input" accept="image/*">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-gestao" onclick="closeModal('modalAluno')" style="flex:1; background: #eee; color: #333;">Cancelar</button>
                    <button type="submit" class="btn-gestao btn-add" style="flex:1;">Salvar Registro</button>
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
    <script src="../js/gestao_alunos.js" defer></script>
</body>
</html>
