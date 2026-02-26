<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    die("ID não informado");
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("
    SELECT imagem 
    FROM evidencias 
    WHERE ocorrencia_id = :id
    LIMIT 1
");

$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$imagem = $stmt->fetch(PDO::FETCH_ASSOC);

if ($imagem && !empty($imagem['imagem'])) {
    header("Content-Type: image/jpeg");
    echo $imagem['imagem'];
} else {
    echo "SEM IMAGEM";
}