<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Bloqueio de Acesso para não-super_admin em todas as ações
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] !== 'super_admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    // --- GESTÃO DE ALUNOS ---
    case 'list_alunos':
        $search = $_GET['search'] ?? '';
        $sql = "SELECT a.id, a.nome, c.nome as curso_nome, a.curso_id 
                FROM alunos a 
                JOIN cursos c ON a.curso_id = c.id
                WHERE a.status = 'ativo'";
        
        if (!empty($search)) {
            $sql .= " WHERE a.nome LIKE ?";
            $stmt = mysqli_prepare($conn, $sql);
            $searchTerm = "%$search%";
            mysqli_stmt_bind_param($stmt, "s", $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($conn, $sql);
        }
        
        $alunos = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($alunos);
        break;

    case 'save_aluno':
        $id = $_POST['id'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $curso_id = $_POST['curso_id'] ?? '';

        if (empty($id)) {
            // INSERT
            $sql = "INSERT INTO alunos (nome, curso_id) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $nome, $curso_id);
        } else {
            // UPDATE
            $sql = "UPDATE alunos SET nome = ?, curso_id = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sii", $nome, $curso_id, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        break;

    case 'delete_aluno':
        $id = $_POST['id'] ?? '';
        $sql = "UPDATE alunos SET status = 'suspenso' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        break;

    // --- GESTÃO DE CURSOS ---
    case 'list_cursos':
        $search = $_GET['search'] ?? '';
        $sql = "SELECT id, nome, vagas FROM cursos WHERE status = 'ativo'";
        
        if (!empty($search)) {
            $sql .= " WHERE nome LIKE ? ORDER BY nome ASC";
            $stmt = mysqli_prepare($conn, $sql);
            $searchTerm = "%$search%";
            mysqli_stmt_bind_param($stmt, "s", $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $sql .= " ORDER BY nome ASC";
            $result = mysqli_query($conn, $sql);
        }
        
        $cursos = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($cursos);
        break;

    case 'save_curso':
        $id = $_POST['id'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $vagas = $_POST['vagas'] ?? 0;

        if (empty($id)) {
            $sql = "INSERT INTO cursos (nome, vagas) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $nome, $vagas);
        } else {
            $sql = "UPDATE cursos SET nome = ?, vagas = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sii", $nome, $vagas, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        break;

    case 'delete_curso':
        $id = $_POST['id'] ?? '';
        // Verifica se tem alunos vinculados antes de deletar
        $check = mysqli_query($conn, "SELECT id FROM alunos WHERE curso_id = $id LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Não é possível excluir um curso que possui alunos vinculados.']);
            exit;
        }

        $sql = "UPDATE cursos SET status = 'suspenso' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        break;

    // --- GESTÃO DE PROFESSORES ---
    case 'list_professores':
        $search = $_GET['search'] ?? '';
        $sql = "SELECT u.id, u.nome, u.usuario, u.cargo, c.nome as curso_nome, u.id_curso 
                FROM usuarios u 
                LEFT JOIN cursos c ON u.id_curso = c.id 
                WHERE u.cargo != 'super_admin' AND u.status = 'ativo'";
        
        if (!empty($search)) {
            $sql .= " AND u.nome LIKE ?";
            $stmt = mysqli_prepare($conn, $sql);
            $searchTerm = "%$search%";
            mysqli_stmt_bind_param($stmt, "s", $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($conn, $sql);
        }
        
        $professores = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($professores);
        break;

    case 'save_professor':
        $id = $_POST['id'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $cargo = $_POST['cargo'] ?? 'instrutor';
        $id_curso = $_POST['id_curso'] ?? 1;

        // Validação de e-mail Gmail
        if (!preg_match('/@gmail\.com$/i', $usuario)) {
            echo json_encode(['status' => 'error', 'message' => 'O usuário de professor deve ser um e-mail @gmail.com.']);
            exit;
        }

        if (empty($id)) {
            $sql = "INSERT INTO usuarios (nome, usuario, senha, cargo, id_curso) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $nome, $usuario, $senha, $cargo, $id_curso);
        } else {
            if (!empty($senha)) {
                $sql = "UPDATE usuarios SET nome = ?, usuario = ?, senha = ?, cargo = ?, id_curso = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssii", $nome, $usuario, $senha, $cargo, $id_curso, $id);
            } else {
                $sql = "UPDATE usuarios SET nome = ?, usuario = ?, cargo = ?, id_curso = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssii", $nome, $usuario, $cargo, $id_curso, $id);
            }
        }

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        break;

    case 'delete_professor':
        $id = $_POST['id'] ?? '';
        $sql = "UPDATE usuarios SET status = 'suspenso' WHERE id = ? AND cargo != 'super_admin'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Ação não definida.']);
        break;
}
?>
