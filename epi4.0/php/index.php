<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI GUARD | Login</title>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Estilos -->
    <link rel="stylesheet" href="../css/login_novo.css">
    <style>
        /* Ajuste para evitar que o overlay apareça antes do tempo */
        .transition-overlay { display: none; }
        .transition-overlay.active { display: flex; }
    </style>
</head>

<body class="intro-active">

    <!-- Overlay de Introdução -->
    <div id="introOverlay" class="intro-overlay">
        <div class="intro-panel"></div>
        <div class="intro-content">
            <div class="intro-logo">SENAI</div>
            <div class="intro-subtext">AUTENTICAR SISTEMA</div>
        </div>
    </div>

    <div class="login-card">
        <!-- Lado Esquerdo: Branding / Hero -->
        <div class="login-left">
            <div class="brand-logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                </svg>
                EPI GUARD
            </div>

            <div class="hero-text">
                <h1>SENAI</h1>
                <p>Portal administrativo para monitorização de segurança industrial e gestão de EPIs.</p>
            </div>

            <div class="login-footer-left">
            </div>
        </div>

        <!-- Lado Direito: Formulário de Autenticação -->
        <div class="login-right">
            <div class="login-right-header">
                <h2>Acessar</h2>
                <p>Insira as suas credenciais administrativas.</p>
            </div>

            <?php if (isset($_GET['erro'])): ?>
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <?php
    if ($_GET['erro'] == 'formato')
        echo "Apenas Gmail ou CPF são permitidos.";
    else
        echo "Usuário ou senha inválidos";
?>
                </div>
            <?php
endif; ?>

            <form method="POST" action="../config/autenticar.php">
                <div class="form-group">
                    <label>GMAIL OU CPF</label>
                    <div class="input-wrapper">
                        <input type="text" name="usuario" id="loginUser" placeholder="exemplo@gmail.com ou CPF" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>SENHA</label>
                    <div class="input-wrapper">
                        <input type="password" name="senha" id="passwordInput" placeholder="••••••••" required>
                        <div class="eye-icon" id="togglePassword">
                            <i data-lucide="eye-off"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-login" onclick="handleLogin(event)">
                    ENTRAR <i data-lucide="chevron-right"></i>
                </button>

                <div class="bottom-links">
                    <a href="redefinir_senha.php">Esqueceu a senha?</a>
                    <a href="cadastro.php">Não tem uma conta? Cadastre-se</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Overlay de Transição -->
    <div id="transitionOverlay" class="transition-overlay">
        <div class="transition-panel"></div>
        <div class="transition-content">
            <div class="transition-logo">SENAI</div>
            <div class="transition-text">A AUTENTICAR SISTEMA</div>
            <div class="progress-container">
                <div id="progressFill" class="progress-fill"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // Lógica da Animação de Entrada (Intro)
            const introOverlay = document.getElementById('introOverlay');
            if (introOverlay) {
                setTimeout(() => {
                    introOverlay.classList.add('fade-out');
                    document.body.classList.remove('intro-active');
                    setTimeout(() => {
                        introOverlay.remove();
                    }, 1000); // Tempo do fade-out
                }, 2500); // Tempo de exibição da intro
            }

            // Lógica de mostrar/ocultar senha
            const togglePassword = document.querySelector('#togglePassword');
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const passwordInput = document.querySelector('#passwordInput');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    const iconContainer = this;
                    const newIconName = type === 'password' ? 'eye-off' : 'eye';
                    
                    iconContainer.innerHTML = `<i data-lucide="${newIconName}"></i>`;
                    lucide.createIcons();
                });
            }
        });

        async function handleLogin(e) {
            e.preventDefault();
            const form = e.target.closest('form');
            const user = document.querySelector('#loginUser').value.trim();
            const pass = document.querySelector('#passwordInput').value;

            // Validação de Gmail ou CPF (Frontend)
            const isGmail = user.toLowerCase().endsWith('@gmail.com');
            const isCPF = /^\d{11}$/.test(user.replace(/\D/g, ''));

            if (!isGmail && !isCPF) {
                alert("Por favor, insira um Gmail válido ou um CPF (apenas números).");
                return;
            }

            if (!user || !pass) {
                alert("Preencha todos os campos.");
                return;
            }

            // Inicia Animação de Transição
            const card = document.querySelector('.login-card');
            const overlay = document.getElementById('transitionOverlay');
            const progressFill = document.getElementById('progressFill');

            // 1. Fade out suave do card
            card.classList.add('fade-out');

            setTimeout(() => {
                // 2. Ativa overlay e expande painel vermelho
                overlay.classList.add('active');
                
                setTimeout(() => {
                    // 3. Inicia preenchimento da barra de progresso (1.5s)
                    progressFill.style.width = '100%';
                    
                    setTimeout(() => {
                        // 4. Envia o formulário após a animação
                        form.submit();
                    }, 1600); // 1.5s da barra + um tempinho de segurança
                }, 500);
            }, 300);
        }
    </script>
</body>

</html>