<?php

namespace App\Presentation\Controllers;

use App\Infrastructure\Persistence\Database;

class OcorrenciasController
{
    public function index()
    {
        $this->checkAuth();
        $conn = Database::getInstance()->getConnection();
        require __DIR__ . '/../Views/ocorrencias.view.php';
    }

    public function gestao()
    {
        $this->checkAuth();
        if ($_SESSION['cargo'] !== 'super_admin') {
            header("Location: index.php?route=dashboard");
            exit;
        }
        $conn = Database::getInstance()->getConnection();
        require __DIR__ . '/../Views/gestao_ocorrencias.view.php';
    }

    private function checkAuth()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?route=login");
            exit;
        }
    }
}
