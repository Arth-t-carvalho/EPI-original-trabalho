<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\ObterDadosDashboard;

class DashboardController
{
    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?route=login");
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $cursoId = $_SESSION['usuario_id_curso'] ?? null;
        $isSuperAdmin = ($_SESSION['cargo'] === 'super_admin');

        $useCase = new ObterDadosDashboard();
        $dados = $useCase->executar($isSuperAdmin, $cursoId);

        // Injeção de $conn para compatibilidade com partes da View que ainda usam mysqli legado
        $db = \App\Infrastructure\Persistence\Database::getInstance();
        $conn = $db->getConnection();

        require __DIR__ . '/../Views/dashboard.view.php';
    }
}
