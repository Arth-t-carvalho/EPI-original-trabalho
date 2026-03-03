<?php
require_once 'database.php';

$nome = $_POST['nome'] ?? '';
$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($nome) || empty($usuario) || empty($senha)) {
    header("Location: ../php/cadastro.php?erro=campos");
    exit;
}

// 1. Verifica se usuário está pré-autorizado (existe no banco mas com senha vazia)
$checkSql = "SELECT id, senha FROM usuarios WHERE usuario = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($stmtCheck, "s", $usuario);
mysqli_stmt_execute($stmtCheck);
$resCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resCheck) === 0) {
    // Não existe no banco = Não autorizado pelo admin
    header("Location: ../php/cadastro.php?erro=nao_autorizado");
    exit;
}

$userRow = mysqli_fetch_assoc($resCheck);

if (!empty($userRow['senha'])) {
    // Já possui senha = Já está cadastrado
    header("Location: ../php/cadastro.php?erro=existe");
    exit;
}

// 2. Finaliza o cadastro (Ativação)
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$sql = "UPDATE usuarios SET nome = ?, senha = ? WHERE usuario = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $nome, $senhaHash, $usuario);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../php/cadastro.php?sucesso=1");
    exit;
} else {
    header("Location: ../php/cadastro.php?erro=db");
    exit;
}
?>
