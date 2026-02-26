<?php
// config/database.php

$host = "localhost";
$db   = "epi_guard";
$user = "root";
$pass = ""; // Coloque sua senha aqui se houver
$port = "3308";
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4;port=$port";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Em produção, não mostre a mensagem detalhada do erro para o usuário
    error_log($e->getMessage());
    die("Erro interno de conexão com o banco de dados.");
}
?>