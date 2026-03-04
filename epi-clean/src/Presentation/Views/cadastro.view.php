<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI GUARD | Cadastro</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="css/login_novo.css">
</head>

<body>
    <div class="login-card">
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
        </div>

        <div class="login-right">
            <?php if (isset($_GET['erro'])): ?>
                <div class="error-message" style="color: red; margin-bottom: 10px;">
                    Erro: <?= htmlspecialchars($_GET['erro']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="success-message" style="color: green; margin-bottom: 10px;">
                    Conta criada com sucesso! Faça login.
                </div>
            <?php endif; ?>

            <div class="login-right-header">
                <h2>Cadastrar</h2>
                <p>Insira as suas credenciais administrativas.</p>
            </div>

            <form method="POST" action="index.php?route=register-process">
                <div class="form-group">
                    <label>NOME COMPLETO</label>
                    <div class="input-wrapper">
                        <input type="text" name="nome" placeholder="Ex: Arthur Silva" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>GMAIL OU CPF</label>
                    <div class="input-wrapper">
                        <input type="text" name="usuario" placeholder="exemplo@gmail.com ou CPF" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>SENHA</label>
                    <div class="input-wrapper">
                        <input type="password" name="senha" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    CRIAR CONTA
                </button>

                <div class="bottom-links">
                    <a href="index.php?route=login">Já tem conta? Faça Login</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>

