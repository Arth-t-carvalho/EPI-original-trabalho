<?php
session_start();

// Detecta se é requisição AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Timeout de sessão
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > 1800)) {

    session_unset();
    session_destroy();

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'session_expired']);
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

// Verifica login
if (!isset($_SESSION['usuario_id'])) {

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'not_logged']);
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

// Atualiza atividade
$_SESSION['last_activity'] = time();

// Segurança
session_regenerate_id(true);