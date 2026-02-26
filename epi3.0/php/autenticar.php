<?php
session_start();
require_once "database.php"; 

$usuario = $_POST['usuario'] ?? '';
$senha   = $_POST['senha'] ?? ''; 

if (empty($usuario) || empty($senha)) {
    header("Location: index.php?erro=campos");
    exit;
}

// Busca o usuário pelo nome
$sql = "SELECT id, nome, usuario, senha, cargo 
        FROM usuarios 
        WHERE usuario = :usuario 
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(":usuario", $usuario);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $senha == $user['senha']) {

    // Login Sucesso
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['nome']       = $user['nome'];
    $_SESSION['cargo']      = $user['cargo'];

    header("Location: dashboard.php");
    exit;

} else {
    // Login inválido
    header("Location: index.php?erro=login");
    exit;
}
?>