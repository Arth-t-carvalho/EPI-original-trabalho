<?php
// =================================================================================
// ARQUIVO: apis/api.php (CORRIGIDO PARA MYSQLI - SEM PDO)
// =================================================================================

require_once __DIR__ . '/../config/database.php';

// Limpa buffer para evitar erros de JSON
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    $action = $_GET['action'] ?? '';
    $year   = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $month  = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
    $date   = $_GET['date'] ?? date('Y-m-d');

    // Função auxiliar para formatar array de meses (agora usando mysqli)
    function formatMonthArray($result) {
        $arr = array_fill(0, 12, 0); 
        while ($r = mysqli_fetch_assoc($result)) {
            $idx = (int)$r['mes'] - 1;
            if ($idx >= 0 && $idx < 12) {
                $arr[$idx] = (int)$r['qtd'];
            }
        }
        return $arr;
    }

    // ---------------------------------------------------------
    // 1. GRÁFICOS (BARRAS E ROSCA)
    // ---------------------------------------------------------
    if ($action === 'charts') {
        
        // A) Barras - Capacete (ID 2)
        $sql = "SELECT MONTH(data_hora) as mes, COUNT(*) as qtd FROM ocorrencias WHERE epi_id = 2 AND YEAR(data_hora) = ? GROUP BY mes";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $year);
        mysqli_stmt_execute($stmt);
        $capaceteArr = formatMonthArray(mysqli_stmt_get_result($stmt));

        // B) Barras - Óculos (ID 1)
        $sql = "SELECT MONTH(data_hora) as mes, COUNT(*) as qtd FROM ocorrencias WHERE epi_id = 1 AND YEAR(data_hora) = ? GROUP BY mes";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $year);
        mysqli_stmt_execute($stmt);
        $oculosArr = formatMonthArray(mysqli_stmt_get_result($stmt));

        // C) Total Geral
        $sql = "SELECT MONTH(data_hora) as mes, COUNT(*) as qtd FROM ocorrencias WHERE YEAR(data_hora) = ? GROUP BY mes";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $year);
        mysqli_stmt_execute($stmt);
        $totalArr = formatMonthArray(mysqli_stmt_get_result($stmt));

        // D) Rosca - Por Tipo de EPI
        $sql = "SELECT e.nome, COUNT(*) as qtd FROM ocorrencias o JOIN epis e ON e.id = o.epi_id WHERE YEAR(o.data_hora) = ? GROUP BY e.nome";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $year);
        mysqli_stmt_execute($stmt);
        $resDoughnut = mysqli_stmt_get_result($stmt);

        $labels = [];
        $dataDoughnut = [];
        while ($d = mysqli_fetch_assoc($resDoughnut)) {
            $labels[] = $d['nome'];
            $dataDoughnut[] = (int)$d['qtd'];
        }

        echo json_encode([
            'bar' => ['capacete' => $capaceteArr, 'oculos' => $oculosArr, 'total' => $totalArr],
            'doughnut' => ['labels' => $labels, 'data' => $dataDoughnut]
        ]);
        exit;
    }

    // ---------------------------------------------------------
    // 2. CALENDÁRIO
    // ---------------------------------------------------------
    if ($action === 'calendar') {
        $sql = "SELECT o.data_hora as full_date, a.nome AS name, e.nome AS `desc`, DATE_FORMAT(o.data_hora, '%H:%i') AS time
                FROM ocorrencias o
                LEFT JOIN alunos a ON o.aluno_id = a.id
                LEFT JOIN epis e ON e.id = o.epi_id
                WHERE MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ?
                ORDER BY o.data_hora ASC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $month, $year);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        exit;
    }

    // ---------------------------------------------------------
    // 3. MODAL (DETALHES)
    // ---------------------------------------------------------
    if ($action === 'modal_details') {
        $mesSQL = ($month == 0) ? 1 : $month;
        $sql = "SELECT DATE_FORMAT(o.data_hora, '%d/%m/%Y') AS data, a.nome AS aluno, c.nome AS curso,
                       COALESCE(e.nome, 'Não informado') AS epis, DATE_FORMAT(o.data_hora, '%H:%i') AS hora,
                       CASE WHEN ac.id IS NOT NULL THEN 'Resolvido' ELSE 'Pendente' END AS status_formatado
                FROM ocorrencias o
                JOIN alunos a ON a.id = o.aluno_id
                LEFT JOIN cursos c ON c.id = a.curso_id
                LEFT JOIN epis e ON e.id = o.epi_id
                LEFT JOIN acoes_ocorrencia ac ON ac.ocorrencia_id = o.id
                WHERE MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ?
                GROUP BY o.id
                ORDER BY o.data_hora DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $mesSQL, $year);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        exit;
    }

    // ---------------------------------------------------------
    // 4. LISTA DE ALUNOS (EPIs Faltando / Risco)
    // ---------------------------------------------------------
    if (empty($action)) {
        $sqlAlunos = "SELECT a.id, a.nome, c.nome as curso_nome FROM alunos a LEFT JOIN cursos c ON c.id = a.curso_id";
        $resAlunos = mysqli_query($conn, $sqlAlunos);
        $resultado = [];

        while ($aluno = mysqli_fetch_assoc($resAlunos)) {
            // Risco Hoje
            $sqlRisco = "SELECT COUNT(*) as total FROM ocorrencias WHERE aluno_id = ? AND DATE(data_hora) = CURDATE()";
            $stmtRisco = mysqli_prepare($conn, $sqlRisco);
            mysqli_stmt_bind_param($stmtRisco, "i", $aluno['id']);
            mysqli_stmt_execute($stmtRisco);
            $temRisco = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtRisco))['total'] > 0;

            // Histórico
            $sqlHist = "SELECT COUNT(*) as total FROM ocorrencias WHERE aluno_id = ?";
            $stmtHist = mysqli_prepare($conn, $sqlHist);
            mysqli_stmt_bind_param($stmtHist, "i", $aluno['id']);
            mysqli_stmt_execute($stmtHist);
            $temHistorico = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtHist))['total'] > 0;

            // EPIs faltando hoje
            $missing = [];
            if ($temRisco) {
                $sqlEpi = "SELECT e.nome FROM ocorrencias o JOIN epis e ON e.id = o.epi_id WHERE o.aluno_id = ? AND DATE(o.data_hora) = CURDATE()";
                $stmtEpi = mysqli_prepare($conn, $sqlEpi);
                mysqli_stmt_bind_param($stmtEpi, "i", $aluno['id']);
                mysqli_stmt_execute($stmtEpi);
                $resEpi = mysqli_stmt_get_result($stmtEpi);
                while($rowEpi = mysqli_fetch_assoc($resEpi)) {
                    $missing[] = $rowEpi['nome'];
                }
            }

            $resultado[] = [
                'id'      => $aluno['id'],
                'name'    => $aluno['nome'],
                'course'  => $aluno['curso_nome'],
                'missing' => $missing,
                'history' => $temHistorico
            ];
        }
        echo json_encode($resultado);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}