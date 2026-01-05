<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Models/CalificacionAcademica.php';

use App\Models\CalificacionAcademica;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST MÉTODO CalificacionAcademica::obtenerCalificaciones  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Obtener un estudiante
    $db = \App\Database::getInstance();
    $sql = "SELECT id FROM estudiantes WHERE modalidad = 'ACADEMICA' LIMIT 1";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute();
    $est = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if (!$est) {
        echo "❌ No hay estudiantes ACADEMICA\n";
        exit;
    }
    
    echo "Obteniendo calificaciones para estudiante ID: {$est['id']}\n\n";
    
    // Llamar el método
    $calificaciones = CalificacionAcademica::obtenerCalificaciones(
        $est['id'],
        null,
        null,
        date('Y')
    );
    
    echo "✅ Método ejecutado exitosamente\n";
    echo "📊 Total registros: " . count($calificaciones) . "\n\n";
    
    if (!empty($calificaciones)) {
        echo "Primeros 3 registros:\n";
        foreach (array_slice($calificaciones, 0, 3) as $calif) {
            echo "  - {$calif['asignatura']} / {$calif['competencia']} = {$calif['nota']}\n";
        }
    } else {
        echo "⚠️ El estudiante no tiene calificaciones registradas\n";
    }
    
    echo "\n✅ PRUEBA EXITOSA - Sin errores JSON\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Tipo: " . get_class($e) . "\n";
}
