<?php
require_once __DIR__ . '/../config/database.php';

// ==========================================
// 1. LÓGICA DE FILTROS (BACK-END)
// ==========================================
$filtroData = $_GET['periodo'] ?? ($_GET['filtro'] ?? 'hoje');
$filtroEpi = isset($_GET['epi']) ? $_GET['epi'] : '';

try {
    $stmtEpis = $pdo->query("SELECT id, nome FROM epis ORDER BY nome ASC");
    $listaEpis = $stmtEpis->fetchAll(PDO::FETCH_ASSOC);

    $sql = "
        SELECT 
            o.id, 
            o.data_hora,
            a.nome AS aluno_nome,
            c.nome AS aluno_curso,
            e.nome AS epi_nome,
            ev.imagem AS foto_caminho 
        FROM ocorrencias o
        JOIN alunos a ON a.id = o.aluno_id
        LEFT JOIN cursos c ON c.id = a.curso_id
        JOIN epis e ON e.id = o.epi_id
        LEFT JOIN evidencias ev ON ev.ocorrencia_id = o.id 
        WHERE 1=1
    ";
    
    if ($filtroData == 'hoje' || $filtroData == 'dia') {
        $sql .= " AND DATE(o.data_hora) = CURDATE()";
    } elseif ($filtroData == '7dias' || $filtroData == 'semana') {
        $sql .= " AND o.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($filtroData == '30dias' || $filtroData == 'mes') {
        $sql .= " AND o.data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }

    if (!empty($filtroEpi)) {
        $sql .= " AND o.epi_id = :epi_id";
    }

    $sql .= " ORDER BY o.data_hora DESC LIMIT 100";

    $stmt = $pdo->prepare($sql);
    if (!empty($filtroEpi)) {
        $stmt->bindValue(':epi_id', $filtroEpi);
    }
    $stmt->execute();
    $infracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $infracoes = [];
    $listaEpis = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Infrações</title>
    <link rel="stylesheet" href="../css/infracoes.css">
    <style>
        /* --- ESTILOS DO MODAL --- */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: none; z-index: 10000;
            justify-content: center; align-items: center;
        }
        .modal-overlay.active { display: flex !important; }
        .modal-content {
            background: white; padding: 20px; border-radius: 12px;
            width: 90%; max-width: 500px; position: relative;
            text-align: center; display: flex; flex-direction: column; gap: 15px;
        }
        .full-image { width: 100%; max-height: 55vh; object-fit: contain; border-radius: 8px; background: #000; }
        .btn-assinar {
            background-color: #DC2626; color: white; border: none; padding: 12px;
            border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;
            width: 100%; transition: background 0.2s; margin-top: 10px;
        }

        /* --- GRID E CARDS --- */
        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
            padding: 20px 0;
        }
        .violation-card {
            background: white; border-radius: 10px; overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08); cursor: pointer;
            border: 1px solid #f0f0f0; transition: all 0.3s ease;
        }
        .violation-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card-image-wrapper { height: 140px; background: #f3f4f6; }
        .card-image { width: 100%; height: 100%; object-fit: cover; }
        .card-content { padding: 12px; }
        .violation-tag { background: #fee2e2; color: #dc2626; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
        .infrator-name { display: block; font-weight: 600; font-size: 14px; margin-top: 6px; color: #1f2937; }
        .timestamp { color: #6b7280; font-size: 11px; margin-top: 4px; }

        /* --- HEADER E BUSCA (O QUE VOCÊ PEDIU) --- */
        .header-container { width: 100%; }
        
        /* --- HEADER E BUSCA --- */
        .header-container { width: 100%; }
        
        .header-controls {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
            width: 100%;
        }

        /* Alterado: agora usa column para ficar um embaixo do outro */
        /* --- FILTROS (Lado a lado, menores e estilizados) --- */
        .filters-row {
            display: flex;
            flex-direction: row; /* Garante que fiquem um ao lado do outro */
            gap: 12px;
            width: 100%;
            align-items: center;
        }

        .filter-select {
            flex: 0 1 auto; /* Impede que eles estiquem para ocupar a tela toda */
            min-width: 180px; /* Tamanho exato e compacto */
            padding: 10px 36px 10px 14px; /* Espaço extra na direita para a seta customizada */
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            
            /* Remove a seta padrão do sistema */
            appearance: none; 
            -webkit-appearance: none;
            -moz-appearance: none;
            
            /* Seta customizada em SVG (mais limpa e moderna) */
            background-color: #f8fafc;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        /* Efeito ao passar o mouse */
        .filter-select:hover {
            border-color: #cbd5e1;
            background-color: #ffffff;
        }

        /* Efeito ao clicar (com a cor vermelha do seu tema) */
        .filter-select:focus {
            outline: none;
            border-color: #DC2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
            background-color: #ffffff;
        }

        /* Container de busca ocupando 100% real (NÃO MEXIDO) */
        .search-container-full {
            width: 100%;
            margin-bottom: 5px;
        }
        
        /* ... restante do seu css ... */

        .search-wrapper-animated {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        /* Efeito de animação ao focar */
        .search-wrapper-animated:focus-within {
            border-color: #DC2626;
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.1);
            transform: translateY(-3px);
        }

        .search-wrapper-animated input {
            border: none;
            outline: none;
            width: 100%;
            padding: 5px 12px;
            font-size: 15px;
            color: #1e293b;
            background: transparent;
        }

        .search-icon {
            color: #94a3b8;
            width: 20px;
            height: 20px;
            transition: color 0.3s ease;
        }

        .search-wrapper-animated:focus-within .search-icon {
            color: #DC2626;
        }
        
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3"
                style="filter: drop-shadow(0 2px 4px rgba(227, 6, 19, 0.3));">
                <circle cx="12" cy="12" r="10" />
            </svg>

            &nbsp; EPI <span>GUARD</span>
        </div>

        <nav class="nav-menu">

            <a class="nav-item " href="dashboard.php">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>

            <a class="nav-item active" href="infracoes.php">
                <i data-lucide="alert-triangle"></i>
                <span>Infrações</span>
            </a>

            <a class="nav-item" href="controleSala.php">
                <i data-lucide="users"></i>
                <span>Controle de Sala</span>
            </a>

            <a class="nav-item" href="ocorrencias.php">
                <i data-lucide="file-text"></i>
                <span>Ocorrências</span>
            </a>

            <a class="nav-item" href="configuracoes.php">
                <i data-lucide="settings"></i>
                <span>Configurações</span>
            </a>
            <a class="nav-item" href="monitoramento.php">
                <i data-lucide="monitor"></i>
                <span>Monitoramento</span>
            </a>

        </nav>
    </aside>
    <main class="main-content">
        <header class="header">
            <div class="header-container">
                <div class="page-title">
                    <h1>Painel Geral</h1>
                    <p>Monitoramento de Segurança</p>
                </div>

                <form method="GET" class="header-controls">
                    <div class="filters-row">
                        <select name="periodo" class="filter-select" onchange="this.form.submit()">
                            <option value="hoje" <?php echo ($filtroData == 'hoje' || $filtroData == 'dia') ? 'selected' : ''; ?>>Hoje</option>
                            <option value="7dias" <?php echo ($filtroData == '7dias' || $filtroData == 'semana') ? 'selected' : ''; ?>>Últimos 7 dias</option>
                            <option value="30dias" <?php echo ($filtroData == '30dias' || $filtroData == 'mes') ? 'selected' : ''; ?>>Últimos 30 dias</option>
                            <option value="todos" <?php echo $filtroData == 'todos' ? 'selected' : ''; ?>>Tudo</option>
                        </select>

                        <select name="epi" class="filter-select" onchange="this.form.submit()">
                            <option value="">Todos os EPIs</option>
                            <?php foreach ($listaEpis as $epi): ?>
                                <option value="<?php echo $epi['id']; ?>" <?php echo $filtroEpi == $epi['id'] ? 'selected' : ''; ?>>
                                    Apenas <?php echo htmlspecialchars($epi['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="search-container-full">
                        <div class="search-wrapper-animated">
                            <i data-lucide="search" class="search-icon"></i>
                            <input type="text" id="searchInput" placeholder="Buscar por aluno, curso ou infração...">
                        </div>
                    </div>
                </form>
            </div>
        </header>

        <div class="gallery-container">
            <div class="grid-cards" id="cardsContainer">
                <?php if (empty($infracoes)): ?>
                    <p style="padding:20px; color:#666;">Nenhuma infração encontrada.</p>
                <?php else: ?>
                    <?php foreach ($infracoes as $item): 
                        $imgSrc = "mostrar_imagem.php?id=" . $item['id'];
                        $nomeSafe = htmlspecialchars($item['aluno_nome'] ?? 'Desconhecido', ENT_QUOTES);
                        $epiSafe = htmlspecialchars($item['epi_nome'] ?? 'EPI', ENT_QUOTES);
                        $setorSafe = htmlspecialchars($item['aluno_curso'] ?? 'Geral', ENT_QUOTES);
                        $dataObj = new DateTime($item['data_hora']);
                        $horaF = $dataObj->format('H:i');
                        $dataF = $dataObj->format('d/m/Y');
                    ?>
                        <div class="violation-card" onclick="openModalPHP('<?php echo $imgSrc; ?>', '<?php echo $nomeSafe; ?>', '<?php echo $epiSafe; ?>', '<?php echo $horaF; ?>', '<?php echo $dataF; ?>')">
                            <div class="card-image-wrapper">
                                <img src="<?php echo $imgSrc; ?>" class="card-image" loading="lazy">
                            </div>
                            <div class="card-content">
                                <span class="violation-tag"><?php echo $epiSafe; ?></span>
                                <span class="infrator-name"><?php echo $nomeSafe; ?></span>
                                <div class="timestamp"><?php echo $horaF; ?> • <?php echo $setorSafe; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="imageModal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button onclick="forceClose()" style="position:absolute; right:10px; top:10px; border:none; background:transparent; font-size:24px; cursor:pointer;">&times;</button>
            <img src="" id="modalImg" class="full-image">
            <div style="text-align:left; width:100%;">
                <h3 id="modalName" style="margin: 5px 0 0 0; color:#1f2937;">Nome</h3>
                <p id="modalDesc" style="color:#dc2626; font-weight:bold; margin: 5px 0;">Infração</p>
                <p id="modalTime" style="color:#666; font-size:14px; margin:0;">Horário</p>
            </div>
            <button id="btnAssinar" class="btn-assinar">Assinar Ocorrência</button>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();

        // Lógica da Busca em Tempo Real com Animação
        document.getElementById('searchInput').addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const cards = document.querySelectorAll('.violation-card');

            cards.forEach(card => {
                const content = card.innerText.toLowerCase();
                if (content.includes(term)) {
                    card.style.display = "block";
                    setTimeout(() => { card.style.opacity = "1"; card.style.transform = "scale(1)"; }, 10);
                } else {
                    card.style.opacity = "0";
                    card.style.transform = "scale(0.95)";
                    setTimeout(() => { if(card.style.opacity === "0") card.style.display = "none"; }, 300);
                }
            });
        });

        // Funções do Modal
        function openModalPHP(src, nome, epi, hora, dataCompleta) {
            document.getElementById('modalImg').src = src;
            document.getElementById('modalName').innerText = nome;
            document.getElementById('modalDesc').innerText = "Infração: " + epi;
            document.getElementById('modalTime').innerText = "Horário: " + hora + " | Data: " + dataCompleta;
            document.getElementById('imageModal').classList.add('active');
        }

        function closeModal(event) { if (event.target.id === 'imageModal') forceClose(); }
        function forceClose() {
            document.getElementById('imageModal').classList.remove('active');
            document.getElementById('modalImg').src = "";
        }
    </script>
    <script>
    lucide.createIcons();

    // --- NOVA LÓGICA: Captura parâmetro da URL ---
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const alunoParaBuscar = urlParams.get('busca');
        const inputBusca = document.getElementById('searchInput');

        if (alunoParaBuscar && inputBusca) {
            inputBusca.value = alunoParaBuscar;
            // Dispara o evento de input para filtrar os cards imediatamente
            inputBusca.dispatchEvent(new Event('input'));
        }
    });

    // Lógica da Busca em Tempo Real (Já existente no seu código)
    document.getElementById('searchInput').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        const cards = document.querySelectorAll('.violation-card');

        cards.forEach(card => {
            const content = card.innerText.toLowerCase();
            if (content.includes(term)) {
                card.style.display = "block";
                setTimeout(() => { card.style.opacity = "1"; card.style.transform = "scale(1)"; }, 10);
            } else {
                card.style.opacity = "0";
                card.style.transform = "scale(0.95)";
                setTimeout(() => { if(card.style.opacity === "0") card.style.display = "none"; }, 300);
            }
        });
    });

    // Funções do Modal (Mantenha as suas)
    function openModalPHP(src, nome, epi, hora, dataCompleta) {
        document.getElementById('modalImg').src = src;
        document.getElementById('modalName').innerText = nome;
        document.getElementById('modalDesc').innerText = "Infração: " + epi;
        document.getElementById('modalTime').innerText = "Horário: " + hora + " | Data: " + dataCompleta;
        document.getElementById('imageModal').classList.add('active');
    }
    function closeModal(event) { if (event.target.id === 'imageModal') forceClose(); }
    function forceClose() {
        document.getElementById('imageModal').classList.remove('active');
        document.getElementById('modalImg').src = "";
    } 
</script>
</body>
</html>