<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI GUARD | Cadastro</title>
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
                <p>Cadastro de Usuário</p>
            </div>

            <div class="login-footer-left">
            </div>
        </div>

        <!-- Lado Direito: Formulário de Cadastro -->
        <div class="login-right">

            <?php if (isset($_GET['erro'])): ?>
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <?php
    if ($_GET['erro'] == 'formato')
        echo "Apenas Gmail ou CPF são permitidos.";
    else if ($_GET['erro'] == 'existe')
        echo "Este e-mail/CPF já possui conta.";
    else if ($_GET['erro'] == 'nao_autorizado')
        echo "Este Gmail/CPF não está autorizado. Contate o administrador.";
    else
        echo "Erro ao cadastrar. Tente novamente.";
?>
                </div>
            <?php
endif; ?>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="success-message">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                    Conta criada com sucesso!
                </div>
            <?php
endif; ?>
            <div class="login-right-header">
                <h2>Cadastrar</h2>
                <p>Insira as suas credenciais administrativas.</p>
            </div>

            <form method="POST" action="../config/registrar.php">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>NOME COMPLETO</label>
                    <div class="input-wrapper">
                        <input type="text" name="nome" placeholder="Ex: Arthur Silva" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>GMAIL OU CPF</label>
                    <div class="input-wrapper">
                        <input type="text" name="usuario" id="registerUser" placeholder="exemplo@gmail.com ou CPF" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>SENHA</label>
                    <div class="input-wrapper">
                        <input type="password" name="senha" id="passwordInput" placeholder="••••••••" required>
                        <div class="eye-icon" id="togglePassword">
                            <i data-lucide="eye-off"></i>
                        </div>
                    </div>
                </div>

                <br>
                <button type="submit" class="btn-login" style="margin-bottom: 15px;">
                    CRIAR CONTA <i data-lucide="chevron-right"></i>
                </button>

                <div class="bottom-links" style="margin-top: 10px;">
                    <a href="index.php">Já tem conta? Faça Login</a>
                </div>
            </form>

          
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const user = document.querySelector('#registerUser').value.trim();
                
                const isGmail = user.toLowerCase().endsWith('@gmail.com');
                const isCPF = /^\d{11}$/.test(user.replace(/\D/g, ''));

                if (!isGmail && !isCPF) {
                    e.preventDefault();
                    alert("Por favor, insira um Gmail válido ou um CPF (apenas números).");
                    return;
                }
            });

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
    </script>
</body>

</html>
