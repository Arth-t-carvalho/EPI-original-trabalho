<?php

namespace App\Presentation\Controllers;

use App\Infrastructure\Persistence\DashboardRepository;

class ApiController
{
    private $repository;

    public function __construct()
    {
        $this->repository = new DashboardRepository();
    }

    public function handle()
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';
        $isSuperAdmin = ($_SESSION['cargo'] === 'super_admin');
        $cursoId = (isset($_SESSION['usuario_id_curso'])) ? (int)$_SESSION['usuario_id_curso'] : null;

        // Sobrescrita de curso se for Super Admin e vier no GET
        if ($isSuperAdmin && isset($_GET['course_id']) && $_GET['course_id'] !== 'all') {
            $cursoId = (int)$_GET['course_id'];
        }

        switch ($action) {
            case 'charts':
                echo json_encode($this->repository->getChartData($isSuperAdmin, $cursoId));
                break;
            case 'calendar':
                $month = (int)($_GET['month'] ?? date('m'));
                $year = (int)($_GET['year'] ?? date('Y'));
                echo json_encode($this->repository->getCalendarData($month, $year, $cursoId));
                break;
            case 'modal_details':
                // Simple implementation for demo
                $month = (int)($_GET['month'] ?? date('m'));
                $year = (int)($_GET['year'] ?? date('Y'));
                $data = $this->repository->getCalendarData($month, $year, $cursoId);
                // Map to expected format
                $mapped = array_map(function ($item) {
                    return [
                        'data' => date('d/m/Y', strtotime($item['data_hora'])),
                        'aluno' => $item['name'],
                        'epis' => $item['desc'],
                        'hora' => $item['time'],
                        'status_formatado' => 'Pendente',
                        'ocorrencia_id' => $item['id'],
                        'aluno_id' => 0 // Mock
                    ];
                }, $data);
                echo json_encode($mapped);
                break;
            case 'notificacoes':
                $lastId = (int)($_GET['last_id'] ?? 0);
                // Mock notification or real query
                echo json_encode(['status' => 'success', 'dados' => []]);
                break;
            default:
                echo json_encode(['error' => 'Action not found']);
                break;
        }
    }
}
