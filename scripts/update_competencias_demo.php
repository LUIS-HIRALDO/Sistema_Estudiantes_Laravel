<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    // 1. Obtener todas las asignaturas
    $stmt = $conn->query("SELECT id_asignatura, nombre FROM asignaturas");
    $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($asignaturas)) {
        die("No hay asignaturas registradas.");
    }

    // Competencias genéricas
    $competenciasGenericas = [
        ['nombre' => 'Primera competencia', 'bloque' => '70'],
        ['nombre' => 'Segunda competencia', 'bloque' => '70'],
        ['nombre' => 'Tercera competencia', 'bloque' => '70'],
        ['nombre' => 'Cuarta competencia', 'bloque' => '30']
    ];

    $stmtDeleteNotas = $conn->prepare("DELETE FROM notas_academicas WHERE id_competencia IN (SELECT id_competencia FROM competencias WHERE id_asignatura = ?)");
    $stmtDeleteComp = $conn->prepare("DELETE FROM competencias WHERE id_asignatura = ?");
    $stmtInsertComp = $conn->prepare("INSERT INTO competencias (id_asignatura, nombre, bloque, activo) VALUES (?, ?, ?, 1)");

    foreach ($asignaturas as $asignatura) {
        $idAsignatura = $asignatura['id_asignatura'];
        echo "Procesando asignatura: " . $asignatura['nombre'] . " (ID: $idAsignatura)...\n";

        // Borrar notas y competencias viejas
        $stmtDeleteNotas->execute([$idAsignatura]);
        $stmtDeleteComp->execute([$idAsignatura]);

        // Insertar nuevas competencias
        foreach ($competenciasGenericas as $comp) {
            $stmtInsertComp->execute([$idAsignatura, $comp['nombre'], $comp['bloque']]);
        }
    }

    $conn->commit();
    echo "Todas las asignaturas han sido actualizadas con las competencias genéricas.\n";

} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
