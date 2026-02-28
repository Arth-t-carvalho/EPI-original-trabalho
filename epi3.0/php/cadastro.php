<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro - EPI Guard</title>
    <link rel="stylesheet" href="../css/index.css">
</head>

<body>

    <div class="login-container" id="loginContainer">
        <div class="login-box">
            <h1>EPI Guard</h1>
            <p>Crie sua conta de monitoramento</p>

            <form method="POST" action="../config/registrar.php">
                <div class="input-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" required placeholder="Ex: Arthur Silva">
                </div>

                <div class="input-group">
                    <label>Usuário (Login)</label>
                    <input type="text" name="usuario" required placeholder="Ex: arthur.silva">
                </div>

                <div class="input-group">
                    <label>Senha</label>
                    <input type="password" name="senha" required placeholder="••••••••">
                </div>

                <div class="input-group">
                    <label>Cargo</label>
                    <select name="cargo" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: white;">
                        <option value="supervisor">Supervisor</option>
                        <option value="instrutor">Instrutor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <button type="submit">Criar Conta</button>
                
                <div style="text-align: center; margin-top: 15px;">
                    <a href="index.php" style="color: #666; text-decoration: none; font-size: 14px;">Já tem conta? <span style="color: #E30613; font-weight: bold;">Faça Login</span></a>
                </div>

                <?php if (isset($_GET['erro'])): ?>
                    <div class="erro">Erro ao cadastrar. Tente outro usuário.</div>
                <?php
endif; ?>
                <?php if (isset($_GET['sucesso'])): ?>
                    <div class="sucesso" style="color: green; background: #e6fffa; padding: 10px; border-radius: 8px; margin-top: 10px; text-align: center; border: 1px solid #38b2ac;">Conta criada com sucesso!</div>
                <?php
endif; ?>
            </form>
        </div>
    </div>

</body>

</html>
