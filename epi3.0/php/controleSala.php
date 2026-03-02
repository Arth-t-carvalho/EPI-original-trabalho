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
    // Busca cursos com conformidade calculada (baseada em riscos ativos hoje)
    $sqlCursos = "SELECT 
        c.id, 
        c.nome,
        (SELECT COUNT(*) FROM alunos a2 WHERE a2.curso_id = c.id) as total_alunos,
        (SELECT COUNT(DISTINCT o.aluno_id) 
         FROM ocorrencias o 
         JOIN alunos a3 ON o.aluno_id = a3.id 
         WHERE a3.curso_id = c.id 
         AND DATE(o.data_hora) = CURDATE() 
         AND o.tipo = 0
         AND NOT EXISTS (
             SELECT 1 FROM ocorrencias o2 
             WHERE o2.aluno_id = o.aluno_id 
             AND o2.epi_id = o.epi_id 
             AND o2.data_hora > o.data_hora 
             AND o2.tipo = 1
         )
        ) as alunos_com_risco
    FROM cursos c
    ORDER BY c.nome ASC";
    
    $resCursosList = mysqli_query($conn, $sqlCursos);
    while ($c = mysqli_fetch_assoc($resCursosList)) {
        $totalAlunos = (int)$c['total_alunos'];
        $comRisco = (int)$c['alunos_com_risco'];
        $conformidade = ($totalAlunos > 0) ? (($totalAlunos - $comRisco) / $totalAlunos) * 100 : 100;
        $c['conformidade'] = round($conformidade, 1);
        $listaCursos[] = $c;
    }
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
                        <button class="btn-api-action" onclick="openCourseModal()" style="display: flex; align-items: center; gap: 8px; background: var(--primary); color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 500;">
                            <i data-lucide="book-open" style="width: 18px;"></i>
                            Trocar Curso
                        </button>
                        <input type="hidden" id="courseFilter" value="">
                    <?php endif; ?>
                </div>

                <div class="student-list" id="studentList">
                    <p style="text-align:center; padding: 40px; color: #64748b; background: white; border-radius: 12px; border: 2px dashed #e2e8f0;">
                        <i data-lucide="info" style="width: 24px; margin-bottom: 8px;"></i><br>
                        Selecione um curso no botão acima para visualizar os alunos.
                    </p>
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

    <!-- Novo Modal de Seleção de Cursos -->
    <div class="modal-overlay" id="courseSelectionModal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <div>
                    <h2 style="margin:0; font-size:18px;">Selecionar Curso</h2>
                    <small style="color:#666;">Escolha um curso para monitorar</small>
                </div>
                <button class="close-btn" onclick="closeCourseModal()">✕</button>
            </div>

            <div style="padding: 20px; max-height: 500px; overflow-y: auto;">
                <table class="data-table" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 14px; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Curso</th>
                            <th style="padding: 14px; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; text-align: center;">Alertas</th>
                            <th style="padding: 14px; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; text-align: right;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaCursos as $c): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 14px;">
                                    <div style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($c['nome']); ?></div>
                                    <div style="font-size: 0.7rem; color: #94a3b8; margin-bottom: 2px;"><?= $c['conformidade']; ?>% de Conformidade</div>
                                    <div style="height: 4px; width: 100%; background: #e2e8f0; border-radius: 10px; overflow: hidden;">
                                        <?php 
                                        $barColor = '#10b981'; // Verde default
                                        if ($c['conformidade'] < 50) $barColor = '#ef4444';
                                        elseif ($c['conformidade'] < 75) $barColor = '#f97316';
                                        elseif ($c['conformidade'] < 95) $barColor = '#eab308';
                                        ?>
                                        <div style="height: 100%; width: <?= $c['conformidade']; ?>%; background: <?= $barColor; ?>; transition: width 0.5s ease;"></div>
                                    </div>
                                </td>
                                <td style="padding: 14px; text-align: center;">
                                    <div style="display: flex; gap: 4px; justify-content: center;">
                                        <?php 
                                        $conf = $c['conformidade'];
                                        // Amarelo (< 95%)
                                        if ($conf < 95) echo '<i data-lucide="alert-triangle" style="width:16px; color: #eab308;" title="Aviso"></i>';
                                        // Laranja (< 75%)
                                        if ($conf < 75) echo '<i data-lucide="alert-circle" style="width:16px; color: #f97316;" title="Alerta"></i>';
                                        // Vermelho (< 50%)
                                        if ($conf < 50) echo '<i data-lucide="alert-octagon" style="width:16px; color: #ef4444;" title="Crítico"></i>';
                                        
                                        if ($conf >= 95) echo '<i data-lucide="shield-check" style="width:16px; color: #10b981;" title="Seguro"></i>';
                                        ?>
                                    </div>
                                </td>
                                <td style="padding: 14px; text-align: right;">
                                    <button onclick="selectCourse('<?= $c['id']; ?>', '<?= addslashes($c['nome']); ?>')" class="btn-verify" style="padding: 8px 16px; font-size: 0.75rem; border-radius: 6px;">Selecionar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../js/controleSala.js"></script>
    <script src="../js/notifications.js" defer></script>
</body>

</html>