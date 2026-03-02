<?php
// ARQUIVO: php/dashboard.php

// Ajuste os requires conforme a localização da sua pasta config
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// CONFIGURAÇÃO DO PROFESSOR (Filtrado pelo curso do usuário logado)
$cursoId = (isset($_SESSION['usuario_id_curso']) && (int)$_SESSION['usuario_id_curso'] > 0) ? (int)$_SESSION['usuario_id_curso'] : 1;


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

// VERIFICA SE É SUPER ADMIN
$isSuperAdmin = (strtolower($userData['cargo'] ?? $_SESSION['cargo'] ?? '') === 'super_admin');

// 1. KPIs GERAIS (Dia, Semana, Mês)
if ($isSuperAdmin) {
    // Queries Globais para Super Admin
    $sqlDia = "SELECT COUNT(id) as total FROM ocorrencias WHERE data_hora >= CURDATE() AND data_hora < CURDATE() + INTERVAL 1 DAY";
    $sqlSemana = "SELECT COUNT(id) as total FROM ocorrencias WHERE YEARWEEK(data_hora, 1) = YEARWEEK(CURDATE(), 1)";
    $sqlMes = "SELECT COUNT(id) as total FROM ocorrencias WHERE MONTH(data_hora) = MONTH(CURDATE()) AND YEAR(data_hora) = YEAR(CURDATE())";

    $resDia = mysqli_query($conn, $sqlDia);
    $infraDia = mysqli_fetch_assoc($resDia)['total'] ?? 0;

    $resSemana = mysqli_query($conn, $sqlSemana);
    $infraSemana = mysqli_fetch_assoc($resSemana)['total'] ?? 0;

    $resMes = mysqli_query($conn, $sqlMes);
    $infraMes = mysqli_fetch_assoc($resMes)['total'] ?? 0;

    // Ontem, Semana Ant, Mês Ant (Global)
    $sqlOntem = "SELECT COUNT(id) as total FROM ocorrencias WHERE data_hora >= CURDATE() - INTERVAL 1 DAY AND data_hora < CURDATE()";
    $infraOntem = (int)(mysqli_fetch_assoc(mysqli_query($conn, $sqlOntem))['total'] ?? 0);

    $sqlSemAnt = "SELECT COUNT(id) as total FROM ocorrencias WHERE YEARWEEK(data_hora, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)";
    $infraSemanaAnterior = (int)(mysqli_fetch_assoc(mysqli_query($conn, $sqlSemAnt))['total'] ?? 0);

    $sqlMesAnt = "SELECT COUNT(id) as total FROM ocorrencias WHERE MONTH(data_hora) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(data_hora) = YEAR(CURDATE() - INTERVAL 1 MONTH)";
    $infraMesAnterior = (int)(mysqli_fetch_assoc(mysqli_query($conn, $sqlMesAnt))['total'] ?? 0);

    // Média Turma (Global)
    $sqlTotalAlunos = "SELECT COUNT(*) as total FROM alunos";
    $totalAlunos = (int)(mysqli_fetch_assoc(mysqli_query($conn, $sqlTotalAlunos))['total'] ?? 0);

    $sqlAlunosHoje = "SELECT COUNT(DISTINCT aluno_id) as total FROM ocorrencias WHERE data_hora >= CURDATE() AND data_hora < CURDATE() + INTERVAL 1 DAY";
    $alunosComInfracaoHoje = (int)(mysqli_fetch_assoc(mysqli_query($conn, $sqlAlunosHoje))['total'] ?? 0);

    // Ranking (Cursos)
    $sqlTop = "SELECT c.nome, COUNT(o.id) AS total 
               FROM ocorrencias o 
               JOIN alunos a ON o.aluno_id = a.id 
               JOIN cursos c ON a.curso_id = c.id 
               GROUP BY c.id ORDER BY total DESC LIMIT 5";
    $listaRanking = mysqli_fetch_all(mysqli_query($conn, $sqlTop), MYSQLI_ASSOC);
    $tituloRanking = "Cursos com mais Infrações";

    $sqlRanking = "SELECT c.nome, COUNT(o.id) AS total 
                   FROM ocorrencias o 
                   JOIN alunos a ON o.aluno_id = a.id 
                   JOIN cursos c ON a.curso_id = c.id 
                   GROUP BY c.id ORDER BY total DESC";
    $rankingModal = mysqli_fetch_all(mysqli_query($conn, $sqlRanking), MYSQLI_ASSOC);
} else {
    // Queries por Curso para Professor
    $sqlDia = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() AND o.data_hora < CURDATE() + INTERVAL 1 DAY";
    $stmtDia = mysqli_prepare($conn, $sqlDia);
    mysqli_stmt_bind_param($stmtDia, "i", $cursoId);
    mysqli_stmt_execute($stmtDia);
    $infraDia = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtDia))['total'] ?? 0;

    $sqlSemana = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE(), 1)";
    $stmtSemana = mysqli_prepare($conn, $sqlSemana);
    mysqli_stmt_bind_param($stmtSemana, "i", $cursoId);
    mysqli_stmt_execute($stmtSemana);
    $infraSemana = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSemana))['total'] ?? 0;

    $sqlMes = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND MONTH(o.data_hora) = MONTH(CURDATE()) AND YEAR(o.data_hora) = YEAR(CURDATE())";
    $stmtMes = mysqli_prepare($conn, $sqlMes);
    mysqli_stmt_bind_param($stmtMes, "i", $cursoId);
    mysqli_stmt_execute($stmtMes);
    $infraMes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMes))['total'] ?? 0;

    // Ontem, Semana Ant, Mês Ant (Curso)
    $sqlOntem = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() - INTERVAL 1 DAY AND o.data_hora < CURDATE()";
    $stmtOntem = mysqli_prepare($conn, $sqlOntem);
    mysqli_stmt_bind_param($stmtOntem, "i", $cursoId);
    mysqli_stmt_execute($stmtOntem);
    $infraOntem = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmtOntem))['total'] ?? 0);

    $sqlSemAnt = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)";
    $stmtSemAnt = mysqli_prepare($conn, $sqlSemAnt);
    mysqli_stmt_bind_param($stmtSemAnt, "i", $cursoId);
    mysqli_stmt_execute($stmtSemAnt);
    $infraSemanaAnterior = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSemAnt))['total'] ?? 0);

    $sqlMesAnt = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND MONTH(o.data_hora) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(o.data_hora) = YEAR(CURDATE() - INTERVAL 1 MONTH)";
    $stmtMesAnt = mysqli_prepare($conn, $sqlMesAnt);
    mysqli_stmt_bind_param($stmtMesAnt, "i", $cursoId);
    mysqli_stmt_execute($stmtMesAnt);
    $infraMesAnterior = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMesAnt))['total'] ?? 0);

    // Média Turma (Curso)
    $sqlTotalAlunos = "SELECT COUNT(*) as total FROM alunos WHERE curso_id = ?";
    $stmtTotal = mysqli_prepare($conn, $sqlTotalAlunos);
    mysqli_stmt_bind_param($stmtTotal, "i", $cursoId);
    mysqli_stmt_execute($stmtTotal);
    $totalAlunos = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'] ?? 0);

    $sqlAlunosHoje = "SELECT COUNT(DISTINCT o.aluno_id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() AND o.data_hora < CURDATE() + INTERVAL 1 DAY";
    $stmtAlunosHoje = mysqli_prepare($conn, $sqlAlunosHoje);
    mysqli_stmt_bind_param($stmtAlunosHoje, "i", $cursoId);
    mysqli_stmt_execute($stmtAlunosHoje);
    $alunosComInfracaoHoje = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAlunosHoje))['total'] ?? 0);

    // Ranking (Alunos)
    $sqlTop = "SELECT a.nome, COUNT(o.id) AS total 
               FROM alunos a 
               JOIN ocorrencias o ON a.id = o.aluno_id 
               WHERE a.curso_id = ? 
               GROUP BY a.id ORDER BY total DESC LIMIT 5";
    $stmtTop = mysqli_prepare($conn, $sqlTop);
    mysqli_stmt_bind_param($stmtTop, "i", $cursoId);
    mysqli_stmt_execute($stmtTop);
    $listaRanking = mysqli_fetch_all(mysqli_stmt_get_result($stmtTop), MYSQLI_ASSOC);
    $tituloRanking = "Alunos com mais Infrações";

    $sqlRanking = "SELECT a.nome, COUNT(o.id) AS total 
                   FROM alunos a 
                   JOIN ocorrencias o ON a.id = o.aluno_id 
                   WHERE a.curso_id = ? 
                   GROUP BY a.id ORDER BY total DESC";
    $stmtRankingModal = mysqli_prepare($conn, $sqlRanking);
    mysqli_stmt_bind_param($stmtRankingModal, "i", $cursoId);
    mysqli_stmt_execute($stmtRankingModal);
    $rankingModal = mysqli_fetch_all(mysqli_stmt_get_result($stmtRankingModal), MYSQLI_ASSOC);
}

// 1.5 BUSCAR TODOS OS CURSOS PARA O SELETOR (Se for Super Admin)
$cursosParaFiltro = [];
if ($isSuperAdmin) {
    $resCursosList = mysqli_query($conn, "SELECT id, nome FROM cursos ORDER BY nome ASC");
    $cursosParaFiltro = mysqli_fetch_all($resCursosList, MYSQLI_ASSOC);
}

// CÁLCULO DAS PORCENTAGENS E MÉDIA
if ($totalAlunos === 0) {
    $mediaTurma = 100;
} else {
    $mediaTurma = round((($totalAlunos - $alunosComInfracaoHoje) / $totalAlunos) * 100);
}
$mediaTurma = max(0, min(100, $mediaTurma));

$percDia = ($infraOntem > 0) ? round((($infraDia - $infraOntem) / $infraOntem) * 100, 1) : ($infraDia > 0 ? 100 : 0);
$percSemana = ($infraSemanaAnterior > 0) ? round((($infraSemana - $infraSemanaAnterior) / $infraSemanaAnterior) * 100, 1) : ($infraSemana > 0 ? 100 : 0);
$percMes = ($infraMesAnterior > 0) ? round((($infraMes - $infraMesAnterior) / $infraMesAnterior) * 100, 1) : ($infraMes > 0 ? 100 : 0);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Dashboard Unificado</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/dark.css">
    <link rel="stylesheet" href="../css/transitions.css">
    <script src="../js/Dark.js"></script>
    <script src="../js/transitions.js"></script>
    <script>
        window.totalStudents = <?php echo $totalAlunos; ?>;
    </script>
    <!-- Bibliotecas para PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
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
                <a href="configuracoes.php" class="btn-header-action" title="Configurações">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </a>

                <a href="infracoes.php" class="btn-header-action" title="Notificações">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    <span class="notif-badge" id="notifBadge">0</span>
                </a>

                <?php if ($isSuperAdmin): ?>
                    <button class="btn-header-action" onclick="openCourseModal()" title="Filtrar por Curso" id="btnFilterCourse">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z" />
                        </svg>
                        <span id="activeCourseName" style="font-size: 10px; position: absolute; bottom: -15px; left: 50%; transform: translateX(-50%); white-space: nowrap; color: var(--primary); font-weight: bold;">Todos</span>
                    </button>
                <?php endif; ?>

                <button class="btn-export" onclick="exportData()" style="margin-left: 10px;">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z" />
                    </svg>
                    Exportar
                </button>

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

        <div class="kpi-grid">
            <div class="card" id="cardInfraDia" onclick="highlightDaily('dia')" style="cursor: pointer;">
                <div class="kpi-header">Infrações Diarias</div>
                <div class="kpi-value">
                    <span id="kpiDia"><?php echo $infraDia; ?></span>
                    <span id="badgeDia" class="badge <?php echo $percDia >= 0 ? 'up' : 'down'; ?>">
                        <?php echo ($percDia >= 0 ? '↗ ' : '↘ ') . abs($percDia); ?>%
                    </span>
                </div>
            </div>
            <div class="card" onclick="highlightDaily('semana')">
                <div class="kpi-header">Infrações Semanais</div>
                <div class="kpi-value">
                    <span id="kpiSemana"><?php echo $infraSemana; ?></span>
                    <span id="badgeSemana" class="badge <?php echo $percSemana >= 0 ? 'up' : 'down'; ?>">
                        <?php echo ($percSemana >= 0 ? '↗ ' : '↘ ') . abs($percSemana); ?>%
                    </span>
                </div>
            </div>
            <div class="card" onclick="highlightDaily('mes')">
                <div class="kpi-header">Infrações Mês</div>
                <div class="kpi-value">
                    <span id="kpiMes"><?php echo $infraMes; ?></span>
                    <span id="badgeMes" class="badge <?php echo $percMes >= 0 ? 'up' : 'down'; ?>">
                        <?php echo ($percMes >= 0 ? '↗ ' : '↘ ') . abs($percMes); ?>%
                    </span>
                </div>
            </div>
            <div class="card">
                <div class="kpi-header">Conformidade</div>
                <div class="kpi-value">
                    <span id="kpiMedia"><?php echo $mediaTurma; ?>%</span>

                    <?php
                    // Lógica de Status de Conformidade
                    if ($mediaTurma < 70) {
                        echo '<span class="status-badge status-critico" title="Risco alto! Bloqueio ou intervenção imediata">🚨 CRÍTICO</span>';
                    } elseif ($mediaTurma < 85) {
                        echo '<span class="status-badge status-alto" title="Abaixo do aceitável! Requer plano de ação">🟠 ALTO RISCO</span>';
                    } elseif ($mediaTurma < 95) {
                        echo '<span class="status-badge status-moderado" title="Nível aceitável, mas requer monitoramento">🟡 MODERADO</span>';
                    } else {
                        echo '<span class="status-badge status-baixo" title="Operação segura e padrão ideal">🟢 CONTROLADO</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="card" style="height: 380px; display: flex; flex-direction: column;">
            <div class="section-header">
                <span class="section-title">Infraçoes de EPIs (Anual)</span>
            </div>
            <div style="flex: 1; position: relative;">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <div class="chart-grid">

            <div class="card" id="cardRegistroDiario">
                <div class="section-header">
                    <span class="section-title">Registro Diário</span>
                </div>

                <div class="calendar-nav" onclick="toggleCalendar()"
                    onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">

                    <button class="nav-btn" onclick="event.stopPropagation(); changeDay(-1)">❮</button>

                    <div class="date-display"
                        style="text-align: center; display: flex; flex-direction: column; align-items: center;">
                        <div id="displayDayNum"
                            style="color: #E30613; font-size: 28px; font-weight: 800; line-height: 1;">
                            02
                        </div>
                        <div id="displayMonthStr" style="color: #64748B; font-size: 13px; font-weight: 600;">
                            Setembro 2024
                        </div>

                        <div
                            style="font-size: 10px; color: #E30613; font-weight: 700; margin-top: 6px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <span style="font-size: 8px;"></span> Clique para expandir
                        </div>
                    </div>

                    <button class="nav-btn" onclick="event.stopPropagation(); changeDay(1)">❯</button>
                </div>

                <div class="occurrences-list" id="occurrenceList">
                </div>
            </div>

            <div class="card">
                <div class="section-header">
                    <span class="section-title">EPI Menos Usado</span>
                </div>
                <div style="height: 200px; position: relative;">
                    <canvas id="doughnutChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="section-header">
                    <span class="section-title"><?php echo $tituloRanking; ?></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">

                    <?php
                    if (count($listaRanking) > 0):
                        foreach ($listaRanking as $item):
                            $width = ($item['total'] > 20) ? 100 : ($item['total'] * 5);
                            $color = ($item['total'] > 10) ? '#E30613' : '#1F2937';
                            $clickAction = $isSuperAdmin ? "" : "onclick=\"irParaInfracoes('" . addslashes($item['nome']) . "')\" style=\"cursor: pointer;\" title=\"Ver todas as infrações de " . htmlspecialchars($item['nome']) . "\"";
                    ?>
                            <div class="list-item" <?php echo $clickAction; ?>>
                                <span
                                    style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($item['nome']); ?></span>
                                <div class="progress-bar">
                                    <div class="progress-fill"
                                        style="width: <?php echo $width; ?>%; ">
                                    </div>
                                </div>
                                <span style="font-size: 12px; font-weight: bold;"><?php echo $item['total']; ?></span>
                            </div>
                        <?php
                        endforeach;
                    else: ?>
                        <div class="list-item"><span style="font-size:13px;">Sem dados ainda.</span></div>
                    <?php
                    endif; ?>

                    <div style="text-align:center; margin-top:10px;">
                        <a href="javascript:void(0)" onclick="openAlunosModal()"
                            style="font-size:12px; color:#64748B; text-decoration:none; font-weight: 600;">
                            Ver todos
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <div class="modal-overlay" id="detailModal">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title">
                    <h2>Relatório de Infrações: <span id="modalMonthTitle">Mês</span></h2>
                    <p style="font-size: 0.85rem; color: #64748B; margin-top: 4px;">Detalhamento completo dos registros.
                    </p>
                </div>
                <button class="btn-close-modal" onclick="closeModal()">&times;</button>
            </div>

            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Aluno</th>
                            <th>Infração (EPI)</th>
                            <th>Horário</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody">
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 10px; text-align: right;">
                <button class="btn-modal-action" onclick="alert('Relatório baixado!')">
                    Baixar PDF
                </button>
            </div>
        </div>
    </div>
    <div class="modal-overlay-calendar" id="calendarModal">
        <div class="calendar-wrapper">
            <button class="close-btn-cal" onclick="toggleCalendar()">✕</button>

            <header class="cal-header">
                <div class="month-nav-wrapper">
                    <button class="nav-btn-cal" id="prevMonth">❮</button>

                    <div class="selector-container" id="monthSelector">
                        <div class="selector-display" onclick="toggleMonthList()">
                            <span id="calMonthDisplay">Janeiro</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z" />
                            </svg>
                        </div>
                        <div class="selector-dropdown" id="monthDropdown">
                        </div>
                    </div>

                    <button class="nav-btn-cal" id="nextMonth">❯</button>
                </div>

                <div class="selector-container" id="yearSelector">
                    <div class="selector-display" onclick="toggleYearList()">
                        <span id="calYearDisplay">2026</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </div>
                    <div class="selector-dropdown" id="yearDropdown">
                    </div>
                </div>
            </header>

            <div class="calendar-body">
                <ul class="weeks">
                    <li>Dom</li>
                    <li>Seg</li>
                    <li>Ter</li>
                    <li>Qua</li>
                    <li>Qui</li>
                    <li>Sex</li>
                    <li>Sáb</li>
                </ul>
                <ul class="days" id="calendarDays"></ul>
            </div>

            <div class="input-area" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">
                <div class="input-wrapper"
                    style="display: flex; align-items: center; height: 38px; background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 8px;">

                    <i data-lucide="calendar-days" class="calendario" style="width: 18px; height: 18px; margin-right: 8px; color: #000; stroke: #000;"></i>

                    <input type="text" id="manualDateInput" placeholder="DD/MM/AAAA" maxlength="10"
                        style="border: none; background: transparent; outline: none; width: 100%; font-size: 13px; height: 100%; padding: 0;">

                    <button class="btn-action-right" onclick="commitManualDate()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="alunosRankingModal" class="modal-ranking-overlay" onclick="closeAlunosModal()">
        <div class="modal-ranking-square" onclick="event.stopPropagation()">

            <div class="modal-ranking-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2>Ranking Geral (<?php echo $isSuperAdmin ? 'Cursos' : 'Alunos'; ?>)</h2>
                        <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Lista completa de infrações</p>
                    </div>
                    <button onclick="closeAlunosModal()"
                        style="background:none; border:none; cursor:pointer; font-size:20px; color:#94a3b8;">&times;</button>
                </div>
            </div>

            <div class="modal-ranking-body">
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>Pos.</th>
                            <th><?php echo $isSuperAdmin ? 'Curso' : 'Aluno'; ?></th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rankingModal)): ?>
                            <?php foreach ($rankingModal as $index => $aluno): ?>
                                <tr class="ranking-row">
                                    <td>#<?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                                    <td>
                                        <span class="badge-count"><?php echo $aluno['total']; ?></span>
                                    </td>
                                </tr>
                            <?php
                            endforeach; ?>
                        <?php
                        else: ?>
                            <tr>
                                <td colspan="3" style="text-align:center;">Nenhum dado encontrado.</td>
                            </tr>
                        <?php
                        endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="padding: 15px; border-top: 1px solid #f1f5f9; text-align: center;">

            </div>
        </div>
    </div>

    <div id="notification-container"></div>

    <?php if ($isSuperAdmin): ?>
        <!-- Modal de Seleção de Curso -->
        <div id="courseSelectionModal" class="modal-ranking-overlay" onclick="closeCourseModal()">
            <div class="modal-ranking-square" style="max-width: 400px; height: auto; max-height: 80vh;" onclick="event.stopPropagation()">
                <div class="modal-ranking-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h2>Selecionar Curso</h2>
                            <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Escolha o curso para filtrar os dados</p>
                        </div>
                        <button onclick="closeCourseModal()" style="background:none; border:none; cursor:pointer; font-size:20px; color:#94a3b8;">&times;</button>
                    </div>
                </div>
                <div class="modal-ranking-body" style="padding: 15px;">
                    <div style="display: grid; gap: 8px;">
                        <button class="btn-modal-action" onclick="selectCourse('all', 'Todos')" style="width: 100%; text-align: left; background: #f8fafc; color: #1e293b; border: 1px solid #e2e8f0; padding: 12px; font-weight: 600;">
                            🌎 Todos os Cursos (Global)
                        </button>
                        <?php foreach ($cursosParaFiltro as $curso): ?>
                            <button class="btn-modal-action" onclick="selectCourse(<?php echo $curso['id']; ?>, '<?php echo addslashes($curso['nome']); ?>')" style="width: 100%; text-align: left; background: white; color: #334155; border: 1px solid #f1f5f9; padding: 12px;">
                                🎓 <?php echo htmlspecialchars($curso['nome']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script src="../js/dashboard.js" defer></script>
    <script src="../js/notifications.js" defer></script>
    <script src="../js/global.js"></script>
    <script src="../js/Dark.js"></script>
    <script src="../js/configuracao.js"></script>
</body>

</html>