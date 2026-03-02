<?php
require_once 'database.php';

echo "--- Verificação de KPIs e Conformidade ---\n";

// Total de Alunos
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM alunos");
$totalAlunos = mysqli_fetch_assoc($res)['total'];
echo "Total de Alunos: $totalAlunos\n";

// Infrações Hoje
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM ocorrencias WHERE DATE(data_hora) = CURDATE()");
$infraHoje = mysqli_fetch_assoc($res)['total'];
echo "Infrações Hoje (Geral): $infraHoje\n";

// Alunos com Infração Hoje
$res = mysqli_query($conn, "SELECT COUNT(DISTINCT aluno_id) as total FROM ocorrencias WHERE DATE(data_hora) = CURDATE()");
$alunosInfratoresHoje = mysqli_fetch_assoc($res)['total'];
echo "Alunos com Infração Hoje: $alunosInfratoresHoje\n";

// Cálculo Conformidade
if ($totalAlunos > 0) {
    $conformidade = (($totalAlunos - $alunosInfratoresHoje) / $totalAlunos) * 100;
    echo "Conformidade Calculada: " . round($conformidade, 2) . "%\n";
}
else {
    echo "Erro: Nenhum aluno cadastrado.\n";
}

// Infrações Semana
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM ocorrencias WHERE YEARWEEK(data_hora, 1) = YEARWEEK(CURDATE(), 1)");
$infraSemana = mysqli_fetch_assoc($res)['total'];
echo "Infrações na Semana: $infraSemana\n";

// Infrações Mês
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM ocorrencias WHERE MONTH(data_hora) = MONTH(CURDATE()) AND YEAR(data_hora) = YEAR(CURDATE())");
$infraMes = mysqli_fetch_assoc($res)['total'];
echo "Infrações no Mês: $infraMes\n";
?>
