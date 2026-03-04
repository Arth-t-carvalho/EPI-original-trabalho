<?php

namespace App\Presentation\Controllers;

class InfracoesController
{
    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?route=login");
            exit;
        }

        $db = \App\Infrastructure\Persistence\Database::getInstance();
        $conn = $db->getConnection();

        // Simulação de busca de infrações
        $infracoes = [];

        require __DIR__ . '/../Views/infracoes.view.php';
    }
}
