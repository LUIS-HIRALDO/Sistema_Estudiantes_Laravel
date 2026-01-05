<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Models/CalificacionTecnica.php';

use App\Models\CalificacionTecnica;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST DE MÉTODOS QUE PUEDEN GENERAR ERRORES               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // 1. cerrarModulo
    echo "1️⃣ CalificacionTecnica::cerrarModulo()\n";
    try {
        $db = \App\Database::getInstance();
        $modulo = $db->getConnection()->prepare("SELECT id_modulo FROM modulos_formativos LIMIT 1");
        $modulo->execute();
        $mod = $modulo->fetch(\PDO::FETCH_ASSOC);
        
        if ($mod) {
            $result = CalificacionTecnica::cerrarModulo($mod['id_modulo'], date('Y'), 1);
            echo "   ✅ OK - " . ($result['éxito'] ? 'Exitoso' : 'No ejecutado') . "\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    // 2. guardarCalificaciones (intentar con datos válidos)
    echo "\n2️⃣ CalificacionTecnica::guardarCalificaciones()\n";
    try {
        $db = \App\Database::getInstance();
        $est = $db->getConnection()->prepare("SELECT id FROM estudiantes WHERE modalidad = 'TECNICA' LIMIT 1");
        $est->execute();
        $estudiante = $est->fetch(\PDO::FETCH_ASSOC);
        
        if ($estudiante) {
            $ras = CalificacionTecnica::obtenerRAActivos(1); // Módulo 1
            $notas = [];
            foreach ($ras as $ra) {
                $notas[$ra['id_ra']] = ['nota' => 80, 'rp' => null];
            }
            
            $result = CalificacionTecnica::guardarCalificaciones($estudiante['id'], 1, date('Y'), $notas);
            echo "   ✅ OK - " . ($result['éxito'] ? 'Guardado' : 'Error: ' . $result['mensaje']) . "\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ TODOS LOS TESTS COMPLETADOS\n";
    
} catch (\Exception $e) {
    echo "\n❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
}
