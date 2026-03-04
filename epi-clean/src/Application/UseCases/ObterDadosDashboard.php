<?php

namespace App\Application\UseCases;

use App\Infrastructure\Persistence\DashboardRepository;

class ObterDadosDashboard
{
    private $repository;

    public function __construct()
    {
        $this->repository = new DashboardRepository();
    }

    public function executar(bool $isSuperAdmin, ?int $cursoId = null): DashboardDadosDTO
    {
        $kpis = $this->repository->getKpis($isSuperAdmin, $cursoId);
        $ranking = $this->repository->getRanking($isSuperAdmin, $cursoId);

        // Cálculo de Conformidade
        $totalAlunos = (int)($kpis['total_alunos'] ?? 0);
        $alunosHoje = (int)($kpis['alunos_infracao_hoje'] ?? 0);

        if ($totalAlunos === 0) {
            $conformidade = 100;
        } else {
            $conformidade = round((($totalAlunos - $alunosHoje) / $totalAlunos) * 100);
        }
        $conformidade = (int)max(0, min(100, $conformidade));

        // Porcentagens (Comparativo com período anterior)
        $percDia = $this->calcPerc($kpis['dia'], $kpis['ontem']);
        $percSemana = $this->calcPerc($kpis['semana'], $kpis['semana_ant']);
        $percMes = $this->calcPerc($kpis['mes'], $kpis['mes_ant']);

        return new DashboardDadosDTO([
            'kpis' => $kpis,
            'ranking' => $ranking,
            'conformidade' => $conformidade,
            'percDia' => $percDia,
            'percSemana' => $percSemana,
            'percMes' => $percMes,
            'cursos' => $isSuperAdmin ? $this->repository->getCursos() : []
        ]);
    }

    private function calcPerc(int $atual, int $anterior): float
    {
        if ($anterior > 0) {
            return round((($atual - $anterior) / $anterior) * 100, 1);
        }
        return $atual > 0 ? 100 : 0;
    }
}
