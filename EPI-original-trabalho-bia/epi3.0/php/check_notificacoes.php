<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

try {
    // Pega o last_id enviado pelo JavaScript (padrão é 0 na primeira vez)
    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : -1;

    // Se for a primeira carga (init), apenas pegamos o último ID do banco
    if ($last_id === 0) {
        // MUDANÇA: Usando o $conn do MySQLi em vez de $pdo
        $result = $conn->query("SELECT MAX(id) AS max_id FROM ocorrencias");
        $row = $result->fetch_assoc();
        $maxId = (int) $row['max_id'];
        
        echo json_encode([
            'status' => 'init',
            // Se o banco estiver vazio, manda -1 para não ficar preso num loop
            'last_id' => $maxId > 0 ? $maxId : -1 
        ]);
        exit;
    }

    // Se o banco estava vazio no init, ajustamos para 0 para buscar as novas
    if ($last_id === -1) {
        $last_id = 0;
    }

    // Buscamos as ocorrências novas
    $query = "SELECT o.id, a.nome AS aluno, o.data_hora,
                     CASE o.epi_id
                        WHEN 1 THEN 'Óculos de Proteção'
                        WHEN 2 THEN 'Capacete de Segurança'
                        ELSE 'EPI não identificado'
                     END AS epi_nome
              FROM ocorrencias o
              LEFT JOIN alunos a ON a.id = o.aluno_id
              WHERE o.id > ?
              ORDER BY o.id ASC";

    // MUDANÇA: Usando o prepare e bind_param do MySQLi
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $last_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $novasOcorrencias = [];
    if ($result) {
        $novasOcorrencias = $result->fetch_all(MYSQLI_ASSOC);
    }

    if (count($novasOcorrencias) > 0) {
        echo json_encode([
            'status' => 'success',
            'dados' => $novasOcorrencias
        ]);
    } else {
        echo json_encode(['status' => 'empty']);
    }

} catch (Exception $e) {
    // Retorna o erro em formato JSON para o JavaScript não quebrar
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar notificacoes']);
}
?>