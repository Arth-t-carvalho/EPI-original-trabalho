<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\RegisterUseCase;

class AuthController
{
    public function showLogin()
    {
        if (isset($_SESSION['usuario_id'])) {
            header("Location: index.php?route=dashboard");
            exit;
        }
        require __DIR__ . '/../Views/login.view.php';
    }

    public function login()
    {
        $usuario = $_POST['usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $useCase = new LoginUseCase();
        $user = $useCase->execute($usuario, $senha);

        if ($user) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['cargo'] = $user['cargo'];
            $_SESSION['usuario_id_curso'] = $user['curso_id'];

            header("Location: index.php?route=dashboard");
            exit;
        }

        header("Location: index.php?route=login&erro=1");
        exit;
    }

    public function logout()
    {
        session_destroy();
        header("Location: index.php?route=login");
        exit;
    }

    public function showRegister()
    {
        require __DIR__ . '/../Views/cadastro.view.php';
    }

    public function register()
    {
        $nome = $_POST['nome'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $useCase = new RegisterUseCase();
        $result = $useCase->execute($nome, $usuario, $senha);

        if ($result === 'success') {
            header("Location: index.php?route=register&sucesso=1");
        } else {
            header("Location: index.php?route=register&erro=" . $result);
        }
        exit;
    }
}
