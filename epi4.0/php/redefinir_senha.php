<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI GUARD | Redefinir Senha</title>
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
                <p>Recuperação de Acesso</p>
            </div>

            <div class="login-footer-left">
            </div>
        </div>

        <!-- Lado Direito: Formulário de Redefinição -->
        <div class="login-right">
            <div class="login-right-header" style="margin-bottom: 25px;">
                <h2>Redefinir Senha</h2>
                <p>Confirme seus dados para criar uma nova senha.</p>
            </div>

            <?php if (isset($_GET['erro'])): ?>
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <?php
    if ($_GET['erro'] == 'formato')
        echo "Apenas Gmail ou CPF são permitidos.";
    else if ($_GET['erro'] == 'nao_encontrado')
        echo "Usuário não encontrado.";
    else if ($_GET['erro'] == 'codigo')
        echo "Código de verificação incorreto ou não aprovado.";
    else
        echo "Erro ao redefinir. Tente novamente.";
?>
                </div>
            <?php
endif; ?>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="success-message">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                    Solicitação enviada! Aguarde o código do Admin.
                </div>
            <?php
endif; ?>

            <form method="POST" action="../config/processar_redefinicao.php">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>GMAIL OU CPF</label>
                    <div class="input-wrapper">
                        <input type="text" name="usuario" id="userIdentifier" placeholder="exemplo@gmail.com ou CPF" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>CÓDIGO DE VERIFICAÇÃO (7 DÍGITOS)</label>
                    <div class="input-wrapper">
                        <input type="text" name="codigo" maxlength="7" placeholder="Opcional se for a 1ª solicitação">
                    </div>
                    <small style="color: #64748b; font-size: 11px;">Insira o código apenas se o Super Admin já o tiver fornecido.</small>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>NOVA SENHA</label>
                    <div class="input-wrapper">
                        <input type="password" name="nova_senha" id="passwordInput" placeholder="••••••••">
                        <div class="eye-icon" id="togglePassword">
                            <i data-lucide="eye-off"></i>
                        </div>
                    </div>
                </div>
                <br>
                <button type="submit" class="btn-login" style="margin-bottom: 15px;">
                    REDEFINIR / SOLICITAR <i data-lucide="check"></i>
                </button>

                <div class="bottom-links" style="margin-top: 10px;">
                    <a href="index.php">Lembrou a senha? Faça Login</a>
                </div>
            </form>

            <div class="login-footer-right" style="margin-top: 25px;">
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

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
