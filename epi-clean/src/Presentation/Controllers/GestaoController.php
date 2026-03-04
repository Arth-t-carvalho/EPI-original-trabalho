<?php

namespace App\Presentation\Controllers;

class GestaoController
{
    public function alunos()
    {
        $this->checkAdmin();
        $db = \App\Infrastructure\Persistence\Database::getInstance();
        $conn = $db->getConnection();
        require __DIR__ . '/../Views/gestao_alunos.view.php';
    }

    public function cursos()
    {
        $this->checkAdmin();
        $db = \App\Infrastructure\Persistence\Database::getInstance();
        $conn = $db->getConnection();
        require __DIR__ . '/../Views/gestao_cursos.view.php';
    }

    public function professores()
    {
        $this->checkAdmin();
        $db = \App\Infrastructure\Persistence\Database::getInstance();
        $conn = $db->getConnection();
        require __DIR__ . '/../Views/gestao_professores.view.php';
    }

    private function checkAdmin()
    {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['cargo'] !== 'super_admin') {
            header("Location: index.php?route=dashboard");
            exit;
        }
    }
}
