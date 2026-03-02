<?php
// =================================================================================
// ARQUIVO: apis/api_gestao.php
// =================================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Bloqueio de Acesso para não-super_admin (opcional, já que a página bloqueia, mas seguro)
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] !== 'super_admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    $action = $_GET['action'] ?? '';

    // 1. LISTAR ALUNOS
    if ($action === 'list_alunos') {
        $search = $_GET['search'] ?? '';
        $sql = "SELECT a.id, a.nome, a.curso_id, a.turno, a.foto_referencia, c.nome as curso_nome,
                (SELECT COALESCE(COUNT(*) / NULLIF(COUNT(DISTINCT DATE(data_hora)), 0), 0) 
                 FROM ocorrencias 
                 WHERE aluno_id = a.id AND tipo = 0) as daily_avg
                FROM alunos a 
                LEFT JOIN cursos c ON c.id = a.curso_id 
                WHERE a.nome LIKE ? 
                ORDER BY a.nome ASC";
        
        $stmt = mysqli_prepare($conn, $sql);
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "s", $searchParam);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        exit;
    }

    // 2. SALVAR ALUNO (NOVO OU EDITAR)
    if ($action === 'save_aluno') {
        $id = $_POST['id'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $curso_id = $_POST['curso_id'] ?? '';
        
        if (empty($nome) || empty($curso_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Nome e Curso são obrigatórios.']);
            exit;
        }

        $foto_referencia = null;

        // Processamento de Foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = "aluno_" . time() . "_" . uniqid() . "." . $ext;
            $uploadDir = __DIR__ . '/../uploads/alunos/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                $foto_referencia = 'uploads/alunos/' . $fileName;
            }
        }

        if (empty($id)) {
            // INSERT
            $sql = "INSERT INTO alunos (nome, curso_id, foto_referencia) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sis", $nome, $curso_id, $foto_referencia);
        } else {
            // UPDATE
            if ($foto_referencia) {
                $sql = "UPDATE alunos SET nome = ?, curso_id = ?, foto_referencia = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sisi", $nome, $curso_id, $foto_referencia, $id);
            } else {
                $sql = "UPDATE alunos SET nome = ?, curso_id = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sii", $nome, $curso_id, $id);
            }
        }

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    // 3. EXCLUIR ALUNO
    if ($action === 'delete_aluno') {
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            $sql = "DELETE FROM alunos WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
            }
        }
        exit;
    }

    // LISTAR PROFESSORES (se necessário para manter compatibilidade com gestao_professores.js se ele usar essa mesma api)
    if ($action === 'list_professores') {
        $search = $_GET['search'] ?? '';
        $sql = "SELECT u.id, u.nome, u.usuario, u.cargo, u.id_curso, c.nome as curso_nome 
                FROM usuarios u 
                LEFT JOIN cursos c ON c.id = u.id_curso 
                WHERE (u.nome LIKE ? OR u.usuario LIKE ?) AND u.cargo != 'super_admin'
                ORDER BY u.nome ASC";
        $stmt = mysqli_prepare($conn, $sql);
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "ss", $searchParam, $searchParam);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        exit;
    }

    // SALVAR PROFESSOR
    if ($action === 'save_professor') {
        $id = $_POST['id'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $cargo = $_POST['cargo'] ?? 'instructor';
        $id_curso = $_POST['id_curso'] ?? null;

        if (empty($id)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, usuario, senha, cargo, id_curso) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $nome, $usuario, $senhaHash, $cargo, $id_curso);
        } else {
            if (!empty($senha)) {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nome = ?, usuario = ?, senha = ?, cargo = ?, id_curso = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssii", $nome, $usuario, $senhaHash, $cargo, $id_curso, $id);
            } else {
                $sql = "UPDATE usuarios SET nome = ?, usuario = ?, cargo = ?, id_curso = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssiii", $nome, $usuario, $cargo, $id_curso, $id);
            }
        }

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    // EXCLUIR PROFESSOR
    if ($action === 'delete_professor') {
        $id = $_POST['id'] ?? 0;
        $sql = "DELETE FROM usuarios WHERE id = ? AND cargo != 'super_admin'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
