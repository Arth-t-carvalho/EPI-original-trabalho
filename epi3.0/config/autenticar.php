<?php
session_start();
require_once 'database.php';

// Função para responder JSON em AJAX ou Redirecionar em Normal
function enviarResposta($sucesso, $msg, $isAjax) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $sucesso, 
            'message' => $msg, 
            'redirect' => '../php/dashboard.php'
        ]);
        exit;
    } else {
        if ($sucesso) {
            header("Location: ../php/dashboard.php");
        } else {
            header("Location: ../php/index.php?erro=" . $msg);
        }
        exit;
    }
}

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']);

$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';

// Validação básica
if (empty($usuario) || empty($senha)) {
    enviarResposta(false, 'campos', $isAjax);
}

// Validação de Gmail ou CPF
$isGmail = str_ends_with(strtolower($usuario), '@gmail.com');
$isCPF = preg_match('/^\d{11}$/', preg_replace('/\D/', '', $usuario));

if (!$isGmail && !$isCPF) {
    enviarResposta(false, 'formato', $isAjax);
}

// Busca o usuário no banco usando MySQLi
$sql = "SELECT id, nome, usuario, senha, cargo 
        FROM usuarios 
        WHERE usuario = ? 
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $usuario);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Comparação de senha (considerando texto puro conforme o estado atual do banco)
if ($user && $senha == $user['senha']) {
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['nome'] = $user['nome'];
    $_SESSION['cargo'] = $user['cargo'];
    
    enviarResposta(true, '', $isAjax);
} else {
    enviarResposta(false, 'login', $isAjax);
}
?>