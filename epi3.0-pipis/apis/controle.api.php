<?php
// =================================================================================
// ARQUIVO: apis/controle.api.php
// =================================================================================

require_once __DIR__ . '/../config/database.php';

// Limpa qualquer saída anterior (espaços em branco, erros) para não quebrar o JSON
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');
// Desativa exibição de erros no corpo da resposta (eles vão para o log do servidor)
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // 1. BUSCAR ALUNOS COM O NOME DO CURSO
    $sql = "
        SELECT 
            a.id, 
            a.nome, 
            c.nome AS curso_nome
        FROM alunos a
        LEFT JOIN cursos c ON a.curso_id = c.id
        ORDER BY a.nome ASC
    ";
    $stmt = $pdo->query($sql);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [];

    foreach ($alunos as $aluno) {
        $id = $aluno['id'];

        // 2. CONTA O TOTAL DE OCORRÊNCIAS (Histórico Geral)
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM ocorrencias WHERE aluno_id = ?");
        $stmtCount->execute([$id]);
        $totalOcorrencias = $stmtCount->fetchColumn();

        // 3. VERIFICA SE FALTOU EPI HOJE (Risco Ativo)
        $stmtEpi = $pdo->prepare("
            SELECT e.nome 
            FROM ocorrencias o 
            JOIN epis e ON e.id = o.epi_id 
            WHERE o.aluno_id = ? AND DATE(o.data_hora) = CURDATE()
        ");
        $stmtEpi->execute([$id]);
        $episFaltantesHoje = $stmtEpi->fetchAll(PDO::FETCH_COLUMN);

        $resultado[] = [
            'id'            => $aluno['id'],
            'name'          => $aluno['nome'],
            'course'        => $aluno['curso_nome'] ?? 'Sem Curso', // Pega o nome real do curso
            'missing'       => $episFaltantesHoje,     // Array: ['Capacete', 'Óculos'] ou []
            'history_count' => (int)$totalOcorrencias  // Número: 0, 1, 5...
        ];
    }

    echo json_encode($resultado);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>