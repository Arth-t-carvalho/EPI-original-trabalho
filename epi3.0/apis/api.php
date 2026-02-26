<?php
// =================================================================================
// ARQUIVO: apis/api.php (CORRIGIDO PARA NOVA ESTRUTURA DO BANCO)
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

    // ---------------------------------------------------------
    // 1. GRÁFICOS (BARRAS E ROSCA)
    // ---------------------------------------------------------
    if ($action === 'charts') {
        
        function formatMonthArray($data) {
            $arr = array_fill(0, 12, 0); 
            foreach ($data as $r) {
                $idx = (int)$r['mes'] - 1;
                if ($idx >= 0 && $idx < 12) {
                    $arr[$idx] = (int)$r['qtd'];
                }
            }
            return $arr;
        }

        // A) Barras - Capacete (ID 2 - Exemplo, ajuste se o ID mudou na tabela EPIs)
        // Nota: Assumindo que epi_id 2 é Capacete. Se não for, ajuste o ID.
        $stmt = $pdo->prepare("SELECT MONTH(data_hora) as mes, COUNT(*) as qtd FROM ocorrencias WHERE epi_id = 2 AND YEAR(data_hora) = :ano GROUP BY mes");
        $stmt->execute(['ano' => $year]);
        $capaceteArr = formatMonthArray($stmt->fetchAll(PDO::FETCH_ASSOC));

        // B) Barras - Óculos (ID 1 - Exemplo)
        $stmt = $pdo->prepare("SELECT MONTH(data_hora) as mes, COUNT(*) as qtd FROM ocorrencias WHERE epi_id = 1 AND YEAR(data_hora) = :ano GROUP BY mes");
        $stmt->execute(['ano' => $year]);
        $oculosArr = formatMonthArray($stmt->fetchAll(PDO::FETCH_ASSOC));

        // C) Total Geral
        $stmt = $pdo->prepare("SELECT MONTH(data_hora) as mes, COUNT(*) as qtd FROM ocorrencias WHERE YEAR(data_hora) = :ano GROUP BY mes");
        $stmt->execute(['ano' => $year]);
        $totalArr = formatMonthArray($stmt->fetchAll(PDO::FETCH_ASSOC));

        // D) Rosca - Por Tipo de EPI
        $stmt = $pdo->prepare("
            SELECT e.nome, COUNT(*) as qtd 
            FROM ocorrencias o 
            JOIN epis e ON e.id = o.epi_id 
            WHERE YEAR(o.data_hora) = :ano 
            GROUP BY e.nome
        ");
        $stmt->execute(['ano' => $year]);
        $doughnutData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = [];
        $dataDoughnut = [];
        foreach ($doughnutData as $d) {
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
// ---------------------------------------------------------
// 2. CALENDÁRIO (CORRIGIDO)
// ---------------------------------------------------------
if ($action === 'calendar') {
    // Buscamos pelo Mês e Ano para popular o JS com dados suficientes
    // Se quiser carregar o ano todo para garantir a navegação entre meses, remova o filtro do mês.
    $sql = "
        SELECT 
            o.data_hora as full_date, -- Importante para o JS saber o dia exato
            a.nome AS name, 
            e.nome AS `desc`, 
            DATE_FORMAT(o.data_hora, '%H:%i') AS time
        FROM ocorrencias o
        LEFT JOIN alunos a ON o.aluno_id = a.id
        LEFT JOIN epis e ON e.id = o.epi_id
        WHERE MONTH(o.data_hora) = :mes 
          AND YEAR(o.data_hora) = :ano
        ORDER BY o.data_hora ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    // Usa as variáveis $month e $year que já foram definidas no topo do seu arquivo
    $stmt->execute(['mes' => $month, 'ano' => $year]);
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
    // ---------------------------------------------------------
    // 3. MODAL (CORRIGIDO: REMOVIDO STATUS, ADICIONADO JOIN ACOES)
    // ---------------------------------------------------------
    if ($action === 'modal_details') {
        $mesSQL = ($month == 0) ? 1 : $month;

        // Logica: Se existe registro na tabela 'acoes_ocorrencia', consideramos 'Resolvido'.
        // Se não existe, é 'Pendente'.
        // Também adicionei JOIN com CURSOS para pegar o nome do curso corretamente.
        $sql = "
            SELECT 
                DATE_FORMAT(o.data_hora, '%d/%m/%Y') AS data,
                a.nome AS aluno,
                c.nome AS curso,
                COALESCE(e.nome, 'Não informado') AS epis,
                DATE_FORMAT(o.data_hora, '%H:%i') AS hora,
                CASE 
                    WHEN ac.id IS NOT NULL THEN 'Resolvido'
                    ELSE 'Pendente'
                END AS status_formatado
            FROM ocorrencias o
            JOIN alunos a ON a.id = o.aluno_id
            LEFT JOIN cursos c ON c.id = a.curso_id
            LEFT JOIN epis e ON e.id = o.epi_id
            LEFT JOIN acoes_ocorrencia ac ON ac.ocorrencia_id = o.id
            WHERE MONTH(o.data_hora) = :mes AND YEAR(o.data_hora) = :ano
            GROUP BY o.id
            ORDER BY o.data_hora DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['mes' => $mesSQL, 'ano' => $year]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // ---------------------------------------------------------
    // 4. LISTA DE ALUNOS (CORRIGIDO: JOIN COM CURSOS)
    // ---------------------------------------------------------
    if (empty($action)) {
        // Agora precisamos pegar o nome do curso na tabela cursos
        $sql = "
            SELECT a.id, a.nome, c.nome as curso_nome 
            FROM alunos a
            LEFT JOIN cursos c ON c.id = a.curso_id
        ";
        $stmt = $pdo->query($sql);
        $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];

        foreach ($alunos as $aluno) {
            // Risco Hoje
            $stmtRisco = $pdo->prepare("SELECT COUNT(*) FROM ocorrencias WHERE aluno_id = ? AND DATE(data_hora) = CURDATE()");
            $stmtRisco->execute([$aluno['id']]);
            $temRisco = $stmtRisco->fetchColumn() > 0;

            // Histórico
            $stmtHist = $pdo->prepare("SELECT COUNT(*) FROM ocorrencias WHERE aluno_id = ?");
            $stmtHist->execute([$aluno['id']]);
            $temHistorico = $stmtHist->fetchColumn() > 0;

            // EPIs faltando hoje
            $missing = [];
            if ($temRisco) {
                $stmtEpi = $pdo->prepare("
                    SELECT e.nome 
                    FROM ocorrencias o 
                    JOIN epis e ON e.id = o.epi_id 
                    WHERE o.aluno_id = ? AND DATE(o.data_hora) = CURDATE()
                ");
                $stmtEpi->execute([$aluno['id']]);
                $missing = $stmtEpi->fetchAll(PDO::FETCH_COLUMN);
            }

            $resultado[] = [
                'id'      => $aluno['id'],
                'name'    => $aluno['nome'],
                'course'  => $aluno['curso_nome'], // Ajustado para pegar do JOIN
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
?>