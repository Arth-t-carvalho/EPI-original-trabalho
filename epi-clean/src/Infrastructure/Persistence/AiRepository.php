<?php

namespace App\Infrastructure\Persistence;

use mysqli;

class AiRepository
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAiData(): array
    {
        $data = [
            'epis' => [],
            'alunos' => [],
            'tempo' => [],
            'resumo' => [],
            'sancoes' => [],
            'turnos' => [],
            'supervisores' => [],
            'cursos' => []
        ];

        if (!$this->conn) return $data;

        // 1. RANKING DE EPIs
        $res = $this->conn->query("SELECT e.nome, COUNT(o.id) as total_ocorrencias FROM epis e LEFT JOIN ocorrencias o ON e.id = o.epi_id GROUP BY e.id, e.nome ORDER BY total_ocorrencias DESC");
        while ($row = $res->fetch_assoc()) $data['epis'][] = $row;

        // 2. RANKING DE ALUNOS
        $res = $this->conn->query("SELECT a.nome, COALESCE(c.nome, 'Sem Curso') as curso, COUNT(o.id) as total_infracoes FROM alunos a LEFT JOIN cursos c ON a.curso_id = c.id LEFT JOIN ocorrencias o ON a.id = o.aluno_id GROUP BY a.id, a.nome, c.nome ORDER BY total_infracoes DESC");
        while ($row = $res->fetch_assoc()) $data['alunos'][] = $row;

        // 3. BUSCA OCORRÊNCIAS POR DIA
        $res = $this->conn->query("SELECT DATE(data_hora) as data, COUNT(id) as total FROM ocorrencias WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(data_hora) ORDER BY data ASC");

        $total30 = 0;
        $max = ['data' => 'N/A', 'total' => 0];
        $min = ['data' => 'N/A', 'total' => 999];

        while ($row = $res->fetch_assoc()) {
            $data['tempo'][] = $row;
            $total30 += $row['total'];
            if ($row['total'] > $max['total']) $max = $row;
            if ($row['total'] < $min['total']) $min = $row;
        }

        $data['resumo'] = [
            'total_30_dias' => $total30,
            'media_diaria' => count($data['tempo']) > 0 ? round($total30 / count($data['tempo']), 2) : 0,
            'dia_com_mais_infracoes' => $max,
            'dia_com_menos_infracoes' => ($min['total'] == 999) ? ['data' => 'N/A', 'total' => 0] : $min
        ];

        // 4. SANÇÕES
        $res = $this->conn->query("SELECT tipo, COUNT(id) as total FROM acoes_ocorrencia GROUP BY tipo ORDER BY total DESC");
        while ($row = $res->fetch_assoc()) $data['sancoes'][] = $row;

        // 5. TURNOS
        $res = $this->conn->query("SELECT turno, COUNT(id) as total FROM alunos WHERE turno IS NOT NULL GROUP BY turno ORDER BY total DESC");
        while ($row = $res->fetch_assoc()) $data['turnos'][] = $row;

        // 6. SUPERVISORES
        $res = $this->conn->query("SELECT u.nome, u.cargo, COUNT(ao.id) as total_acoes FROM usuarios u JOIN acoes_ocorrencia ao ON u.id = ao.usuario_id GROUP BY u.id, u.nome, u.cargo ORDER BY total_acoes DESC");
        while ($row = $res->fetch_assoc()) $data['supervisores'][] = $row;

        // 7. CURSOS
        $res = $this->conn->query("SELECT c.nome as curso, COUNT(o.id) as total_infracoes FROM cursos c JOIN alunos a ON c.id = a.curso_id JOIN ocorrencias o ON a.id = o.aluno_id GROUP BY c.id, c.nome ORDER BY total_infracoes DESC");
        while ($row = $res->fetch_assoc()) $data['cursos'][] = $row;

        return $data;
    }
}
