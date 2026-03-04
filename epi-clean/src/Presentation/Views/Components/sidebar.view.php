<?php
$current_route = $_GET['route'] ?? 'dashboard';
?>
<aside class="sidebar">
    <div class="brand">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3">
            <circle cx="12" cy="12" r="10" />
        </svg>
        &nbsp; EPI <span>GUARD</span>
    </div>

    <nav class="nav-menu">
        <a class="nav-item <?= ($current_route == 'dashboard') ? 'active' : ''; ?>" href="index.php?route=dashboard">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>

        <a class="nav-item <?= ($current_route == 'infracoes') ? 'active' : ''; ?>" href="index.php?route=infracoes">
            <i data-lucide="alert-triangle"></i>
            <span>Infrações</span>
        </a>

        <a class="nav-item <?= ($current_route == 'ocorrencias') ? 'active' : ''; ?>" href="index.php?route=ocorrencias">
            <i data-lucide="file-text"></i>
            <span>Ocorrências</span>
        </a>

        <?php if (isset($_SESSION['cargo']) && $_SESSION['cargo'] === 'super_admin'): ?>
            <div class="nav-group <?= (in_array($current_route, ['gestao-alunos', 'gestao-cursos'])) ? 'open' : ''; ?>">
                <div class="nav-item nav-group-trigger" onclick="this.parentElement.classList.toggle('open')">
                    <i data-lucide="shield-check"></i>
                    <span>Gestão</span>
                    <i data-lucide="chevron-down" class="chevron"></i>
                </div>
                <div class="nav-submenu">
                    <a class="nav-subitem <?= ($current_route == 'gestao-alunos') ? 'active' : ''; ?>" href="index.php?route=gestao-alunos">
                        <i data-lucide="graduation-cap"></i>
                        <span>Alunos</span>
                    </a>
                    <a class="nav-subitem <?= ($current_route == 'gestao-professores') ? 'active' : ''; ?>" href="index.php?route=gestao-professores">
                        <i data-lucide="user-plus"></i>
                        <span>Professores</span>
                    </a>
                    <a class="nav-subitem <?= ($current_route == 'gestao-cursos') ? 'active' : ''; ?>" href="index.php?route=gestao-cursos">
                        <i data-lucide="book-open"></i>
                        <span>Cursos</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <a class="nav-item <?= ($current_route == 'monitoramento') ? 'active' : ''; ?>" href="index.php?route=monitoramento">
            <i data-lucide="monitor"></i>
            <span>Monitoramento</span>
        </a>

        <a class="nav-item <?= ($current_route == 'configuracoes') ? 'active' : ''; ?>" href="index.php?route=configuracoes">
            <i data-lucide="settings"></i>
            <span>Configurações</span>
        </a>

        <div class="nav-item" onclick="toggleAssistenteIA()" style="cursor: pointer; margin-top: auto; color: var(--primary);">
            <i data-lucide="sparkles"></i>
            <span>Assistente IA</span>
        </div>
    </nav>
</aside>

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>