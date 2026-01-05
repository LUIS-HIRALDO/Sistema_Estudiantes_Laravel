<?php

require_once __DIR__ . '/app/Database.php';

// Conectar a BD
$db = \App\Database::getInstance();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST ENDPOINT CALIFICACIONES ACADÉMICAS                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Obtener un estudiante ACADEMICA
    $sql = "SELECT id, nombre, apellido, matricula FROM estudiantes WHERE modalidad = 'ACADEMICA' LIMIT 1";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute();
    $est = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if (!$est) {
        echo "❌ No hay estudiantes ACADEMICA en BD\n";
        exit;
    }
    
    echo "Estudiante: {$est['nombre']} {$est['apellido']} (Mat: {$est['matricula']})\n\n";
    
    // Probar consulta de calificaciones
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
            WHERE na.id_estudiante = ?
              AND na.id_anio = ?
            LIMIT 5";
    
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([$est['id'], date('Y')]);
    $calificaciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    echo "✅ Consulta exitosa\n";
    echo "📊 Calificaciones encontradas: " . count($calificaciones) . "\n\n";
    
    if (count($calificaciones) > 0) {
        echo "Primeras 3:\n";
        foreach (array_slice($calificaciones, 0, 3) as $calif) {
            echo "  - {$calif['asignatura']} / {$calif['competencia']} (Bloque {$calif['bloque']}) = {$calif['nota']}\n";
        }
    }
    
    // Probar cálculo de bloque 70
    $sql = "SELECT AVG(CASE WHEN na.rp IS NOT NULL THEN na.rp ELSE na.nota END) as promedio
            FROM notas_academicas na
            JOIN competencias c ON na.id_competencia = c.id_competencia
            WHERE na.id_estudiante = ?
              AND c.id_asignatura = (SELECT id_asignatura FROM asignaturas LIMIT 1)
              AND na.id_periodo = ?
              AND na.id_anio = ?
              AND c.bloque = '70'";
    
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([$est['id'], 1, date('Y')]);
    $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    echo "\n✅ Cálculo Bloque 70 exitoso\n";
    echo "   Promedio: " . ($resultado['promedio'] ?? 'N/A') . "\n";
    echo "   Bloque 70: " . (round(($resultado['promedio'] ?? 0) * 0.70, 2)) . "\n";
    
    echo "\n✅ TODOS LOS TESTS PASADOS\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}
