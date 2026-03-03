<?php
// =================================================================================
// ARQUIVO: apis/api.php (CORRIGIDO PARA MYSQLI - SEM PDO)
// =================================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php'; // Proteção de sessão

// Limpa buffer para evitar erros de JSON
if (ob_get_length())
    ob_clean();

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1); // Temporário para debug

$cursoId = (isset($_SESSION['usuario_id_curso']) && (int)$_SESSION['usuario_id_curso'] > 0) ? (int)$_SESSION['usuario_id_curso'] : 1;
$cargo = strtolower($_SESSION['cargo'] ?? '');
$isSuperAdmin = ($cargo === 'super_admin');

// Se for Super Admin, ele pode vir a filtrar por um curso específico vindo via GET
$isFiltering = ($isSuperAdmin && isset($_GET['course_id']));
if ($isFiltering && $_GET['course_id'] !== 'all' && (int)$_GET['course_id'] > 0) {
    $cursoId = (int)$_GET['course_id'];
}

try {
    $action = $_GET['action'] ?? '';
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

    // Função auxiliar para formatar array de meses
    function formatMonthArray($result)
    {
        $arr = array_fill(0, 12, 0);
        while ($r = mysqli_fetch_assoc($result)) {
            $idx = (int)$r['mes'] - 1;
            if ($idx >= 0 && $idx < 12) {
                $arr[$idx] = (int)$r['qtd'];
            }
        }
        return $arr;
    }

    if ($action === 'charts') {
        // [SQL queries for A, B, C, D use filtering logic]
        // Exemplo para Capacete:
        $sql = "SELECT MONTH(o.data_hora) as mes, COUNT(*) as qtd 
                FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id 
                WHERE o.epi_id = 2 AND YEAR(o.data_hora) = ? " . ($isFiltering && $_GET['course_id'] === 'all' ? "" : " AND a.curso_id = ? ") .
            " GROUP BY mes";
        $stmt = mysqli_prepare($conn, $sql);
        if ($isFiltering && $_GET['course_id'] === 'all') {
            mysqli_stmt_bind_param($stmt, "i", $year);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $year, $cursoId);
        }
        mysqli_stmt_execute($stmt);
        $capaceteArr = formatMonthArray(mysqli_stmt_get_result($stmt));

        // B) Barras - Óculos (ID 1)
        $sql = "SELECT MONTH(o.data_hora) as mes, COUNT(*) as qtd 
                FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id 
                WHERE o.epi_id = 1 AND YEAR(o.data_hora) = ? " . ($isFiltering && $_GET['course_id'] === 'all' ? "" : " AND a.curso_id = ? ") .
            " GROUP BY mes";
        $stmt = mysqli_prepare($conn, $sql);
        if ($isFiltering && $_GET['course_id'] === 'all') {
            mysqli_stmt_bind_param($stmt, "i", $year);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $year, $cursoId);
        }
        mysqli_stmt_execute($stmt);
        $oculosArr = formatMonthArray(mysqli_stmt_get_result($stmt));

        // C) Total Geral
        $sql = "SELECT MONTH(o.data_hora) as mes, COUNT(*) as qtd 
                FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id 
                WHERE YEAR(o.data_hora) = ? " . ($isFiltering && $_GET['course_id'] === 'all' ? "" : " AND a.curso_id = ? ") .
            " GROUP BY mes";
        $stmt = mysqli_prepare($conn, $sql);
        if ($isFiltering && $_GET['course_id'] === 'all') {
            mysqli_stmt_bind_param($stmt, "i", $year);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $year, $cursoId);
        }
        mysqli_stmt_execute($stmt);
        $totalArr = formatMonthArray(mysqli_stmt_get_result($stmt));

        // Rosca
        $sql = "SELECT e.nome, COUNT(*) as qtd FROM ocorrencias o 
                JOIN epis e ON e.id = o.epi_id 
                JOIN alunos a ON a.id = o.aluno_id
                WHERE YEAR(o.data_hora) = ? " . ($isFiltering && $_GET['course_id'] === 'all' ? "" : " AND a.curso_id = ? ") .
            " GROUP BY e.nome";
        $stmt = mysqli_prepare($conn, $sql);
        if ($isFiltering && $_GET['course_id'] === 'all') {
            mysqli_stmt_bind_param($stmt, "i", $year);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $year, $cursoId);
        }
        mysqli_stmt_execute($stmt);
        $resDoughnut = mysqli_stmt_get_result($stmt);
        $labels = [];
        $dataDoughnut = [];
        while ($d = mysqli_fetch_assoc($resDoughnut)) {
            $labels[] = $d['nome'];
            $dataDoughnut[] = (int)$d['qtd'];
        }

        // E) Resumo KPI para atualização dinâmica
        $sqlTotal = "SELECT COUNT(*) as total FROM alunos " . ($isFiltering && $_GET['course_id'] === 'all' ? "" : " WHERE curso_id = ? ");
        $stmtTotal = mysqli_prepare($conn, $sqlTotal);
        if (!($isFiltering && $_GET['course_id'] === 'all')) mysqli_stmt_bind_param($stmtTotal, "i", $cursoId);
        mysqli_stmt_execute($stmtTotal);
        $totalStudents = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'] ?? 0;

        $sqlToday = "SELECT COUNT(*) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE o.data_hora >= CURDATE() AND o.data_hora < CURDATE() + INTERVAL 1 DAY" . ($isFiltering && $_GET['course_id'] === 'all' ? "" : " AND a.curso_id = ? ");
        $stmtToday = mysqli_prepare($conn, $sqlToday);
        if (!($isFiltering && $_GET['course_id'] === 'all')) mysqli_stmt_bind_param($stmtToday, "i", $cursoId);
        mysqli_stmt_execute($stmtToday);
        $todayCount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtToday))['total'] ?? 0;

        $sqlWeek = "SELECT COUNT(*) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE(), 1)" . ($isFiltering && $_GET['course_id'] === 'all' ? "" : " AND a.curso_id = ? ");
        $stmtWeek = mysqli_prepare($conn, $sqlWeek);
        if (!($isFiltering && $_GET['course_id'] === 'all')) mysqli_stmt_bind_param($stmtWeek, "i", $cursoId);
        mysqli_stmt_execute($stmtWeek);
        $weekCount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtWeek))['total'] ?? 0;

        $sqlMonth = "SELECT COUNT(*) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE MONTH(o.data_hora) = MONTH(CURDATE()) AND YEAR(o.data_hora) = YEAR(CURDATE())" . ($isFiltering && $_GET['course_id'] === 'all' ? "" : " AND a.curso_id = ? ");
        $stmtMonth = mysqli_prepare($conn, $sqlMonth);
        if (!($isFiltering && $_GET['course_id'] === 'all')) mysqli_stmt_bind_param($stmtMonth, "i", $cursoId);
        mysqli_stmt_execute($stmtMonth);
        $monthCount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMonth))['total'] ?? 0;

        echo json_encode([
            'bar' => ['capacete' => $capaceteArr, 'oculos' => $oculosArr, 'total' => $totalArr],
            'doughnut' => ['labels' => $labels, 'data' => $dataDoughnut],
            'summary' => [
                'total_students' => (int)$totalStudents,
                'today' => (int)$todayCount,
                'week' => (int)$weekCount,
                'month' => (int)$monthCount
            ]
        ]);
        exit;
    }

    // 2. CALENDÁRIO
    if ($action === 'calendar') {
        $isGlobal = ($isSuperAdmin && $_GET['course_id'] === 'all');

        if ($isGlobal) {
            // Se for Global, queremos ver os Cursos que tiveram infrações no dia
            $sql = "SELECT o.data_hora as full_date, c.nome AS name, e.nome AS `desc`, DATE_FORMAT(o.data_hora, '%H:%i') AS time
                    FROM ocorrencias o
                    JOIN alunos a ON o.aluno_id = a.id
                    JOIN cursos c ON a.curso_id = c.id
                    LEFT JOIN epis e ON e.id = o.epi_id
                    WHERE MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ?
                    ORDER BY o.data_hora ASC";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $month, $year);
        } else {
            // Se for curso específico ou professor, mantém alunos
            $sql = "SELECT o.data_hora as full_date, a.nome AS name, e.nome AS `desc`, DATE_FORMAT(o.data_hora, '%H:%i') AS time
                    FROM ocorrencias o
                    JOIN alunos a ON o.aluno_id = a.id
                    LEFT JOIN epis e ON e.id = o.epi_id
                    WHERE MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ? AND a.curso_id = ?
                    ORDER BY o.data_hora ASC";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iii", $month, $year, $cursoId);
        }

        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        exit;
    }

    // 3. LISTAGEM COMPLETA DE OCORRÊNCIAS (Para Super Admin / Gestão)
    if ($action === 'list_all_ocorrencias') {
        $search = $_GET['search'] ?? '';
        $filtroCurso = $_GET['curso_id'] ?? '';

        $sql = "SELECT o.id, o.data_hora, a.nome AS aluno_nome, c.nome AS curso_nome, e.nome AS epi_nome
                FROM ocorrencias o
                JOIN alunos a ON o.aluno_id = a.id
                LEFT JOIN cursos c ON a.curso_id = c.id
                LEFT JOIN epis e ON o.epi_id = e.id
                LEFT JOIN acoes_ocorrencia ac ON ac.ocorrencia_id = o.id
                WHERE o.tipo = 0 AND o.oculto = 0 AND ac.id IS NULL"; // Apenas Não Conformidades PENDENTES de ação humana

        $params = [];
        $types = "";

        if (!$isSuperAdmin) {
            $sql .= " AND a.curso_id = ? ";
            $params[] = $cursoId;
            $types .= "i";
        } elseif (!empty($filtroCurso) && $filtroCurso !== 'todos') {
            $sql .= " AND a.curso_id = ? ";
            $params[] = (int)$filtroCurso;
            $types .= "i";
        }

        if (!empty($search)) {
            $sql .= " AND a.nome LIKE ? ";
            $params[] = "%$search%";
            $types .= "s";
        }

        $sql .= " ORDER BY o.data_hora DESC LIMIT 100";

        $stmt = mysqli_prepare($conn, $sql);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        exit;
    }

    // 4. MODAL (DETALHES)
    if ($action === 'modal_details') {
        $mesSQL = ($month == 0) ? 1 : $month;
        $epiFilter = $_GET['epi'] ?? '';
        $isGlobal = ($isSuperAdmin && (!isset($_GET['course_id']) || $_GET['course_id'] === 'all'));

        if ($isGlobal) {
            // Se for Global, queremos o RESUMO POR CURSO
            $sql = "SELECT 
                        c.id AS curso_id,
                        c.nome AS curso_nome,
                        COUNT(o.id) AS total_infracoes,
                        (SELECT COUNT(*) FROM alunos a2 WHERE a2.curso_id = c.id) AS total_alunos,
                        (SELECT COUNT(DISTINCT o2.aluno_id) 
                         FROM ocorrencias o2 
                         JOIN alunos a3 ON a3.id = o2.aluno_id 
                         WHERE a3.curso_id = c.id AND MONTH(o2.data_hora) = ? AND YEAR(o2.data_hora) = ?) AS alunos_com_infracao
                    FROM cursos c
                    LEFT JOIN alunos a ON a.curso_id = c.id
                    LEFT JOIN ocorrencias o ON o.aluno_id = a.id AND MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ?
                    " . (!empty($epiFilter) ? " WHERE o.id IS NOT NULL AND (SELECT e.nome FROM epis e WHERE e.id = o.epi_id) = ? " : "") . "
                    GROUP BY c.id
                    HAVING total_infracoes > 0
                    ORDER BY total_infracoes DESC";

            $stmt = mysqli_prepare($conn, $sql);
            if (!empty($epiFilter)) {
                mysqli_stmt_bind_param($stmt, "iiiis", $mesSQL, $year, $mesSQL, $year, $epiFilter);
            } else {
                mysqli_stmt_bind_param($stmt, "iiii", $mesSQL, $year, $mesSQL, $year);
            }
        } else {
            // Se for curso específico, mantém o detalhamento por ALUNO
            $sql = "SELECT o.id AS ocorrencia_id, DATE_FORMAT(o.data_hora, '%d/%m/%Y') AS data, a.nome AS aluno, a.id AS aluno_id, c.nome AS curso,
                           COALESCE(e.nome, 'Não informado') AS epis, DATE_FORMAT(o.data_hora, '%H:%i') AS hora,
                           CASE WHEN ac.id IS NOT NULL THEN 'Resolvido' ELSE 'Pendente' END AS status_formatado
                    FROM ocorrencias o
                    JOIN alunos a ON a.id = o.aluno_id
                    LEFT JOIN cursos c ON c.id = a.curso_id
                    LEFT JOIN epis e ON e.id = o.epi_id
                    LEFT JOIN acoes_ocorrencia ac ON ac.ocorrencia_id = o.id
                    WHERE MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ? AND a.curso_id = ? ";

            if (!empty($epiFilter)) {
                $sql .= " AND e.nome = ? ";
            }

            $sql .= " GROUP BY o.id ORDER BY o.data_hora DESC";
            $stmt = mysqli_prepare($conn, $sql);
            if (!empty($epiFilter)) {
                mysqli_stmt_bind_param($stmt, "iiis", $mesSQL, $year, $cursoId, $epiFilter);
            } else {
                mysqli_stmt_bind_param($stmt, "iii", $mesSQL, $year, $cursoId);
            }
        }

        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        exit;
    }

    // 4. CONTADOR DE NOTIFICAÇÕES (Pendentes)
    if ($action === 'notification_count') {
        $seenId = (int)($_GET['seen_id'] ?? 0);
        $withDetails = isset($_GET['details']) && $_GET['details'] == '1';

        // Conta quantos tem ID > que o visto pelo usuário
        $sqlCount = "SELECT COUNT(o.id) as total, MAX(o.id) as max_id 
                     FROM ocorrencias o 
                     JOIN alunos a ON a.id = o.aluno_id 
                     WHERE o.id > ? " . ($isSuperAdmin ? "" : " AND a.curso_id = ? ");

        $stmt = mysqli_prepare($conn, $sqlCount);
        if ($isSuperAdmin) {
            mysqli_stmt_bind_param($stmt, "i", $seenId);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $seenId, $cursoId);
        }
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);

        $details = [];
        if ($withDetails && ($row['total'] ?? 0) > 0) {
            $sqlDetails = "SELECT a.nome as aluno, e.nome as epi, o.id 
                           FROM ocorrencias o 
                           JOIN alunos a ON a.id = o.aluno_id 
                           JOIN epis e ON e.id = o.epi_id 
                           WHERE o.id > ? " . ($isSuperAdmin ? "" : " AND a.curso_id = ? ") .
                " ORDER BY o.id DESC LIMIT 3";
            $stmtD = mysqli_prepare($conn, $sqlDetails);
            if ($isSuperAdmin) {
                mysqli_stmt_bind_param($stmtD, "i", $seenId);
            } else {
                mysqli_stmt_bind_param($stmtD, "ii", $seenId, $cursoId);
            }
            mysqli_stmt_execute($stmtD);
            $resD = mysqli_stmt_get_result($stmtD);
            while ($d = mysqli_fetch_assoc($resD)) {
                $details[] = $d;
            }
        }

        echo json_encode([
            'count' => (int)($row['total'] ?? 0),
            'max_id' => (int)($row['max_id'] ?? 0),
            'new_items' => $details
        ]);
        exit;
    }

    // 5. SALVAR OCORRÊNCIA MANUAL (Formulário)
    if ($action === 'save_occurrence') {
        $alunoId = (int)($_POST['aluno_id'] ?? 0);
        $epiId = (int)($_POST['epi_id'] ?? 1); // Padrão 1 se não vier
        $tipoRegistro = $_POST['tipo'] ?? 'obs';
        $observacao = $_POST['observacao'] ?? '';
        $usuarioId = $_SESSION['usuario_id'] ?? 0;

        if ($alunoId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Aluno não selecionado.']);
            exit;
        }

        // 1. Cria a ocorrência básica (tipo=0 por padrão para não conformidade, ou o sistema decide)
        // Aqui assumimos que se o professor está abrindo, ele está registrando uma não-conformidade (tipo 0)
        $sql = "INSERT INTO ocorrencias (aluno_id, epi_id, tipo, data_hora) VALUES (?, ?, 0, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $alunoId, $epiId);

        if (mysqli_stmt_execute($stmt)) {
            $ocorrenciaId = mysqli_insert_id($conn);

            // 2. Registra a ação tomada (Advertência/Observação)
            $sqlAcao = "INSERT INTO acoes_ocorrencia (ocorrencia_id, tipo, observacao, usuario_id, data_hora) 
                        VALUES (?, ?, ?, ?, NOW())";
            $stmtAcao = mysqli_prepare($conn, $sqlAcao);
            mysqli_stmt_bind_param($stmtAcao, "issi", $ocorrenciaId, $tipoRegistro, $observacao, $usuarioId);
            mysqli_stmt_execute($stmtAcao);

            // 3. Processa Evidências (Fotos)
            if (isset($_FILES['fotos'])) {
                $uploadDir = __DIR__ . '/../uploads/evidencias/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                foreach ($_FILES['fotos']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['fotos']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['fotos']['name'][$key], PATHINFO_EXTENSION);
                        $fileName = "evidência_" . $ocorrenciaId . "_" . time() . "_" . $key . "." . $ext;

                        if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                            $caminho = 'uploads/evidencias/' . $fileName;
                            $sqlEv = "INSERT INTO evidencias (ocorrencia_id, imagem) VALUES (?, ?)";
                            $stmtEv = mysqli_prepare($conn, $sqlEv);
                            mysqli_stmt_bind_param($stmtEv, "is", $ocorrenciaId, $caminho);
                            mysqli_stmt_execute($stmtEv);
                        }
                    }
                }
            }

            echo json_encode(['success' => true, 'id' => $ocorrenciaId]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
        exit;
    }

    // 5. RESOLVER OCORRÊNCIA (Assinar)
    if ($action === 'resolve_occurrence') {
        $ocorrenciaId = (int)($_POST['ocorrencia_id'] ?? 0);
        $tipo = $_POST['tipo'] ?? 'obs';
        $observacao = $_POST['observacao'] ?? '';
        $usuarioId = $_SESSION['usuario_id'] ?? 0;

        if ($ocorrenciaId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de ocorrência inválido ou não fornecido.']);
            exit;
        }

        // Insere na tabela de ações para mudar o status para 'Resolvido'/'Confirmado'
        $sql = "INSERT INTO acoes_ocorrencia (ocorrencia_id, tipo, observacao, usuario_id, data_hora) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issi", $ocorrenciaId, $tipo, $observacao, $usuarioId);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao salvar: ' . mysqli_error($conn)]);
        }
        exit;
    }

    // 6. DISPENSAR OCORRÊNCIA (Ocultar do front)
    if ($action === 'dismiss_occurrence') {
        $ocorrenciaId = (int)($_POST['ocorrencia_id'] ?? 0);
        if ($ocorrenciaId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID inválido.']);
            exit;
        }

        $sql = "UPDATE ocorrencias SET oculto = 1 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $ocorrenciaId);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
        exit;
    }

    // 5. LISTA DE ALUNOS
    if (empty($action)) {
        $sqlAlunos = "SELECT a.id, a.nome, c.nome as curso_nome FROM alunos a 
                      LEFT JOIN cursos c ON c.id = a.curso_id 
                      WHERE a.curso_id = ?";
        $stmtAlunos = mysqli_prepare($conn, $sqlAlunos);
        mysqli_stmt_bind_param($stmtAlunos, "i", $cursoId);
        mysqli_stmt_execute($stmtAlunos);
        $resAlunos = mysqli_stmt_get_result($stmtAlunos);
        $resultado = [];

        while ($aluno = mysqli_fetch_assoc($resAlunos)) {
            // Risco Hoje
            $sqlRisco = "SELECT COUNT(*) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE o.aluno_id = ? AND DATE(o.data_hora) = CURDATE() AND a.curso_id = ?";
            $stmtRisco = mysqli_prepare($conn, $sqlRisco);
            mysqli_stmt_bind_param($stmtRisco, "ii", $aluno['id'], $cursoId);
            mysqli_stmt_execute($stmtRisco);
            $temRisco = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtRisco))['total'] > 0;

            // Histórico
            $sqlHist = "SELECT COUNT(*) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE o.aluno_id = ? AND a.curso_id = ?";
            $stmtHist = mysqli_prepare($conn, $sqlHist);
            mysqli_stmt_bind_param($stmtHist, "ii", $aluno['id'], $cursoId);
            mysqli_stmt_execute($stmtHist);
            $temHistorico = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtHist))['total'] > 0;

            // EPIs faltando hoje
            $missing = [];
            if ($temRisco) {
                $sqlEpi = "SELECT e.nome FROM ocorrencias o JOIN epis e ON e.id = o.epi_id JOIN alunos a ON a.id = o.aluno_id WHERE o.aluno_id = ? AND DATE(o.data_hora) = CURDATE() AND a.curso_id = ?";
                $stmtEpi = mysqli_prepare($conn, $sqlEpi);
                mysqli_stmt_bind_param($stmtEpi, "ii", $aluno['id'], $cursoId);
                mysqli_stmt_execute($stmtEpi);
                $resEpi = mysqli_stmt_get_result($stmtEpi);
                while ($rowEpi = mysqli_fetch_assoc($resEpi)) {
                    $missing[] = $rowEpi['nome'];
                }
            }

            $resultado[] = [
                'id' => $aluno['id'],
                'name' => $aluno['nome'],
                'course' => $aluno['curso_nome'],
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
