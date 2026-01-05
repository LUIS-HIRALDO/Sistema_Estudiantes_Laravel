<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';

$db = \App\Database::getInstance();
$conn = $db->getConnection();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST SQL SELECT DE CALIFICACIONES ACADÉMICAS              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$sql = "SELECT 
            na.id_nota,
            na.id_estudiante,
            na.id_competencia,
            na.id_periodo,
            na.nota,
            na.rp,
            c.nombre as competencia,
            c.bloque,
            a.nombre as asignatura,
            a.codigo as codigo_asignatura,
            e.nombre as estudiante_nombre,
            e.apellido as estudiante_apellido,
            e.matricula,
            p.nombre as periodo_nombre,
            e.grado
        FROM notas_academicas na
        JOIN competencias c ON na.id_competencia = c.id_competencia
        JOIN asignaturas a ON c.id_asignatura = a.id_asignatura
        JOIN estudiantes e ON na.id_estudiante = e.id
        JOIN periodos p ON na.id_periodo = p.id_periodo
        WHERE na.id_anio = ?
        ORDER BY e.nombre, a.nombre, c.bloque, na.id_periodo";

try {
    echo "SQL a ejecutar:\n$sql\n\n";
    
    echo "Preparando statement...\n";
    $stmt = $conn->prepare($sql);
    
    echo "Ejecutando con parámetro: " . date('Y') . "\n";
    $result = $stmt->execute([date('Y')]);
    
    if ($result) {
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        echo "✅ Query ejecutada exitosamente\n";
        echo "✅ Registros obtenidos: " . count($rows) . "\n\n";
        
        if (count($rows) > 0) {
            echo "Primer registro:\n";
            var_dump($rows[0]);
        }
    } else {
        echo "❌ Error ejecutando query\n";
        var_dump($stmt->errorInfo());
    }
    
} catch (\Exception $e) {
    echo "❌ EXCEPCIÓN:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
