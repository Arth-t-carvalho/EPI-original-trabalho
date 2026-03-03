<?php
// config/processar_redefinicao.php
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = mysqli_real_escape_string($conn, $_POST['usuario']);
    $codigo = isset($_POST['codigo']) ? mysqli_real_escape_string($conn, $_POST['codigo']) : '';
    $nova_senha = isset($_POST['nova_senha']) ? $_POST['nova_senha'] : '';

    // 1. Verificar se o usuário existe
    $sql_user = "SELECT id FROM usuarios WHERE usuario = '$usuario'";
    $res_user = mysqli_query($conn, $sql_user);

    if (mysqli_num_rows($res_user) === 0) {
        header("Location: ../php/redefinir_senha.php?erro=nao_encontrado");
        exit;
    }

    $user_data = mysqli_fetch_assoc($res_user);
    $usuario_id = $user_data['id'];

    if (empty($codigo)) {
        // --- STAGE 1: SOLICITAR REDEFINIÇÃO ---
        // Se houver uma solicitação pendente, não cria outra
        $sql_check = "SELECT id FROM solicitacoes_redefinicao WHERE usuario_id = $usuario_id AND status = 'pendente'";
        if (mysqli_num_rows(mysqli_query($conn, $sql_check)) == 0) {
            $sql_insert = "INSERT INTO solicitacoes_redefinicao (usuario_id, status) VALUES ($usuario_id, 'pendente')";
            mysqli_query($conn, $sql_insert);
        }
        header("Location: ../php/redefinir_senha.php?sucesso=1");
        exit;
    }
    else {
        // --- STAGE 2: PROCESSAR COM CÓDIGO ---
        if (empty($nova_senha)) {
            header("Location: ../php/redefinir_senha.php?erro=senha_vazia");
            exit;
        }

        // Verifica se o código bate e está aprovado
        $sql_verif = "SELECT id FROM solicitacoes_redefinicao 
                      WHERE usuario_id = $usuario_id 
                      AND codigo_verificacao = '$codigo' 
                      AND status = 'aprovado'";
        $res_verif = mysqli_query($conn, $sql_verif);

        if (mysqli_num_rows($res_verif) > 0) {
            $solicitacao = mysqli_fetch_assoc($res_verif);
            $solicitacao_id = $solicitacao['id'];

            // Atualiza a senha do usuário
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql_update_user = "UPDATE usuarios SET senha = '$senha_hash' WHERE id = $usuario_id";

            if (mysqli_query($conn, $sql_update_user)) {
                // Marca solicitação como usada
                $sql_finalize = "UPDATE solicitacoes_redefinicao SET status = 'usado' WHERE id = $solicitacao_id";
                mysqli_query($conn, $sql_finalize);

                header("Location: ../php/index.php?reset=sucesso");
            }
            else {
                header("Location: ../php/redefinir_senha.php?erro=db");
            }
        }
        else {
            header("Location: ../php/redefinir_senha.php?erro=codigo");
        }
        exit;
    }
}
?>
