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
</head>

<body>

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
                <h2>Sign In</h2>
                <p>Insira as suas credenciais administrativas.</p>
            </div>

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

                <button type="submit" class="btn-login">
                    ENTRAR <i data-lucide="chevron-right"></i>
                </button>

                <div class="bottom-links">
                    <a href="redefinir_senha.php">Esqueceu a senha?</a>
                    <a href="cadastro.php">Não tem uma conta? Cadastre-se</a>
                </div>
            </form>

      
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const user = document.querySelector('#loginUser').value.trim();
                
                const isGmail = user.toLowerCase().endsWith('@gmail.com');
                const isCPF = /^\d{11}$/.test(user.replace(/\D/g, ''));

                if (!isGmail && !isCPF) {
                    e.preventDefault();
                    alert("Por favor, insira um Gmail válido ou um CPF (apenas números).");
                }
            });

            togglePassword.addEventListener('click', function() {
                const passwordInput = document.querySelector('#passwordInput');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Alterna o ícone baseado no estado
                const iconContainer = this;
                const newIconName = type === 'password' ? 'eye-off' : 'eye';
                
                iconContainer.innerHTML = `<i data-lucide="${newIconName}"></i>`;
                lucide.createIcons();
            });
        });
    </script>
</body>

</html>