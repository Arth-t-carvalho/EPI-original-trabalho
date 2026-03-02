<?php
// Identifica a página atual para marcar a classe "active" no menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
    <aside class="sidebar">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3"
                style="filter: drop-shadow(0 2px 4px rgba(227, 6, 19, 0.3));">
                <circle cx="12" cy="12" r="10" />
            </svg>

            &nbsp; EPI <span>GUARD</span>
        </div>

        <nav class="nav-menu">

            <a class="nav-item <?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>

             <?php if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] !== 'super_admin'): ?>
             <a class="nav-item <?= ($current_page == 'monitoramento.php') ? 'active' : ''; ?>" href="monitoramento.php">
                <i data-lucide="monitor"></i>
                <span>Monitoramento</span>
            </a>
            <?php endif; ?>

            <a class="nav-item <?= ($current_page == 'infracoes.php') ? 'active' : ''; ?>" href="infracoes.php">
                <i data-lucide="alert-triangle"></i>
                <span>Infrações</span>
            </a>

            <a class="nav-item <?= ($current_page == 'controleSala.php') ? 'active' : ''; ?>" href="controleSala.php">
                <i data-lucide="users"></i>
                <span>Controle de Sala</span>
            </a>

            <?php if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] !== 'super_admin'): ?>
            <a class="nav-item <?= ($current_page == 'ocorrencias.php') ? 'active' : ''; ?>" href="ocorrencias.php">
                <i data-lucide="file-text"></i>
                <span>Ocorrências</span>
            </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['cargo']) && $_SESSION['cargo'] === 'super_admin'): ?>
                <div class="nav-group <?= (in_array($current_page, ['gestao_alunos.php', 'gestao_cursos.php', 'gestao_professores.php'])) ? 'active' : ''; ?>">
                    <div class="nav-item nav-group-trigger" onclick="toggleNavGroup(this)">
                        <i data-lucide="shield-check"></i>
                        <span>Gestão</span>
                        <i data-lucide="chevron-down" class="chevron"></i>
                    </div>
                    <div class="nav-submenu">
                        <a class="nav-subitem <?= ($current_page == 'gestao_alunos.php') ? 'active' : ''; ?>" href="gestao_alunos.php">
                            <i data-lucide="graduation-cap"></i>
                            <span>Alunos</span>
                        </a>
                        <a class="nav-subitem <?= ($current_page == 'gestao_ocorrencias.php') ? 'active' : ''; ?>" href="gestao_ocorrencias.php">
                            <i data-lucide="file-check"></i>
                            <span>Ocorrências</span>
                        </a>
                        <a class="nav-subitem <?= ($current_page == 'gestao_cursos.php') ? 'active' : ''; ?>" href="gestao_cursos.php">
                            <i data-lucide="book-open"></i>
                            <span>Cursos</span>
                        </a>
                        <a class="nav-subitem <?= ($current_page == 'gestao_professores.php') ? 'active' : ''; ?>" href="gestao_professores.php">
                            <i data-lucide="user-plus"></i>
                            <span>Professores</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <a class="nav-item <?= ($current_page == 'configuracoes.php') ? 'active' : ''; ?>" href="configuracoes.php">
                <i data-lucide="settings"></i>
                <span>Configurações</span>
            </a>
           
            <!-- Indicador Deslizante -->
            <div class="nav-active-indicator" id="navIndicator"></div>

        </nav>

        <!-- Assistente de IA -->
        <div class="ai-assistant-container">
            <div class="ai-chat-window" id="aiChatWindow">
                <div class="ai-chat-header">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="sparkles" style="width:16px; color:#E30613;"></i>
                        <span>Assistente EPI Guard</span>
                    </div>
                    <button onclick="toggleAIChat()" style="background:none; border:none; color:#64748b; cursor:pointer;">&times;</button>
                </div>
                <div class="ai-chat-messages" id="aiChatMessages">
                    <div class="ai-message bot">Olá! Para começarmos, por favor, **coloque a chave da sua API do Gemini** no arquivo `apis/api_ai.php` ou informe-a aqui se o sistema estiver configurado para processá-la.</div>
                </div>
                <div class="ai-chat-input">
                    <input type="text" id="aiInput" placeholder="Pergunte algo..." onkeypress="handleAIKey(event)">
                    <button onclick="sendAIMessage()">
                        <i data-lucide="send" style="width:16px;"></i>
                    </button>
                </div>
            </div>
            
            <button class="ai-toggle-btn" onclick="toggleAIChat()" id="aiToggleBtn">
                <i data-lucide="sparkles"></i>
                <span>Assistente IA</span>
            </button>
        </div>
    </aside>

    <script src="../js/ai_assistant.js" defer></script>
    <script>
        lucide.createIcons();
        
        function toggleNavGroup(element) {
            const group = element.parentElement;
            group.classList.toggle('open');
        }

        (function() {
            const menu = document.querySelector('.nav-menu');
            const indicator = document.getElementById('navIndicator');
            const activeItem = menu.querySelector('.nav-item.active, .nav-subitem.active');

            if (activeItem && indicator) {
                const updateIndicator = (target) => {
                    // Se for subitem, o indicador deve apontar para o pai (Gestão) ou se comportar diferente?
                    // Por padrão do design, vamos manter no item principal se for subitem
                    let indicatorTarget = target;
                    if(target.classList.contains('nav-subitem')) {
                        indicatorTarget = target.closest('.nav-group').querySelector('.nav-item');
                    }

                    const top = indicatorTarget.offsetTop;
                    const height = indicatorTarget.offsetHeight;
                    
                    indicator.style.transform = `translateY(${top}px)`;
                    indicator.style.height = `${height}px`;
                    indicator.classList.add('ready');
                };

                updateIndicator(activeItem);
                
                // Se um subitem estiver ativo, abre o grupo automaticamente
                const activeSub = menu.querySelector('.nav-subitem.active');
                if(activeSub) {
                    activeSub.closest('.nav-group').classList.add('open');
                }
            }
        })();
    </script>
