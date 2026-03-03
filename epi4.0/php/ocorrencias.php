<?php
// Correção solicitada: auth.php (caminho relativo assumindo que está na pasta /pages/)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// --- CÓDIGO NOVO: Busca de alunos (Filtrado por Curso) ---
$cursoId = $_SESSION['usuario_id_curso'] ?? 1;
$sql_alunos = "SELECT id, nome, curso_id, turno, foto_referencia 
               FROM alunos 
               WHERE curso_id = ? 
               ORDER BY nome ASC";
$stmt_alunos = mysqli_prepare($conn, $sql_alunos);
mysqli_stmt_bind_param($stmt_alunos, "i", $cursoId);
mysqli_stmt_execute($stmt_alunos);
$result_alunos = mysqli_stmt_get_result($stmt_alunos);
// ------------------------------------


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
    <title>EPI Guard | Nova Ocorrência</title>
    <link rel="stylesheet" href="../css/Ocorrencia.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/dark.css">
    <link rel="stylesheet" href="../css/transitions.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../js/Dark.js"></script>
    <script src="../js/transitions.js"></script>


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
                <button class="btn-export" onclick="exportData()">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z" />
                    </svg>
                    Exportar
                </button>

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

        <form class="form-container" id="incidentForm">
            <input type="hidden" id="ocorrenciaId" name="ocorrencia_id">


            <div class="form-section-title">
                 Dados da Infração (Automático)
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Aluno Identificado</label>
                    <select class="form-select" id="studentNameInput" name="aluno_id" required >
                        <option value="" disabled selected>Selecione um aluno...</option>
                        <?php
// Verifica se retornou algum aluno
if ($result_alunos && mysqli_num_rows($result_alunos) > 0) {
    // Cria uma opção (option) para cada aluno encontrado
    while ($aluno = mysqli_fetch_assoc($result_alunos)) {
        echo '<option value="' . htmlspecialchars($aluno['id']) . '" ';
        echo 'data-curso="' . htmlspecialchars($aluno['curso_id'] ?? '') . '" ';
        echo 'data-turno="' . htmlspecialchars($aluno['turno'] ?? '') . '" ';
        echo 'data-foto="' . htmlspecialchars($aluno['foto_referencia'] ?? '') . '">';
        echo htmlspecialchars($aluno['nome']);
        echo '</option>';
    }
}
else {
    echo '<option value="" disabled>Nenhum aluno encontrado</option>';
}
?>

                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Motivo Principal</label>
                    <input type="text" class="form-input" id="reasonInput" value="..." readonly
                        style="color: var(--primary); font-weight: 700; background: #FEF2F2; border-color: #FCA5A5;">
                </div>

                <div class="form-group">
                    <label class="form-label">Data e Hora</label>
                    <input type="text" class="form-input" id="dateTimeInput" readonly>
                </div>
            </div>

            <div class="form-section-title">
                 Ação Tomada
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Tipo de Registro / Advertência</label>
                    <select class="form-select" id="actionType" name="tipo">
                        <option value="obs" selected> Adicionar Observação (Padrão)</option>
                        <option value="adv_verbal"> Advertência Verbal</option>
                        <option value="adv_escrita"> Advertência Escrita</option>
                        <option value="suspensao"> Suspensão</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Observações Adicionais</label>
                    <textarea class="form-textarea" name="observacao" placeholder="Descreva detalhes sobre a ocorrência..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Evidências</label>

                    <div class="photos-container" id="photoGallery">
                        <!-- Imagens dinâmicas ou uploads aparecerão aqui -->
                        <input type="file" id="fileInput" hidden multiple accept="image/*">

                        <div class="btn-add-photo" onclick="document.getElementById('fileInput').click()">
                            <span>+</span>
                            <p>Adicionar</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="window.history.back()">Cancelar</button>
                <button type="submit" class="btn btn-submit">
                    Confirmar Ocorrência
                </button>
            </div>
        </form>

    </main>
    <script src="../js/ocorrencias.js" defer></script>
    <script src="../js/notifications.js" defer></script>

    <script>
        lucide.createIcons();
    </script>

</body>

</html>