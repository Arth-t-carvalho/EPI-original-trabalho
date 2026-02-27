<?php
// ARQUIVO: php/dashboard.php

// Ajuste os requires conforme a localização da sua pasta config
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// CONFIGURAÇÃO DO PROFESSOR
$cursoId = 1;

// DADOS DO USUÁRIO
$sqlUser = "SELECT nome, cargo FROM usuarios WHERE usuario = ? LIMIT 1";
$stmtUser = mysqli_prepare($conn, $sqlUser);
$userRef = $_SESSION['usuario_nome'] ?? 'admin';
mysqli_stmt_bind_param($stmtUser, "s", $userRef);
mysqli_stmt_execute($stmtUser);
$resUser = mysqli_stmt_get_result($stmtUser);
$userData = mysqli_fetch_assoc($resUser);

$nomeUsuario = $userData['nome'] ?? 'Usuário';
$cargoUsuario = ucfirst($userData['cargo'] ?? 'Visitante');

// KPIs
// Infrações do Dia
$sqlDia = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() AND o.data_hora < CURDATE() + INTERVAL 1 DAY";
$stmtDia = mysqli_prepare($conn, $sqlDia);
mysqli_stmt_bind_param($stmtDia, "i", $cursoId);
mysqli_stmt_execute($stmtDia);
$resDia = mysqli_stmt_get_result($stmtDia);
$infraDia = mysqli_fetch_assoc($resDia)['total'] ?? 0;

// Infrações da Semana
$sqlSemana = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE(), 1)";
$stmtSemana = mysqli_prepare($conn, $sqlSemana);
mysqli_stmt_bind_param($stmtSemana, "i", $cursoId);
mysqli_stmt_execute($stmtSemana);
$resSemana = mysqli_stmt_get_result($stmtSemana);
$infraSemana = mysqli_fetch_assoc($resSemana)['total'] ?? 0;

// Infrações do Mês
$sqlMes = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND MONTH(o.data_hora) = MONTH(CURDATE()) AND YEAR(o.data_hora) = YEAR(CURDATE())";
$stmtMes = mysqli_prepare($conn, $sqlMes);
mysqli_stmt_bind_param($stmtMes, "i", $cursoId);
mysqli_stmt_execute($stmtMes);
$resMes = mysqli_stmt_get_result($stmtMes);
$infraMes = mysqli_fetch_assoc($resMes)['total'] ?? 0;

// MÉDIA TURMA
$sqlTotalAlunos = "SELECT COUNT(*) as total FROM alunos WHERE curso_id = ?";
$stmtAlunosTotal = mysqli_prepare($conn, $sqlTotalAlunos);
mysqli_stmt_bind_param($stmtAlunosTotal, "i", $cursoId);
mysqli_stmt_execute($stmtAlunosTotal);
$resTotalAlunos = mysqli_stmt_get_result($stmtAlunosTotal);
$totalAlunos = (int) (mysqli_fetch_assoc($resTotalAlunos)['total'] ?? 0);

if ($totalAlunos === 0) {
    $mediaTurma = 100;
} else {
    $mediaTurma = 100 - (($infraMes / $totalAlunos) * 100);
    $mediaTurma = max(0, min(100, round($mediaTurma)));
}

// ALUNOS CRÍTICOS
$sqlCriticos = "SELECT a.nome, COUNT(o.id) AS total FROM alunos a JOIN ocorrencias o ON a.id = o.aluno_id WHERE a.curso_id = ? GROUP BY a.id ORDER BY total DESC LIMIT 5";
$stmtAlunosCriticos = mysqli_prepare($conn, $sqlCriticos);
mysqli_stmt_bind_param($stmtAlunosCriticos, "i", $cursoId);
mysqli_stmt_execute($stmtAlunosCriticos);
$resCriticos = mysqli_stmt_get_result($stmtAlunosCriticos);
$alunosCriticos = mysqli_fetch_all($resCriticos, MYSQLI_ASSOC);

// COMPARAÇÕES
// Ontem
$sqlOntem = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() - INTERVAL 1 DAY AND o.data_hora < CURDATE()";
$stmtOntem = mysqli_prepare($conn, $sqlOntem);
mysqli_stmt_bind_param($stmtOntem, "i", $cursoId);
mysqli_stmt_execute($stmtOntem);
$infraOntem = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmtOntem))['total'] ?? 0);
$percDia = ($infraOntem > 0) ? round((($infraDia - $infraOntem) / $infraOntem) * 100, 1) : ($infraDia * 100);

// Semana Anterior
$sqlSemAnt = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)";
$stmtSemAnt = mysqli_prepare($conn, $sqlSemAnt);
mysqli_stmt_bind_param($stmtSemAnt, "i", $cursoId);
mysqli_stmt_execute($stmtSemAnt);
$infraSemanaAnterior = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSemAnt))['total'] ?? 0);
$percSemana = ($infraSemanaAnterior > 0) ? round((($infraSemana - $infraSemanaAnterior) / $infraSemanaAnterior) * 100, 1) : ($infraSemana * 100);

// Mês Anterior
$sqlMesAnt = "SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND MONTH(o.data_hora) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(o.data_hora) = YEAR(CURDATE() - INTERVAL 1 MONTH)";
$stmtMesAnt = mysqli_prepare($conn, $sqlMesAnt);
mysqli_stmt_bind_param($stmtMesAnt, "i", $cursoId);
mysqli_stmt_execute($stmtMesAnt);
$infraMesAnterior = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMesAnt))['total'] ?? 0);
$percMes = ($infraMesAnterior > 0) ? round((($infraMes - $infraMesAnterior) / $infraMesAnterior) * 100, 1) : ($infraMes * 100);

// Ranking Modal
$sqlRanking = "SELECT a.nome, COUNT(o.id) AS total FROM alunos a JOIN ocorrencias o ON a.id = o.aluno_id WHERE a.curso_id = ? GROUP BY a.id ORDER BY total DESC";
$stmtRankingModal = mysqli_prepare($conn, $sqlRanking);
mysqli_stmt_bind_param($stmtRankingModal, "i", $cursoId);
mysqli_stmt_execute($stmtRankingModal);
$rankingModal = mysqli_fetch_all(mysqli_stmt_get_result($stmtRankingModal), MYSQLI_ASSOC);
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
                <button class="btn-export" onclick="exportData()">
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
                <button class="btn-close-card" onclick="toggleInstructorCard()">Sair</button>
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
                    <span class="section-title">Alunos + Infrações</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">

                    <?php
                    if (count($alunosCriticos) > 0):
                        foreach ($alunosCriticos as $aluno):
                            $width = ($aluno['total'] > 20) ? 100 : ($aluno['total'] * 5);
                            $color = ($aluno['total'] > 10) ? '#E30613' : '#1F2937';
                            ?>
                            <div class="list-item">
                                <span
                                    style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($aluno['nome']); ?></span>
                                <div class="progress-bar">
                                    <div class="progress-fill"
                                        style="width: <?php echo $width; ?>%; ">
                                    </div>
                                </div>
                                <span style="font-size: 12px; font-weight: bold;"><?php echo $aluno['total']; ?></span>
                            </div>
                        <?php endforeach; else: ?>
                        <div class="list-item"><span style="font-size:13px;">Sem dados ainda.</span></div>
                    <?php endif; ?>

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

                    <svg class="icon-left" style="width: 16px; height: 16px; fill: #9CA3AF; margin-right: 8px;">
                        <path
                            d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                    </svg>

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
                        <h2>Ranking Geral</h2>
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
                            <th>Aluno</th>
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
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align:center;">Nenhum dado encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="padding: 15px; border-top: 1px solid #f1f5f9; text-align: center;">

            </div>
        </div>
    </div>

    <div id="notification-container"></div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script src="../js/dashboard.js"></script>
   <script src="../js/global.js"></script>
    <script src="../js/Dark.js"></script>
    <script src="../js/configuracao.js"></script>
</body>

</html>