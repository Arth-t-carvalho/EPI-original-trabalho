<?php
// =================================================================================
// ARQUIVO: apis/controle.api.php (CONVERTIDO PARA MYSQLI - SEM PDO)
// =================================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Filtros e Configurações
$isSuperAdmin = (isset($_SESSION['cargo']) && $_SESSION['cargo'] === 'super_admin');
$cursoIdSession = (isset($_SESSION['usuario_id_curso']) && (int)$_SESSION['usuario_id_curso'] > 0) ? (int)$_SESSION['usuario_id_curso'] : 1;

$filtroCurso = $_GET['curso_id'] ?? '';
$filtroStatus = $_GET['status_filter'] ?? 'all';
$globalView = ($isSuperAdmin && ($filtroCurso === 'todos' || empty($filtroCurso)));

if ($isSuperAdmin && !$globalView) {
    $cursoIdSession = (int)$filtroCurso;
}

// Limpa qualquer saída anterior
if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    /* 
       NOVA LÓGICA DE STATUS "AO VIVO":
       1. RISCO_ATIVO: Se o ÚLTIMO registro de QUALQUER EPI para o aluno for tipo=0 (Infração).
          Isso persiste até que um registro com tipo=1 (Conformidade) seja inserido para aquele EPI.
       2. REINCIDENTE: Se o aluno teve MAIS DE 1 infração (tipo=0) HOJE.
       3. REGULAR: Outros casos.
    */
    
    // Query para buscar o último status de cada EPI por aluno e a contagem de infrações hoje
    $sql = "
        SELECT 
            a.id, 
            a.nome, 
            c.nome AS curso_nome,
            -- Total de infrações hoje (para Reincidência Diária)
            (SELECT COUNT(*) FROM ocorrencias WHERE aluno_id = a.id AND DATE(data_hora) = CURDATE() AND tipo = 0) as infractions_today,
            -- Total histórico de infrações
            (SELECT COUNT(*) FROM ocorrencias WHERE aluno_id = a.id AND tipo = 0) as history_total,
            -- Média de infrações diárias
            (SELECT COALESCE(COUNT(*) / NULLIF(COUNT(DISTINCT DATE(data_hora)), 0), 0) 
             FROM ocorrencias 
             WHERE aluno_id = a.id AND tipo = 0) as daily_avg,
            -- Verifica se há algum EPI cujo último registro é uma infração (tipo=0)
            (SELECT GROUP_CONCAT(e_inner.nome)
             FROM epis e_inner
             WHERE (
                 SELECT o_inner.tipo 
                 FROM ocorrencias o_inner 
                 WHERE o_inner.aluno_id = a.id AND o_inner.epi_id = e_inner.id 
                 ORDER BY o_inner.data_hora DESC LIMIT 1
             ) = 0
            ) as missing_epis_persistento,
            -- Busca a última ocorrência assinada hoje (para o indicador visual)
            (SELECT o3.id 
             FROM ocorrencias o3 
             JOIN acoes_ocorrencia ac3 ON ac3.ocorrencia_id = o3.id
             WHERE o3.aluno_id = a.id AND DATE(o3.data_hora) = CURDATE()
             ORDER BY o3.data_hora DESC LIMIT 1
            ) as last_signed_id,
            (SELECT DATE_FORMAT(ac4.data_hora, '%d/%m/%Y')
             FROM acoes_ocorrencia ac4
             JOIN ocorrencias o4 ON o4.id = ac4.ocorrencia_id
             WHERE o4.aluno_id = a.id AND DATE(o4.data_hora) = CURDATE()
             ORDER BY ac4.data_hora DESC LIMIT 1
            ) as last_signed_date
        FROM alunos a
        LEFT JOIN cursos c ON a.curso_id = c.id
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    // Filtro de Curso
    if (!$globalView) {
        $sql .= " AND a.curso_id = ? ";
        $params[] = $cursoIdSession;
        $types .= "i";
    }

    $finalSql = "SELECT * FROM ($sql) as aluno_data WHERE 1=1 ";
    
    if ($filtroStatus === 'Risk') {
        $finalSql .= " AND missing_epis_persistento IS NOT NULL ";
    } elseif ($filtroStatus === 'Recurrent') {
        $finalSql .= " AND infractions_today > 1 AND missing_epis_persistento IS NULL ";
    } elseif ($filtroStatus === 'Safe') {
        $finalSql .= " AND missing_epis_persistento IS NULL AND infractions_today <= 1 ";
    }

    $stmt = $conn->prepare($finalSql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $res = $stmt->get_result();
    
    $resultado = [];
    while ($row = $res->fetch_assoc()) {
        // Determinação do status final determinístico
        $status = 'Safe';
        if ($row['missing_epis_persistento']) {
            $status = 'Risk';
        } elseif ($row['infractions_today'] > 1) {
            $status = 'Recurrent';
        }

        $resultado[] = [
            'id'            => (int)$row['id'],
            'name'          => $row['nome'],
            'course'        => $row['curso_nome'] ?? 'Sem Curso',
            'missing'       => $row['missing_epis_persistento'] ? explode(',', $row['missing_epis_persistento']) : [],
            'history_count' => (int)$row['history_total'],
            'infractions_today' => (int)$row['infractions_today'],
            'daily_avg'     => (float)$row['daily_avg'],
            'status'        => $status,
            'signed_id'     => $row['last_signed_id'] ? (int)$row['last_signed_id'] : null,
            'signed_date'   => $row['last_signed_date'] ?: null
        ];
    }

    echo json_encode($resultado);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>