<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Models/CalificacionTecnica.php';

use App\Models\CalificacionTecnica;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST MÉTODO CalificacionTecnica::obtenerCalificaciones    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Obtener un estudiante técnico
    $db = \App\Database::getInstance();
    $sql = "SELECT id FROM estudiantes WHERE modalidad = 'TECNICA' LIMIT 1";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute();
    $est = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if (!$est) {
        echo "❌ No hay estudiantes TECNICA\n";
        exit;
    }
    
    echo "Obteniendo calificaciones para estudiante ID: {$est['id']}\n\n";
    
    // Llamar el método
    $calificaciones = CalificacionTecnica::obtenerCalificaciones(
        $est['id'],
        null,
        date('Y')
    );
    
    echo "✅ Método ejecutado exitosamente\n";
    echo "📊 Total registros: " . count($calificaciones) . "\n\n";
    
    if (!empty($calificaciones)) {
        echo "Primeros 3 registros:\n";
        foreach (array_slice($calificaciones, 0, 3) as $calif) {
            echo "  - {$calif['modulo']} / RA: {$calif['codigo_ra']} = {$calif['nota']}\n";
        }
    } else {
        echo "⚠️ El estudiante no tiene calificaciones registradas\n";
    }
    
    echo "\n✅ PRUEBA EXITOSA - Sin errores JSON\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Tipo: " . get_class($e) . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
}
