<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

try {
    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    $cursoId = 1;

    // Se for a primeira carga (init), apenas pegamos o último ID do banco
    // para não carregar o histórico inteiro como notificação.
    if ($last_id === 0) {
        $stmt = $pdo->prepare("SELECT MAX(o.id) as max_id FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ?");
        $stmt->execute([$cursoId]);
        $maxId = (int) $stmt->fetchColumn();
        
        echo json_encode([
            'status' => 'init',
            'last_id' => $maxId
        ]);
        exit;
    }

    // Se não for init, buscamos TODAS as ocorrências MAIORES que o last_id
    $query = "SELECT o.id, a.nome AS aluno,
                     CASE o.epi_id
                        WHEN 1 THEN 'oculos'
                        WHEN 2 THEN 'capacete'
                        ELSE 'epi não identificado'
                     END AS epi_nome
              FROM ocorrencias o
              JOIN alunos a ON a.id = o.aluno_id
              WHERE a.curso_id = ? AND o.id > ?
              ORDER BY o.id ASC"; // ASC para mostrar na ordem cronológica correta

    $stmt = $pdo->prepare($query);
    $stmt->execute([$cursoId, $last_id]);
    
    // FETCH_ALL é a chave aqui! Ele garante que o retorno seja um Array, compatível com o forEach do JS
    $novasOcorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($novasOcorrencias) > 0) {
        echo json_encode([
            'status' => 'success',
            'dados' => $novasOcorrencias
        ]);
    } else {
        echo json_encode([
            'status' => 'no_new'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
exit;