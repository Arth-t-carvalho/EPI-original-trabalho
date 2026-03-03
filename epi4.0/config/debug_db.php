<?php
require_once 'database.php';
$tables = ['usuarios', 'alunos', 'ocorrencias', 'cursos'];
foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    $res = mysqli_query($conn, "DESCRIBE $table");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    }
    else {
        echo "Error describing $table: " . mysqli_error($conn) . "\n";
    }
    echo "\n";
}
?>
