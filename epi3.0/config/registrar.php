<?php
require_once 'database.php';

$nome = $_POST['nome'] ?? '';
$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';
$cargo = $_POST['cargo'] ?? 'instrutor';
$id_curso = 1; // Padrão Inicial

if (empty($nome) || empty($usuario) || empty($senha)) {
    header("Location: ../php/cadastro.php?erro=campos");
    exit;
}

// Verifica se usuário já existe
$checkSql = "SELECT id FROM usuarios WHERE usuario = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($stmtCheck, "s", $usuario);
mysqli_stmt_execute($stmtCheck);
$resCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resCheck) > 0) {
    header("Location: ../php/cadastro.php?erro=existe");
    exit;
}

// Insere novo usuário
$sql = "INSERT INTO usuarios (nome, usuario, senha, cargo, id_curso) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssi", $nome, $usuario, $senha, $cargo, $id_curso);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../php/cadastro.php?sucesso=1");
    exit;
}
else {
    header("Location: ../php/cadastro.php?erro=db");
    exit;
}
?>
