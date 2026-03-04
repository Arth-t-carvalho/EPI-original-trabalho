<?php
$kpis = $dados->kpis;
$ranking = $dados->rankingCursos;
$conformidade = $dados->conformidade;
$percDia = $dados->percDia;
$percSemana = $dados->percSemana;
$percMes = $dados->percMes;
$isSuperAdmin = ($_SESSION['cargo'] === 'super_admin');
$cursosParaFiltro = $dados->cursosParaFiltro;
$nomeUsuario = $_SESSION['nome'] ?? 'Usuário';
$cargoUsuario = ucfirst($_SESSION['cargo'] ?? 'Visitante');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/dark.css">
    <link rel="stylesheet" href="css/transitions.css">
    <script src="js/Dark.js"></script>
    <script src="js/transitions.js"></script>
    <script>
        window.totalStudents = <?php echo $kpis['total_alunos'] ?? 0; ?>;
        window.userRole = '<?php echo strtolower($_SESSION['cargo']); ?>';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/Components/sidebar.view.php'; ?>

    <main class="main-content">
        <header class="header">
            <div class="page-title">
                <h1>Painel Geral</h1>
                <p>Laboratório B • Monitoramento em Tempo Real</p>
            </div>

            <div class="header-actions">
                <a href="index.php?route=configuracoes" class="btn-header-action" title="Configurações">
                    <i data-lucide="settings"></i>
                </a>

                <a href="index.php?route=infracoes" class="btn-header-action" title="Notificações">
                    <i data-lucide="bell"></i>
                    <span class="notif-badge" id="notifBadge">0</span>
                </a>

                <button class="btn-export" onclick="exportData()" style="margin-left: 10px;">
                    <i data-lucide="download"></i> Exportar
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
                    <a href="index.php?route=logout" class="btn-close-card" style="flex:1; background: #fee2e2; color: #dc2626; text-decoration: none; text-align: center; line-height: 1.5;">Sair</a>
                </div>
            </div>
        </header>

        <div class="kpi-grid">
            <div class="card" id="cardInfraDia" onclick="highlightDaily('dia')" style="cursor: pointer;">
                <div class="kpi-header">Infrações Diárias</div>
                <div class="kpi-value">
                    <span id="kpiDia"><?php echo $kpis['dia']; ?></span>
                    <span id="badgeDia" class="badge <?php echo $percDia >= 0 ? 'up' : 'down'; ?>">
                        <?php echo ($percDia >= 0 ? '↗ ' : '↘ ') . abs($percDia); ?>%
                    </span>
                </div>
            </div>
            <div class="card" onclick="highlightDaily('semana')">
                <div class="kpi-header">Infrações Semanais</div>
                <div class="kpi-value">
                    <span id="kpiSemana"><?php echo $kpis['semana']; ?></span>
                    <span id="badgeSemana" class="badge <?php echo $percSemana >= 0 ? 'up' : 'down'; ?>">
                        <?php echo ($percSemana >= 0 ? '↗ ' : '↘ ') . abs($percSemana); ?>%
                    </span>
                </div>
            </div>
            <div class="card" onclick="highlightDaily('mes')">
                <div class="kpi-header">Infrações Mês</div>
                <div class="kpi-value">
                    <span id="kpiMes"><?php echo $kpis['mes']; ?></span>
                    <span id="badgeMes" class="badge <?php echo $percMes >= 0 ? 'up' : 'down'; ?>">
                        <?php echo ($percMes >= 0 ? '↗ ' : '↘ ') . abs($percMes); ?>%
                    </span>
                </div>
            </div>
            <div class="card">
                <div class="kpi-header">Conformidade</div>
                <div class="kpi-value">
                    <span id="kpiMedia"><?php echo $conformidade; ?>%</span>
                    <?php if ($conformidade < 70): ?>
                        <span class="status-badge status-critico">🚨 CRÍTICO</span>
                    <?php elseif ($conformidade < 85): ?>
                        <span class="status-badge status-alto">🟠 ALTO RISCO</span>
                    <?php else: ?>
                        <span class="status-badge status-baixo">🟢 CONTROLADO</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card" style="height: 380px; display: flex; flex-direction: column; position: relative; margin-top:20px;">
            <div class="section-header">
                <span class="section-title">Infrações de EPIs (Anual)</span>
                <?php if ($isSuperAdmin): ?>
                    <button class="btn-filter-chart" onclick="openCourseModal()" title="Filtrar por Curso" id="btnFilterCourse">
                        <i data-lucide="filter"></i>
                        <span id="activeCourseName" class="active-course-name">Todos</span>
                    </button>
                <?php endif; ?>
            </div>
            <div style="flex: 1; position: relative;">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <div class="chart-grid" style="margin-top:20px; display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px;">
            <div class="card" id="cardRegistroDiario">
                <div class="section-header">
                    <span class="section-title">Registro Diário</span>
                </div>
                <div class="daily-nav-card">
                    <button class="nav-btn" onclick="changeDay(-1)"><i data-lucide="chevron-left"></i></button>
                    <div class="date-center" onclick="toggleCalendar()">
                        <div id="displayDayNum" class="big-day"><?php echo date('d'); ?></div>
                        <div id="displayMonthStr" class="month-year"><?php echo date('F Y'); ?></div>
                        <div class="expand-hint">Clique para expandir</div>
                    </div>
                    <button class="nav-btn" onclick="changeDay(1)"><i data-lucide="chevron-right"></i></button>
                </div>
                <div class="occurrences-list" id="occurrenceList">
                    <!-- Carregado via JS -->
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

            <div class="card card-ranking">
                <div class="section-header">
                    <span class="section-title">Cursos com mais infrações</span>
                </div>
                <div class="ranking-list">
                    <?php if (empty($ranking)): ?>
                        <div class="empty-state">Nenhum dado de ranking disponível.</div>
                    <?php else: ?>
                        <?php foreach (array_slice($ranking, 0, 5) as $item): ?>
                            <div class="ranking-item">
                                <div class="ranking-info">
                                    <span class="course-name"><?php echo htmlspecialchars($item['nome']); ?></span>
                                    <span class="course-count"><?php echo $item['total']; ?></span>
                                </div>
                                <div class="ranking-bar-container">
                                    <div class="ranking-bar-fill" style="width: <?php echo $item['porcentagem']; ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="ranking-footer">
                    <a href="index.php?route=gestao-cursos" class="btn-link">Ver todos</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Modais omitidos para brevidade mas devem ser incluídos conforme necessário -->

    <div id="notification-container"></div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script src="js/dashboard.js" defer></script>
    <script src="js/notifications.js" defer></script>
    <script src="js/global.js"></script>
    <?php include __DIR__ . '/Components/ai_assistant.view.php'; ?>
</body>

</html>