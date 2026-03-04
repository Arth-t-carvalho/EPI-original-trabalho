<?php



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
    <title>Gestão de Professores | EPI Guard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/dark.css">
    <link rel="stylesheet" href="css/transitions.css">
    <link rel="stylesheet" href="css/gestao.css">
    <style>
        .course-select-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .course-select-box:hover {
            border-color: var(--primary);
            background: #fff;
        }
        .course-list-scroll {
            max-height: 300px;
            overflow-y: auto;
            border-top: 1px solid #eee;
        }
        .course-item-option {
            padding: 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }
        .course-item-option:hover {
            background: #fef2f2;
            color: var(--primary);
            font-weight: 600;
        }
    </style>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="js/Dark.js"></script>
    <script src="js/transitions.js"></script>
</head>
<body>
    <?php include __DIR__ . '/Components/sidebar.view.php'; ?>

    <main class="main-content">
        <header class="header">
            <div class="page-title">
                <h1>Gestão de Professores</h1>
                <p>Gerenciamento de Contas e Permissões</p>
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
                    <a href="../config/index.php?route=logout" class="btn-close-card" style="flex:1; background: #fee2e2; color: #dc2626; text-decoration: none; text-align: center; line-height: 1.5;">Sair</a>
                </div>
            </div>
        </header>

        <section class="gestao-container">
            <div class="gestao-header">
                <div class="search-wrapper">
                    <i data-lucide="search"></i>
                    <input type="text" id="searchProf" class="search-input" placeholder="Buscar professor por nome...">
                </div>
                <button class="btn-gestao btn-add" onclick="openModal('modalProf')">
                    <i data-lucide="plus"></i> Novo Professor
                </button>
            </div>

            <div class="card-gestao">
                <table class="gestao-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>Cargo</th>
                            <th>Curso / Lab</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableProfs">
                        <!-- Conteúdo via JS -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Modal Novo/Editar Professor -->
    <div class="modal-gestao" id="modalProf">
        <div class="modal-content-gestao">
            <div class="modal-header-gestao">
                <h2 id="modalLabel">Novo Professor</h2>
                <button class="btn-close-modal" onclick="closeModal('modalProf')">&times;</button>
            </div>
            <form id="formProf">
                <input type="hidden" name="id" id="profId">
                <div class="form-group">
                    <label for="profUser">Autorizar Gmail ou CPF</label>
                    <input type="text" id="profUser" name="usuario" class="form-input" placeholder="exemplo@gmail.com ou 12345678901" required>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">* O professor usará este dado para se cadastrar futuramente.</small>
                </div>
                <div class="form-group">
                    <label>Cargo de Permissão</label>
                    <select name="cargo" id="profCargo" class="form-select" required>
                        <option value="instrutor">Instrutor</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Vínculo de Curso</label>
                    <input type="hidden" name="id_curso" id="profCurso">
                    <div id="courseSelectTrigger" class="course-select-box" onclick="openModal('modalSelectCurso')">
                        <span id="selectedCourseName">Selecionar Curso...</span>
                        <i data-lucide="chevron-down"></i>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-gestao" onclick="closeModal('modalProf')" style="flex:1; background: #eee; color: #333;">Cancelar</button>
                    <button type="submit" class="btn-gestao btn-add" style="flex:1;">Autorizar Acesso</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Especial de Seleção de Curso -->
    <div class="modal-gestao" id="modalSelectCurso" style="z-index: 11000;">
        <div class="modal-content-gestao" style="width: 400px;">
            <div class="modal-header-gestao">
                <h2>Selecionar Curso</h2>
                <button class="btn-close-modal" onclick="closeModal('modalSelectCurso')">&times;</button>
            </div>
            <div class="search-wrapper" style="margin-bottom: 15px; width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 5px 10px;">
                <i data-lucide="search" style="width: 16px; color: #999;"></i>
                <input type="text" id="searchCourseModal" class="search-input" placeholder="Filtrar cursos..." oninput="filterCoursesModal()" style="border: none; outline: none; padding: 5px; width: 100%;">
            </div>
            <div class="course-list-scroll" id="courseListModal">
                <?php foreach($listaCursos as $curso): ?>
                    <div class="course-item-option" data-nome="<?= strtolower(htmlspecialchars($curso['nome'])); ?>" onclick="selectCourse('<?= $curso['id']; ?>', '<?= htmlspecialchars($curso['nome']); ?>')">
                        <?= htmlspecialchars($curso['nome']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
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

    <script src="js/global.js"></script>
    <script src="js/notifications.js" defer></script>
    <script src="js/gestao_professores.js" defer></script>
    <?php include __DIR__ . '/Components/ai_assistant.view.php'; ?>
</body>
</html>





