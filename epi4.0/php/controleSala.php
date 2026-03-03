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
    
    <script>
        window.userRole = "<?= $_SESSION['cargo']; ?>";
        window.isSuperAdmin = <?= $isSuperAdmin ? 'true' : 'false'; ?>;
    </script>


  
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
                    <?php endif; ?>
                    <input type="hidden" id="courseFilter" value="<?= $isSuperAdmin ? '' : $cursoId; ?>">
                </div>

                <div class="student-list" id="studentList" style="display: flex; align-items: center; justify-content: center; min-height: 400px; width: 100%;">
                    <div style="text-align:center; padding: 60px; color: #64748b; background: white; border-radius: 20px; border: 2px dashed #cbd5e1; max-width: 500px; width: 90%; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);">
                        <?php if ($isSuperAdmin): ?>
                            <div style="background: #f1f5f9; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i data-lucide="book-open" style="width: 32px; height: 32px; color: var(--primary);"></i>
                            </div>
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Aguardando Seleção</h2>
                            <p style="font-size: 0.95rem; line-height: 1.5; color: #64748b;">
                                Selecione um curso no botão acima para visualizar os alunos e o monitoramento em tempo real.
                            </p>
                        <?php else: ?>
                            <div style="text-align:center;">
                                🔄 Carregando alunos do seu curso...
                            </div>
                        <?php endif; ?>
                    </div>
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
        <div class="modal-content" style="max-width: 700px; border-radius: 24px; overflow: hidden; padding: 0;">
            <div class="modal-header" style="background: #ffffff; padding: 24px 30px; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h2 style="margin:0; font-size:22px; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">Selecionar Curso</h2>
                    <p style="color:#64748b; margin: 4px 0 0 0; font-size: 14px;">Escolha a turma que deseja monitorar agora</p>
                </div>
                <button class="close-btn" onclick="closeCourseModal()" style="background: #f1f5f9; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">✕</button>
            </div>

            <div style="padding: 20px 30px 10px;">
                <div class="search-wrapper" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 15px; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="search" style="width: 18px; color: #94a3b8;"></i>
                    <input type="text" id="searchCourseModal" placeholder="Buscar curso ou laboratório..." oninput="filterCoursesModal()" style="background: transparent; border: none; outline: none; width: 100%; font-size: 14px; font-weight: 500;">
                </div>
            </div>

            <div style="padding: 10px 30px 30px; max-height: 450px; overflow-y: auto;">
                <table class="data-table" style="width:100%; border-collapse: separate; border-spacing: 0 10px;">
                    <thead>
                        <tr style="text-align: left;">
                            <th style="padding: 10px 15px; font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Curso / Turma</th>
                            <th style="padding: 10px 15px; font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Saúde da Turma</th>
                            <th style="padding: 10px 15px; font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="courseTableBody">
                        <?php foreach ($listaCursos as $c): ?>
                            <tr class="course-row-item" data-nome="<?= strtolower(htmlspecialchars($c['nome'])); ?>" style="background: #ffffff; border-radius: 12px; box-shadow: 0 0 0 1px #e2e8f0; transition: all 0.2s;">
                                <td style="padding: 18px 15px; border-radius: 12px 0 0 12px;">
                                    <div style="font-weight: 700; color: #1e293b; font-size: 15px;"><?= htmlspecialchars($c['nome']); ?></div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                                        <div style="height: 6px; width: 80px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                                            <?php 
                                            $barColor = '#10b981'; 
                                            if ($c['conformidade'] < 50) $barColor = '#ef4444';
                                            elseif ($c['conformidade'] < 75) $barColor = '#f97316';
                                            elseif ($c['conformidade'] < 95) $barColor = '#eab308';
                                            ?>
                                            <div style="height: 100%; width: <?= $c['conformidade']; ?>%; background: <?= $barColor; ?>;"></div>
                                        </div>
                                        <span style="font-size: 12px; font-weight: 600; color: #64748b;"><?= $c['conformidade']; ?>%</span>
                                    </div>
                                </td>
                                <td style="padding: 18px 15px; text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <?php 
                                        $conf = $c['conformidade'];
                                        if ($conf < 95) echo '<div title="Alerta Amarelo" style="width:28px; height:28px; background:#fefce8; border-radius:8px; display:flex; align-items:center; justify-content:center;"><i data-lucide="alert-triangle" style="width:16px; color: #ca8a04;"></i></div>';
                                        if ($conf < 75) echo '<div title="Alerta Laranja" style="width:28px; height:28px; background:#fff7ed; border-radius:8px; display:flex; align-items:center; justify-content:center;"><i data-lucide="alert-circle" style="width:16px; color: #ea580c;"></i></div>';
                                        if ($conf < 50) echo '<div title="Crítico" style="width:28px; height:28px; background:#fef2f2; border-radius:8px; display:flex; align-items:center; justify-content:center;"><i data-lucide="alert-octagon" style="width:16px; color: #dc2626;"></i></div>';
                                        if ($conf >= 95) echo '<div title="Excelente" style="width:28px; height:28px; background:#f0fdf4; border-radius:8px; display:flex; align-items:center; justify-content:center;"><i data-lucide="shield-check" style="width:16px; color: #15803d;"></i></div>';
                                        ?>
                                    </div>
                                </td>
                                <td style="padding: 18px 15px; text-align: right; border-radius: 0 12px 12px 0;">
                                    <button onclick="selectCourse('<?= $c['id']; ?>', '<?= addslashes($c['nome']); ?>')" style="padding: 10px 20px; font-size: 13px; font-weight: 700; background: #f1f5f9; color: #475569; border: none; border-radius: 10px; cursor: pointer; transition: all 0.2s;">Visualizar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .course-row-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(0,0,0,0.1) !important;
            border-color: var(--primary) !important;
        }
        .course-row-item:hover button {
            background: var(--primary) !important;
            color: white !important;
        }
    </style>

    <script src="../js/controleSala.js"></script>
    <script src="../js/notifications.js" defer></script>
</body>

</html>