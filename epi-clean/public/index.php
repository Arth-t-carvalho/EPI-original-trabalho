<?php

require_once __DIR__ . '/../src/autoload.php';

use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\DashboardController;

session_start();

// Rota padrão: Se logado vai para dashboard, se não vai para login
$route = $_GET['route'] ?? (isset($_SESSION['usuario_id']) ? 'dashboard' : 'login');

switch ($route) {
    case 'login':
        (new AuthController())->showLogin();
        break;
    case 'login-process':
        (new AuthController())->login();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'register':
        (new AuthController())->showRegister();
        break;
    case 'register-process':
        (new AuthController())->register();
        break;
    case 'dashboard':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?route=login");
            exit;
        }
        (new DashboardController())->index();
        break;
    case 'infracoes':
        (new \App\Presentation\Controllers\InfracoesController())->index();
        break;
    case 'monitoramento':
        (new \App\Presentation\Controllers\MonitoramentoController())->index();
        break;
    case 'controle-sala':
        (new \App\Presentation\Controllers\ControleSalaController())->index();
        break;
    case 'gestao-alunos':
        (new \App\Presentation\Controllers\GestaoController())->alunos();
        break;
    case 'gestao-cursos':
        (new \App\Presentation\Controllers\GestaoController())->cursos();
        break;
    case 'gestao-professores':
        (new \App\Presentation\Controllers\GestaoController())->professores();
        break;
    case 'gestao-ocorrencias':
        (new \App\Presentation\Controllers\OcorrenciasController())->gestao();
        break;
    case 'ocorrencias':
        (new \App\Presentation\Controllers\OcorrenciasController())->index();
        break;
    case 'configuracoes':
        (new \App\Presentation\Controllers\SettingsController())->index();
        break;
    case 'api':
        (new \App\Presentation\Controllers\ApiController())->handle();
        break;
    default:
        header("Location: index.php?route=login");
        break;
}
