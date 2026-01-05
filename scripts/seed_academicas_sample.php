<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Database.php';

$yearId = (int) date('Y');
$anioLabel = $yearId . '-' . ($yearId + 1);

$conn = \App\Database::getInstance()->getConnection();

try {
    $conn->beginTransaction();

    $stmtYear = $conn->prepare(
        'INSERT INTO anios_escolares (id_anio, anio, activo) VALUES (?, ?, 1)
         ON DUPLICATE KEY UPDATE anio = VALUES(anio), activo = VALUES(activo)'
    );
    $stmtYear->execute([$yearId, $anioLabel]);

    $notas = [
        1 => ['nota' => 85.50, 'rp' => null],
        2 => ['nota' => 90.00, 'rp' => null],
        3 => ['nota' => 88.00, 'rp' => null],
    ];

    $stmtNota = $conn->prepare(
        'INSERT INTO notas_academicas (id_estudiante, id_competencia, id_periodo, nota, rp, id_anio, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
         ON DUPLICATE KEY UPDATE nota = VALUES(nota), rp = VALUES(rp), updated_at = NOW()'
    );

    foreach ($notas as $idCompetencia => $valores) {
        $stmtNota->execute([1, $idCompetencia, 1, $valores['nota'], $valores['rp'], $yearId]);
    }

    $conn->commit();
    echo "✓ Calificaciones de muestra registradas para el estudiante 1 (anio {$yearId})\n";
} catch (Throwable $e) {
    $conn->rollBack();
    echo '✗ Error insertando datos: ' . $e->getMessage() . "\n";
    exit(1);
}
