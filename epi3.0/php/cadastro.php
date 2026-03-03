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
                <p>Cadastro de Usuário</p>
            </div>

            <div class="login-footer-left">
            </div>
        </div>

        <!-- Lado Direito: Formulário de Cadastro -->
        <div class="login-right">

            <div id="dynamicError" class="error-message" style="display: none;"></div>
            <?php if (isset($_GET['erro'])): ?>
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <?php 
                        if($_GET['erro'] == 'formato') echo "Apenas Gmail ou CPF são permitidos.";
                        else if($_GET['erro'] == 'existe') echo "Este usuário já está cadastrado.";
                        else echo "Erro ao cadastrar. Tente novamente.";
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="success-message">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                    Conta criada com sucesso!
                </div>
            <?php endif; ?>
            <div class="login-right-header">
                <h2>Cadastrar</h2>
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
                        <div class="eye-icon">
                            <i data-lucide="eye-off"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>CONFIRMAR SENHA</label>
                    <div class="input-wrapper">
                        <input type="password" name="confirmar_senha" id="confirmPasswordInput" placeholder="••••••••" required>
                        <div class="eye-icon">
                            <i data-lucide="eye-off"></i>
                        </div>
                    </div>
                </div>

                <br>
                <button type="submit" class="btn-login" style="margin-bottom: 15px;" onclick="handleCadastro(event)">
                    CRIAR CONTA <i data-lucide="chevron-right"></i>
                </button>

                <div class="bottom-links" style="margin-top: 10px;">
                    <a href="index.php">Já tem conta? Faça Login</a>
                </div>
            </form>

          
        </div>
    </div>

    <!-- Overlay de Transição Premium SENAI -->
    <div id="transitionOverlay" class="transition-overlay">
        <div class="transition-content">
            <div class="transition-logo">SENAI</div>
            <div class="transition-text">CADASTRANDO NO SISTEMA</div>
            <div class="progress-container">
                <div id="progressFill" class="progress-fill"></div>
            </div>
            <div class="transition-footer">EPI GUARD © 2026</div>
        </div>
    </div>

    <script>
        async function handleCadastro(e) {
            e.preventDefault();
            const form = e.target.closest('form');
            const nome = document.querySelector('input[name="nome"]').value.trim();
            const user = document.querySelector('#registerUser').value.trim();
            const pass = document.querySelector('#passwordInput').value;
            const confirmPass = document.querySelector('#confirmPasswordInput').value;
            const errorDiv = document.querySelector('#dynamicError');

            if (!nome || !user || !pass || !confirmPass) {
                errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> Preencha todos os campos.`;
                errorDiv.style.display = 'flex';
                lucide.createIcons();
                return;
            }

            if (pass !== confirmPass) {
                errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> As senhas não coincidem.`;
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

                const response = await fetch('../config/registrar.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (result.success) {
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
                                    window.location.href = '../php/index.php?sucesso=cadastrado';
                                }, 700);
                            }, 1000); 
                        }, 800); 
                    }, 50);
                } else {
                    let msg = "Erro ao cadastrar.";
                    if(result.message === 'existe') msg = "Este usuário já está cadastrado.";
                    else if(result.message === 'formato') msg = "Apenas Gmail ou CPF são permitidos.";
                    
                    errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> ${msg}`;
                    errorDiv.style.display = 'flex';
                    lucide.createIcons();
                }
            } catch (error) {
                console.error("Erro no cadastro:", error);
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
