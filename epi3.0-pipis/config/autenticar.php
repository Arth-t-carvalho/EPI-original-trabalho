<?php
session_start();
require_once 'database.php';

$usuario = $_POST['usuario'] ?? '';
$senha   = $_POST['senha'] ?? '';

// Validação básica
if (empty($usuario) || empty($senha)) {
    header("Location: ../php/index.php?erro=campos");
    exit;
}

// Busca o usuário no banco
$sql = "SELECT id, nome, usuario, senha, cargo
        FROM usuarios
        WHERE usuario = :usuario
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':usuario', $usuario);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// --- AQUI ESTÁ A MUDANÇA ---
// Antes: password_verify($senha, $user['senha'])
// Agora: $senha == $user['senha'] (Compara se é igualzinho)

if ($user && $senha == $user['senha']) {

    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['nome']       = $user['nome'];
    $_SESSION['cargo']      = $user['cargo'];

    // Caminho corrigido para a pasta php onde está a dashboard
    header("Location: ../php/dashboard.php");
    exit;

} else {
    // Caminho corrigido para voltar pro login
    header("Location: ../php/index.php?erro=login");
    exit;
}