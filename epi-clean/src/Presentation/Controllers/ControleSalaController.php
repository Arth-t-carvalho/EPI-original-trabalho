<?php

namespace App\Presentation\Controllers;

class ControleSalaController
{
    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?route=login");
            exit;
        }

        $db = \App\Infrastructure\Persistence\Database::getInstance();
        $conn = $db->getConnection();

        require __DIR__ . '/../Views/controleSala.view.php';
    }
}
