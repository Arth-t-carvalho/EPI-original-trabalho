<?php

namespace App\Application\UseCases;

/**
 * DTO para transporte de dados da Dashboard para a View.
 * Garante tipagem e evita lógica de negócio dentro do template.
 */
class DashboardDadosDTO
{
    public array $kpis;
    public array $rankingCursos;
    public int $conformidade;
    public float $percDia;
    public float $percSemana;
    public float $percMes;
    public array $cursosParaFiltro;

    public function __construct(array $dados)
    {
        $this->kpis = $dados['kpis'] ?? [];
        $this->rankingCursos = $this->prepararRanking($dados['ranking'] ?? []);
        $this->conformidade = (int)($dados['conformidade'] ?? 0);
        $this->percDia = (float)($dados['percDia'] ?? 0);
        $this->percSemana = (float)($dados['percSemana'] ?? 0);
        $this->percMes = (float)($dados['percMes'] ?? 0);
        $this->cursosParaFiltro = $dados['cursos'] ?? [];
    }

    /**
     * Prepara os dados do ranking, calculando a porcentagem para a barra de progresso visual.
     */
    private function prepararRanking(array $rankingRaw): array
    {
        if (empty($rankingRaw)) return [];

        // Valor máximo para normalização das barras de progresso
        $maxTotal = 0;
        foreach ($rankingRaw as $item) {
            if ($item['total'] > $maxTotal) $maxTotal = $item['total'];
        }

        return array_map(function ($item) use ($maxTotal) {
            $item['porcentagem'] = ($maxTotal > 0) ? round(($item['total'] / $maxTotal) * 100) : 0;
            return $item;
        }, $rankingRaw);
    }
}
