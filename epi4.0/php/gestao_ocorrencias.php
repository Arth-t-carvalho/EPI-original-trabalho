<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Bloqueio de Acesso para não-super_admin
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit;
}

// DADOS DO USUÁRIO
$sqlUser = "SELECT nome, cargo FROM usuarios WHERE id = ? LIMIT 1";
$stmtUser = mysqli_prepare($conn, $sqlUser);
$userRef = $_SESSION['usuario_id'];
mysqli_stmt_bind_param($stmtUser, "i", $userRef);
mysqli_stmt_execute($stmtUser);
$resUser = mysqli_stmt_get_result($stmtUser);
$userData = mysqli_fetch_assoc($resUser);

$nomeUsuario = $userData['nome'] ?? ($_SESSION['nome'] ?? 'Usuário');
$cargoUsuario = ucfirst($userData['cargo'] ?? ($_SESSION['cargo'] ?? 'Visitante'));

// Lista de Cursos para Filtro
$resCursos = $conn->query("SELECT id, nome FROM cursos ORDER BY nome ASC");
$listaCursos = [];
while ($c = $resCursos->fetch_assoc()) $listaCursos[] = $c;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Gestão de Ocorrências</title>
    <link rel="stylesheet" href="../css/Ocorrencia.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/dark.css">
    <link rel="stylesheet" href="../css/transitions.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../js/Dark.js"></script>
    <script src="../js/transitions.js"></script>
    <style>
        .gestao-container {
            padding: 20px;
        }

        .filter-bar {
            background: var(--card-bg);
            padding: 15px;
            border-radius: 12px;
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            align-items: center;
        }

        .search-box {
            position: relative;
            flex: 1;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .search-box input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        .table-card {
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 15px;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .data-table td {
            padding: 15px;
            border-top: 1px solid #f1f5f9;
        }

        .btn-verify {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            transition: opacity 0.2s;
        }

        .btn-verify:hover {
            opacity: 0.9;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pendente {
            background: #fef3c7;
            color: #92400e;
        }

        /* Modal active state */
        .modal-verify {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-verify.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content-inner {
            background: white;
            width: 90%;
            max-width: 500px;
            border-radius: 12px;
            padding: 25px;
            position: relative;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <main class="main-content">

        <header class="header">
            <div class="page-title">
                <h1>Gestão de Ocorrências</h1>
                <p>Verificação centralizada de infrações pendentes</p>
            </div>
            <div class="header-actions">
                <div class="user-profile-trigger" id="profileTrigger" onclick="toggleInstructorCard()">
                    <div class="user-info-mini">
                        <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($cargoUsuario); ?></span>
                    </div>
                    <div class="user-avatar"><?php echo strtoupper(substr($nomeUsuario, 0, 2)); ?></div>
                </div>
            </div>
        </header>

        <div class="gestao-container">
            <div class="filter-bar">
                <div class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" id="searchOcorrencia" placeholder="Buscar aluno...">
                </div>
                <select class="form-select" id="filterCurso" style="max-width: 250px;">
                    <option value="todos">Todos os Cursos</option>
                    <?php foreach ($listaCursos as $c): ?>
                        <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Aluno</th>
                            <th>Curso</th>
                            <th>Infração</th>
                            <th>Status</th>
                            <th style="text-align: right;">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tableOcorrencias">
                        <!-- Carregado via JS -->
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Modal de Verificação -->
    <div class="modal-verify" id="modalVerify">
        <div class="modal-content-inner">
            <h2 style="margin-bottom: 20px; color: #1f2937;">Verificar Infração</h2>
            <div id="verifyDetails" style="margin-bottom: 20px;">
                <!-- Carregado via JS -->
            </div>

            <form id="formVerify">
                <input type="hidden" name="ocorrencia_id" id="verifyId">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">Ação Administrativa</label>
                    <select class="form-select" name="tipo" style="width: 100%;">
                        <option value="obs">Apenas Registrar Observação</option>
                        <option value="adv_verbal">Advertência Verbal</option>
                        <option value="adv_escrita">Advertência Escrita</option>
                        <option value="suspensao">Suspensão</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Observações</label>
                    <textarea class="form-textarea" name="observacao" style="width: 100%; height: 80px;" placeholder="Detalhes da decisão..."></textarea>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-cancel" onclick="closeModal('modalVerify')">Cancelar</button>
                    <button type="submit" class="btn btn-submit">Confirmar Ação</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/gestao_ocorrencias.js" defer></script>
    <script src="../js/notifications.js" defer></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
