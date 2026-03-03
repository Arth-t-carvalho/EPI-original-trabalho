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

    <div class="login-card card-transition-enter">
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

            <div id="dynamicError" class="error-message" style="display: none;"></div>
            <?php if (isset($_GET['erro'])): ?>
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <?php 
                        if($_GET['erro'] == 'formato') echo "Apenas Gmail ou CPF são permitidos.";
                        else if($_GET['erro'] == 'nao_encontrado') echo "Usuário não encontrado em nossa base.";
                        else echo "Erro ao redefinir. Tente novamente.";
                    ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="../config/processar_redefinicao.php">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>GMAIL OU CPF</label>
                    <div class="input-wrapper">
                        <input type="text" name="usuario" id="resetUser" placeholder="exemplo@gmail.com ou CPF" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>NOVA SENHA</label>
                    <div class="input-wrapper">
                        <input type="password" name="nova_senha" id="passwordInput" placeholder="••••••••" required>
                        <div class="eye-icon" id="togglePassword">
                            <i data-lucide="eye-off"></i>
                        </div>
                    </div>
                </div>
                <br>
                <button type="submit" class="btn-login" style="margin-bottom: 15px;" onclick="handleReset(event)">
                    REDEFINIR <i data-lucide="check"></i>
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
        async function handleReset(e) {
            e.preventDefault();
            const form = e.target.closest('form');
            const user = document.querySelector('#resetUser').value.trim();
            const pass = document.querySelector('#passwordInput').value;
            const errorDiv = document.querySelector('#dynamicError');

            if (!user || !pass) {
                errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> Preencha todos os campos.`;
                errorDiv.style.display = 'flex';
                lucide.createIcons();
                return;
            }

            const isGmail = user.toLowerCase().endsWith('@gmail.com');
            const isCPF = /^\d{11}$/.test(user.replace(/\D/g, ''));
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

                const response = await fetch('../config/processar_redefinicao.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (result.success) {
                    errorDiv.className = 'success-message';
                    errorDiv.innerHTML = `<i data-lucide="check-circle"></i> Senha alterada com sucesso! Você já pode voltar ao login.`;
                    errorDiv.style.display = 'flex';
                    lucide.createIcons();
                } else {
                    let msg = "Erro ao redefinir.";
                    if(result.message === 'nao_encontrado') msg = "Usuário não encontrado.";
                    else if(result.message === 'campos') msg = "Preencha todos os campos.";
                    
                    errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> ${msg}`;
                    errorDiv.style.display = 'flex';
                    lucide.createIcons();
                }
            } catch (error) {
                console.error("Erro na redefinição:", error);
                errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> Erro de conexão.`;
                errorDiv.style.display = 'flex';
                lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // Lógica Universal: Mostrar/Ocultar Senha
            const togglePasswordIcons = document.querySelectorAll('.eye-icon');
            togglePasswordIcons.forEach(icon => {
                icon.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    const isPassword = type === 'password';
                    this.innerHTML = `<i data-lucide="${isPassword ? 'eye' : 'eye-off'}"></i>`;
                    lucide.createIcons();
                });
            });

            // Transição Premium de Navegação (0.25s)
            const internalLinks = document.querySelectorAll('.bottom-links a');
            internalLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href.endsWith('.php')) {
                        e.preventDefault();
                        const card = document.querySelector('.login-card');
                        const isBack = this.innerText.toLowerCase().includes('voltar');
                        
                        card.classList.add(isBack ? 'card-transition-exit-back' : 'card-transition-exit');
                        setTimeout(() => {
                            window.location.href = href;
                        }, 250);
                    }
                });
            });
        });
    </script>
</body>
</html>
