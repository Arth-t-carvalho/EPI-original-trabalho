<?php

namespace App\Infrastructure\Persistence;

class DashboardRepository
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getKpis(bool $isSuperAdmin, ?int $cursoId = null): array
    {
        $data = [];
        if ($isSuperAdmin) {
            $data['dia'] = $this->querySingleValue("SELECT COUNT(id) as total FROM ocorrencias WHERE data_hora >= CURDATE() AND data_hora < CURDATE() + INTERVAL 1 DAY");
            $data['semana'] = $this->querySingleValue("SELECT COUNT(id) as total FROM ocorrencias WHERE YEARWEEK(data_hora, 1) = YEARWEEK(CURDATE(), 1)");
            $data['mes'] = $this->querySingleValue("SELECT COUNT(id) as total FROM ocorrencias WHERE MONTH(data_hora) = MONTH(CURDATE()) AND YEAR(data_hora) = YEAR(CURDATE())");

            $data['ontem'] = $this->querySingleValue("SELECT COUNT(id) as total FROM ocorrencias WHERE data_hora >= CURDATE() - INTERVAL 1 DAY AND data_hora < CURDATE()");
            $data['semana_ant'] = $this->querySingleValue("SELECT COUNT(id) as total FROM ocorrencias WHERE YEARWEEK(data_hora, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)");
            $data['mes_ant'] = $this->querySingleValue("SELECT COUNT(id) as total FROM ocorrencias WHERE MONTH(data_hora) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(data_hora) = YEAR(CURDATE() - INTERVAL 1 MONTH)");

            $data['total_alunos'] = $this->querySingleValue("SELECT COUNT(*) as total FROM alunos");
            $data['alunos_infracao_hoje'] = $this->querySingleValue("SELECT COUNT(DISTINCT aluno_id) as total FROM ocorrencias WHERE data_hora >= CURDATE() AND data_hora < CURDATE() + INTERVAL 1 DAY");
        } else {
            $data['dia'] = $this->querySingleValueParam("SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() AND o.data_hora < CURDATE() + INTERVAL 1 DAY", "i", $cursoId);
            $data['semana'] = $this->querySingleValueParam("SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE(), 1)", "i", $cursoId);
            $data['mes'] = $this->querySingleValueParam("SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND MONTH(o.data_hora) = MONTH(CURDATE()) AND YEAR(o.data_hora) = YEAR(CURDATE())", "i", $cursoId);

            $data['ontem'] = $this->querySingleValueParam("SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() - INTERVAL 1 DAY AND o.data_hora < CURDATE()", "i", $cursoId);
            $data['semana_ant'] = $this->querySingleValueParam("SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)", "i", $cursoId);
            $data['mes_ant'] = $this->querySingleValueParam("SELECT COUNT(o.id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND MONTH(o.data_hora) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(o.data_hora) = YEAR(CURDATE() - INTERVAL 1 MONTH)", "i", $cursoId);

            $data['total_alunos'] = $this->querySingleValueParam("SELECT COUNT(*) as total FROM alunos WHERE curso_id = ?", "i", $cursoId);
            $data['alunos_infracao_hoje'] = $this->querySingleValueParam("SELECT COUNT(DISTINCT o.aluno_id) as total FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() AND o.data_hora < CURDATE() + INTERVAL 1 DAY", "i", $cursoId);
        }
        return $data;
    }

    public function getRanking(bool $isSuperAdmin, ?int $cursoId = null): array
    {
        if ($isSuperAdmin) {
            $sql = "SELECT c.nome, COUNT(o.id) AS total FROM ocorrencias o JOIN alunos a ON o.aluno_id = a.id JOIN cursos c ON a.curso_id = c.id GROUP BY c.id ORDER BY total DESC LIMIT 5";
            $res = mysqli_query($this->conn, $sql);
            return mysqli_fetch_all($res, MYSQLI_ASSOC);
        } else {
            $sql = "SELECT a.nome, COUNT(o.id) AS total FROM alunos a JOIN ocorrencias o ON a.id = o.aluno_id WHERE a.curso_id = ? GROUP BY a.id ORDER BY total DESC LIMIT 5";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $cursoId);
            mysqli_stmt_execute($stmt);
            return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        }
    }

    public function getCursos(): array
    {
        $res = mysqli_query($this->conn, "SELECT id, nome FROM cursos ORDER BY nome ASC");
        return mysqli_fetch_all($res, MYSQLI_ASSOC);
    }

    private function querySingleValue(string $sql): int
    {
        $res = mysqli_query($this->conn, $sql);
        return (int)(mysqli_fetch_assoc($res)['total'] ?? 0);
    }

    private function querySingleValueParam(string $sql, string $types, ...$params): int
    {
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        return (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'] ?? 0);
    }

    public function getChartData(bool $isSuperAdmin, ?int $cursoId = null): array
    {
        $labels = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
        $capacete = array_fill(0, 12, 0);
        $oculos = array_fill(0, 12, 0);
        $total = array_fill(0, 12, 0);

        $where = $isSuperAdmin ? "" : "WHERE a.curso_id = $cursoId";
        if ($isSuperAdmin && $cursoId) $where = "WHERE a.curso_id = $cursoId";

        $sql = "SELECT MONTH(o.data_hora) as mes, e.nome as epi, COUNT(o.id) as total
                FROM ocorrencias o
                JOIN alunos a ON o.aluno_id = a.id
                JOIN epis e ON o.epi_id = e.id
                $where
                GROUP BY mes, epi";

        $res = mysqli_query($this->conn, $sql);
        while ($row = mysqli_fetch_assoc($res)) {
            $m = (int)$row['mes'] - 1;
            $epi = strtolower($row['epi']);
            if (strpos($epi, 'capacete') !== false) $capacete[$m] = (int)$row['total'];
            if (strpos($epi, 'oculos') !== false) $oculos[$m] = (int)$row['total'];
            $total[$m] += (int)$row['total'];
        }

        return [
            'bar' => [
                'labels' => $labels,
                'capacete' => $capacete,
                'oculos' => $oculos,
                'total' => $total
            ],
            'doughnut' => [
                'labels' => ['Capacete', 'Óculos', 'Outros'],
                'data' => [array_sum($capacete), array_sum($oculos), max(0, array_sum($total) - array_sum($capacete) - array_sum($oculos))]
            ]
        ];
    }

    public function getCalendarData(int $month, int $year, ?int $cursoId = null): array
    {
        $sql = "SELECT o.id, a.nome as name, e.nome as `desc`, DATE_FORMAT(o.data_hora, '%H:%i') as time, o.data_hora 
                FROM ocorrencias o 
                JOIN alunos a ON o.aluno_id = a.id 
                JOIN epis e ON o.epi_id = e.id ";

        if ($cursoId) {
            $sql .= " WHERE a.curso_id = ? AND MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "iii", $cursoId, $month, $year);
        } else {
            $sql .= " WHERE MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $month, $year);
        }

        mysqli_stmt_execute($stmt);
        return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    }
}
