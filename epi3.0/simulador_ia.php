<?php
/**
 * SCRIPT DE SIMULAÇÃO DE EVENTOS DE IA (CONFORMIDADE / INFRAÇÃO)
 * Use este script para testar o "Controle de Sala" em tempo real.
 */

require_once __DIR__ . '/config/database.php';

// Parâmetros via GET
$alunoId = (int)($_GET['aluno_id'] ?? 0);
$epiId = (int)($_GET['epi_id'] ?? 1); // 1: Óculos, 2: Capacete
$tipo = (int)($_GET['tipo'] ?? 0); // 0: Infração (Não Conforme), 1: Conformidade (EPI colocado)

if ($alunoId <= 0) {
    die("Uso: simulador_ia.php?aluno_id=X&epi_id=Y&tipo=Z\n(tipo 0 = infração, tipo 1 = conformidade)");
}

$sql = "INSERT INTO ocorrencias (aluno_id, epi_id, tipo, data_hora) VALUES (?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iii", $alunoId, $epiId, $tipo);

if (mysqli_stmt_execute($stmt)) {
    $msg = ($tipo == 0) ? "INFRAÇÃO registrada" : "CONFORMIDADE registrada";
    echo "Sucesso: $msg para o aluno ID $alunoId no EPI ID $epiId.";
}
else {
    echo "Erro ao registrar evento: " . mysqli_error($conn);
}
?>
