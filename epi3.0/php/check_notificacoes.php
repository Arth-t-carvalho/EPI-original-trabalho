<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/auth.php';
// Supondo que dentro de database.php agora você tenha uma conexão MySQLi na variável $conn
require_once __DIR__ . '/../config/database.php'; 

try {
    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    $cursoId = 1;

    // Se for a primeira carga (init)
    if ($last_id === 0) {
        $stmt = $conn->prepare("SELECT MAX(o.id) as max_id FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ?");
        $stmt->bind_param("i", $cursoId); // "i" para integer
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $maxId = (int) ($row['max_id'] ?? 0);
        
        echo json_encode([
            'status' => 'init',
            'last_id' => $maxId
        ]);
        exit;
    }

    // Busca ocorrências MAIORES que o last_id
    $query = "SELECT o.id, a.nome AS aluno,
                     CASE o.epi_id
                        WHEN 1 THEN 'oculos'
                        WHEN 2 THEN 'capacete'
                        ELSE 'epi não identificado'
                     END AS epi_nome
              FROM ocorrencias o
              JOIN alunos a ON a.id = o.aluno_id
              WHERE a.curso_id = ? AND o.id > ?
              ORDER BY o.id ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $cursoId, $last_id); // dois inteiros
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Transforma o resultado em um Array (equivalente ao fetchAll do PDO)
    $novasOcorrencias = [];
    while ($row = $result->fetch_assoc()) {
        $novasOcorrencias[] = $row;
    }

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