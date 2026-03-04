<?php

namespace App\Presentation\Controllers;

class SettingsController
{
    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?route=login");
            exit;
        }

        $db = \App\Infrastructure\Persistence\Database::getInstance();
        $conn = $db->getConnection();

        require __DIR__ . '/../Views/configuracoes.view.php';
    }
}
