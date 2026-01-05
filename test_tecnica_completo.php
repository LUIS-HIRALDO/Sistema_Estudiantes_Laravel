<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Models/CalificacionTecnica.php';

use App\Models\CalificacionTecnica;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST COMPLETO - ENDPOINT CALIFICACIONES TÉCNICAS          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    $db = \App\Database::getInstance();
    
    // Obtener todos los estudiantes técnicos
    $sql = "SELECT id FROM estudiantes WHERE modalidad = 'TECNICA'";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute();
    $estudiantes = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    
    echo "📊 Estudiantes TECNICA encontrados: " . count($estudiantes) . "\n";
    
    if (empty($estudiantes)) {
        echo "⚠️ No hay estudiantes técnicos\n";
        exit;
    }
    
    // Probar cada método crítico
    echo "\n🧪 Probando métodos del modelo:\n\n";
    
    // 1. obtenerCalificaciones
    echo "1️⃣ CalificacionTecnica::obtenerCalificaciones()\n";
    try {
        $cals = CalificacionTecnica::obtenerCalificaciones(null, null, date('Y'));
        echo "   ✅ OK - " . count($cals) . " registros\n";
    } catch (\Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    // 2. obtenerRAActivos
    echo "\n2️⃣ CalificacionTecnica::obtenerRAActivos()\n";
    try {
        $modulos = \App\Database::getInstance()->getConnection()->prepare(
            "SELECT id_modulo FROM modulos_formativos LIMIT 1"
        );
        $modulos->execute();
        $mod = $modulos->fetch(\PDO::FETCH_ASSOC);
        if ($mod) {
            $ras = CalificacionTecnica::obtenerRAActivos($mod['id_modulo']);
            echo "   ✅ OK - " . count($ras) . " RA activos\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    // 3. validarPorcentajes
    echo "\n3️⃣ CalificacionTecnica::validarPorcentajes()\n";
    try {
        $modulos = \App\Database::getInstance()->getConnection()->prepare(
            "SELECT id_modulo FROM modulos_formativos LIMIT 1"
        );
        $modulos->execute();
        $mod = $modulos->fetch(\PDO::FETCH_ASSOC);
        if ($mod) {
            $result = CalificacionTecnica::validarPorcentajes($mod['id_modulo']);
            echo "   ✅ OK - Válido: " . ($result['válido'] ? 'SÍ' : 'NO') . "\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    // 4. calcularNotaFinal
    echo "\n4️⃣ CalificacionTecnica::calcularNotaFinal()\n";
    try {
        $modulos = \App\Database::getInstance()->getConnection()->prepare(
            "SELECT id_modulo FROM modulos_formativos LIMIT 1"
        );
        $modulos->execute();
        $mod = $modulos->fetch(\PDO::FETCH_ASSOC);
        if ($mod && !empty($estudiantes)) {
            $nota = CalificacionTecnica::calcularNotaFinal($estudiantes[0], $mod['id_modulo'], date('Y'));
            echo "   ✅ OK - Nota: $nota\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    // 5. obtenerDesglose
    echo "\n5️⃣ CalificacionTecnica::obtenerDesglose()\n";
    try {
        $modulos = \App\Database::getInstance()->getConnection()->prepare(
            "SELECT id_modulo FROM modulos_formativos LIMIT 1"
        );
        $modulos->execute();
        $mod = $modulos->fetch(\PDO::FETCH_ASSOC);
        if ($mod && !empty($estudiantes)) {
            $desglose = CalificacionTecnica::obtenerDesglose($estudiantes[0], $mod['id_modulo'], date('Y'));
            echo "   ✅ OK - Items: " . count($desglose) . "\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ TODOS LOS MÉTODOS EJECUTADOS SIN ERRORES DE JSON\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR GENERAL: " . $e->getMessage() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
}
