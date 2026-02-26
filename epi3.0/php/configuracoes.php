<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI GUARD | Configurações</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../css/configuracoes.css">
    <link rel="stylesheet" href="../css/dashboard.css">

    <style>

    </style>
</head>

<body>

       <aside class="sidebar">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3"
                style="filter: drop-shadow(0 2px 4px rgba(227, 6, 19, 0.3));">
                <circle cx="12" cy="12" r="10" />
            </svg>

            &nbsp; EPI <span>GUARD</span>
        </div>

        <nav class="nav-menu">

            <a class="nav-item " href="dashboard.php">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>

            <a class="nav-item" href="infracoes.php">
                <i data-lucide="alert-triangle"></i>
                <span>Infrações</span>
            </a>

            <a class="nav-item" href="controleSala.php">
                <i data-lucide="users"></i>
                <span>Controle de Sala</span>
            </a>

            <a class="nav-item" href="ocorrencias.php">
                <i data-lucide="file-text"></i>
                <span>Ocorrências</span>
            </a>

            <a class="nav-item active" href="configuracoes.php">
                <i data-lucide="settings"></i>
                <span>Configurações</span>
            </a>
            <a class="nav-item" href="monitoramento.php">
                <i data-lucide="monitor"></i>
                <span>Monitoramento</span>
            </a>

        </nav>
    </aside>
    <main>
        <div id="view-config" class="view-section active">
            <header>
                <div class="page-title">
                    <h1>Configurações do Sistema</h1>
                    <p>Personalize a aparência e o comportamento da dashboard</p>
                </div>
            </header>

            <div class="config-grid">

                <div class="config-card">
                    <div class="config-header"><i data-lucide="monitor"></i> Interface</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Modo Noturno</span>
                            <small>Alternar tema escuro/claro</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-darkmode" onchange="toggleTheme()">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Exibir Porcentagem</span>
                            <small>Mostrar % nos cards do topo</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked id="toggle-percent"
                                onchange="toggleVisibility('.percent-wrapper')">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Exibir Status (Badges)</span>
                            <small>Mostrar/Ocultar fundo colorido</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked id="toggle-status" onchange="toggleStatus()">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-header"><i data-lucide="pie-chart"></i> Gráfico de EPIs</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Tipo de Gráfico</span>
                            <small>Altera visual do fieldset</small>
                        </div>
                        <select class="form-select" onchange="changeChartType(this.value)">
                            <option value="donut">Rosca</option>
                            <option value="bar">Barras</option>
                            <option value="line">Linha</option>
                        </select>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Cor do Destaque</span>
                            <small>Muda cor dos gráficos</small>
                        </div>
                        <input type="color" value="#E30613" onchange="changeChartColor(this.value)">
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-header"><i data-lucide="mouse-pointer"></i> Interatividade</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Link nos Cards de Infrações</span>
                            <small>clique nos cards de infração para ir para outras paginas</small>

                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-link" onchange="toggleLinkAbility()">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Link nos Cards</span>
                            <small>Permitir clique para detalhes</small>

                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-link" onchange="toggleLinkAbility()">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                <div class="config-card">
                    <div class="config-header"><i data-lucide="refresh-cw"></i> Atualização de Dados</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Auto-Refresh</span>
                            <small>Permitir que as informações mude</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Intervalo</span>
                            <small>Frequência de busca</small>
                        </div>
                        <select class="form-select" style="width: 140px;">
                            <option>Tempo Real</option>
                            <option>30 Segundos</option>
                            <option>1 Minuto</option>
                            <option>5 Minutos</option>
                        </select>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-header"><i data-lucide="bell"></i> Alertas e Sons</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Alerta Sonoro</span>
                            <small>Tocar bip ao detectar infração</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Notificação</span>
                            <small>Enviar e-mail se infração crítica</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <script>
        function toggleDarkMode() {
            const body = document.documentElement; // ou document.body

            if (body.getAttribute("data-theme") === "dark") {
                body.removeAttribute("data-theme");
            } else {
                body.setAttribute("data-theme", "dark");
            }
        }
        // Inicializa ícones do Lucide
        lucide.createIcons();

        // Variável de estado para link habilitado
        let linksEnabled = false;

        // 1. Lógica do Clique no Card (Com trava)
        function toggleLinkAbility() {
            linksEnabled = document.getElementById('toggle-link').checked;

            // Adiciona feedback visual (cursor pointer)
            const cards = document.querySelectorAll('.card');
            cards.forEach(c => {
                if (linksEnabled) c.classList.add('clickable');
                else c.classList.remove('clickable');
            });
        }

        function handleCardClick(cardId) {
            if (linksEnabled) {
                // Simula ir para outra página
                alert(`Redirecionando para detalhes de: ${cardId}`);
                // window.location.href = 'infracoes.php?filtro=' + cardId;
            }
        }

        // 2. Dark Mode
        function toggleTheme() {
            const isDark = document.getElementById('toggle-darkmode').checked;
            document.body.setAttribute('data-theme', isDark ? 'dark' : 'light');
        }

        // 3. Visibilidade de Porcentagem
        function toggleVisibility(selector) {
            const isChecked = document.getElementById('toggle-percent').checked;
            document.querySelectorAll(selector).forEach(el => {
                el.style.display = isChecked ? 'inline' : 'none';
            });
        }

        // 4. Visibilidade de Status (Badges inteiros)
        function toggleStatus() {
            const isChecked = document.getElementById('toggle-status').checked;
            document.querySelectorAll('.status-wrapper').forEach(el => {
                if (!isChecked) {
                    el.style.background = 'transparent';
                    el.style.border = 'none';
                    el.style.color = 'var(--text-muted)';
                    el.querySelector('svg').style.display = 'none';
                } else {
                    el.style.background = '';
                    el.style.border = '';
                    el.style.color = '';
                    el.querySelector('svg').style.display = 'inline';
                }
            });
        }

        // 5. Troca de Tipo de Gráfico (Fieldset)
        function changeChartType(type) {
            document.getElementById('chart-donut').style.display = 'none';
            document.getElementById('chart-bar').style.display = 'none';
            document.getElementById('chart-line').style.display = 'none';

            if (type === 'donut') document.getElementById('chart-donut').style.display = 'flex';
            if (type === 'bar') document.getElementById('chart-bar').style.display = 'flex';
            if (type === 'line') document.getElementById('chart-line').style.display = 'block';
        }

        // 6. Troca de Cor Dinâmica
        function changeChartColor(color) {
            document.documentElement.style.setProperty('--chart-main-color', color);
        }

    </script>
</body>

</html>