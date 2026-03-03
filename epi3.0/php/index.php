        <title>EPI GUARD | Login</title>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Estilos -->
    <link rel="stylesheet" href="../css/login_novo.css">
</head>

<body>

    <div class="login-card <?php echo (isset($_GET['from']) && $_GET['from'] == 'back') ? 'card-transition-enter-back' : 'card-transition-enter'; ?>">
        <div class="card-bg-branding">EPI GUARD</div>
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

            <div id="dynamicError" class="error-message" style="display: none;"></div>
            <?php if (isset($_GET['erro'])): ?>
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <?php 
                        if($_GET['erro'] == 'formato') echo "Apenas Gmail ou CPF são permitidos.";
                        else echo "Usuário ou senha inválidos";
                    ?>
                </div>
            <?php endif; ?>

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
                    <a href="redefinir_senha.php" id="linkEsqueciSenha">Esqueceu a senha?</a>
                    <a href="cadastro.php">Não tem uma conta? Cadastre-se</a>
                </div>
            </form>
        </div>
    </div>





 <!-- Overlay de Transição Premium SENAI -->
    <div id="transitionOverlay" class="transition-overlay">
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

            // Transição Premium V5 para navegação interna
            const internalLinks = document.querySelectorAll('.bottom-links a');
            internalLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    const card = document.querySelector('.login-card');
                    card.classList.add('card-transition-exit');
                    
                    setTimeout(() => {
                        window.location.href = href;
                    }, 250);
                });
            });

            // Lógica Universal: Mostrar/Ocultar Senha
            const togglePasswordIcons = document.querySelectorAll('.eye-icon');
            togglePasswordIcons.forEach(icon => {
                icon.addEventListener('click', function() {
                    const passwordInput = this.parentElement.querySelector('input');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    const newIconName = type === 'password' ? 'eye-off' : 'eye';
                    this.innerHTML = `<i data-lucide="${newIconName}"></i>`;
                    lucide.createIcons();
                });
            });
        });

        async function handleLogin(e) {
            e.preventDefault();
            const form = e.target.closest('form');
            const user = document.querySelector('#loginUser').value.trim();
            const pass = document.querySelector('#passwordInput').value;
            const errorDiv = document.querySelector('#dynamicError');

            // Validação de Gmail ou CPF (Frontend)
            const isGmail = user.toLowerCase().endsWith('@gmail.com');
            const isCPF = /^\d{11}$/.test(user.replace(/\D/g, ''));

            if (!user || !pass) {
                errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> Preencha todos os campos.`;
                errorDiv.style.display = 'flex';
                lucide.createIcons();
                return;
            }

            if (!isGmail && !isCPF) {
                errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> Gmail ou CPF inválido.`;
                errorDiv.style.display = 'flex';
                lucide.createIcons();
                return;
            }

            errorDiv.style.display = 'none';

            try {
                const formData = new FormData(form);
                formData.append('ajax', 'true');

                const response = await fetch('../config/autenticar.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (result.success) {
                    // SUCESSO: Dispara Animação Premium SENAI
                    const card = document.querySelector('.login-card');
                    const overlay = document.getElementById('transitionOverlay');
                    const progressFill = document.getElementById('progressFill');
                    const content = document.querySelector('.transition-content');

                    overlay.style.display = 'flex'; 
                    setTimeout(() => {
                        overlay.classList.add('active');
                        card.classList.add('blur-start');

                        setTimeout(() => {
                            progressFill.style.width = '100%';
                            setTimeout(() => {
                                content.classList.add('leaving');
                                setTimeout(() => {
                                    window.location.href = result.redirect;
                                }, 700);
                            }, 1000); 
                        }, 800); 
                    }, 50);
                } else {
                    // ERRO: Exibe mensagem sem animação
                    let msg = "Usuário ou senha incorretos.";
                    if(result.message === 'formato') msg = "Apenas Gmail ou CPF são permitidos.";
                    else if(result.message === 'campos') msg = "Preencha todos os campos.";
                    
                    errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> ${msg}`;
                    errorDiv.style.display = 'flex';
                    lucide.createIcons();
                }
            } catch (error) {
                console.error("Erro na autenticação:", error);
                errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> Erro de conexão com o servidor.`;
                errorDiv.style.display = 'flex';
                lucide.createIcons();
            }
        }
    </script>
</body>

</html>