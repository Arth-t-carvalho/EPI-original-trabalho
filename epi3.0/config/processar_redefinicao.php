<?php
require_once 'database.php';

$usuario = $_POST['usuario'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']);

function enviarResposta($sucesso, $msg, $isAjax) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $sucesso, 'message' => $msg, 'redirect' => 'index.php']);
        exit;
    } else {
        if ($sucesso) {
            header("Location: ../php/index.php?sucesso=redefinido");
        } else {
            header("Location: ../php/redefinir_senha.php?erro=" . $msg);
        }
        exit;
    }
}

if (empty($usuario) || empty($nova_senha)) {
    enviarResposta(false, 'campos', $isAjax);
}

// Validação de Gmail ou CPF
$isGmail = str_ends_with(strtolower($usuario), '@gmail.com');
$isCPF = preg_match('/^\d{11}$/', preg_replace('/\D/', '', $usuario));

if (!$isGmail && !$isCPF) {
    enviarResposta(false, 'formato', $isAjax);
}

// Verifica e atualiza
$checkSql = "SELECT id FROM usuarios WHERE usuario = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($stmtCheck, "s", $usuario);
mysqli_stmt_execute($stmtCheck);
if (mysqli_num_rows(mysqli_stmt_get_result($stmtCheck)) === 0) {
    enviarResposta(false, 'nao_encontrado', $isAjax);
}

$updateSql = "UPDATE usuarios SET senha = ? WHERE usuario = ?";
$stmtUpdate = mysqli_prepare($conn, $updateSql);
mysqli_stmt_bind_param($stmtUpdate, "ss", $nova_senha, $usuario);

if (mysqli_stmt_execute($stmtUpdate)) {
    enviarResposta(true, 'sucesso', $isAjax);
} else {
    enviarResposta(false, 'db', $isAjax);
}
?>
