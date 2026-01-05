<?php
// Test detallado del endpoint de calificaciones académicas
// para identificar errores específicos

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Models/CalificacionAcademica.php';
require_once __DIR__ . '/app/globals.php';
require_once __DIR__ . '/app/helpers.php';

use App\Models\CalificacionAcademica;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST ENDPOINT CALIFICACIONES ACADÉMICAS                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Simular parámetros GET
    $_GET['id_estudiante'] = null;
    $_GET['id_periodo'] = null;
    $_GET['id_asignatura'] = null;
    $_GET['id_anio'] = date('Y');
    
    $id_anio = $_GET['id_anio'] ?? date('Y');
    
    echo "📋 PASO 1: Obtener calificaciones...\n";
    
    $calificaciones = CalificacionAcademica::obtenerCalificaciones(
        null, // id_estudiante
        null, // id_periodo
        null, // id_asignatura
        $id_anio
    );
    
    echo "   ✅ Se obtuvieron: " . count($calificaciones) . " registros\n\n";
    
    // Procesar y agrupar
    echo "📋 PASO 2: Agrupar por estudiante, período y asignatura...\n";
    
    $resultado = [];
    $contador = 0;
    
    foreach ($calificaciones as $calif) {
        $contador++;
        
        echo "   [$contador] Estudiante: {$calif['id_estudiante']}, Asignatura: {$calif['id_asignatura']}\n";
        
        $key = $calif['id_estudiante'] . '_' . $calif['id_periodo'] . '_' . $calif['id_asignatura'];
        
        if (!isset($resultado[$key])) {
            echo "       → Creando grupo: $key\n";
            
            // Calcular nota final
            echo "       → Calculando nota final...\n";
            $notaFinal = CalificacionAcademica::calcularNotaFinal(
                $calif['id_estudiante'],
                $calif['id_asignatura'],
                $calif['id_periodo'],
                $id_anio
            );
            
            echo "       → Calculando bloque 70%...\n";
            $bloque70 = CalificacionAcademica::calcularBloque70(
                $calif['id_estudiante'],
                $calif['id_asignatura'],
                $calif['id_periodo'],
                $id_anio
            );
            
            echo "       → Calculando bloque 30%...\n";
            $bloque30 = CalificacionAcademica::calcularBloque30(
                $calif['id_estudiante'],
                $calif['id_asignatura'],
                $calif['id_periodo'],
                $id_anio
            );
            
            echo "       → Obteniendo estado...\n";
            $estado = CalificacionAcademica::obtenerEstado($notaFinal);
            
            $resultado[$key] = [
                'id_estudiante' => $calif['id_estudiante'],
                'estudiante' => $calif['estudiante_nombre'] . ' ' . $calif['estudiante_apellido'],
                'matricula' => $calif['matricula'],
                'grado' => $calif['grado'],
                'asignatura' => $calif['asignatura'],
                'codigo_asignatura' => $calif['codigo_asignatura'],
                'periodo' => $calif['periodo_nombre'],
                'id_periodo' => $calif['id_periodo'],
                'notas' => [],
                'notaFinal' => $notaFinal,
                'estado' => $estado,
                'bloque70' => $bloque70,
                'bloque30' => $bloque30
            ];
        }
        
        echo "       → Agregando nota competencia...\n";
        $resultado[$key]['notas'][] = [
            'id' => $calif['id'],
            'id_competencia' => $calif['id_competencia'],
            'competencia' => $calif['competencia'],
            'bloque' => $calif['bloque'],
            'nota' => $calif['nota'],
            'rp' => $calif['rp'],
            'notaUsada' => $calif['rp'] ?? $calif['nota']
        ];
    }
    
    echo "\n   ✅ Se agruparon en: " . count($resultado) . " registros\n\n";
    
    // Convertir a array
    echo "📋 PASO 3: Convertir a array indexado...\n";
    $resultado_final = array_values($resultado);
    echo "   ✅ Array final tiene: " . count($resultado_final) . " elementos\n\n";
    
    // Serializar a JSON
    echo "📋 PASO 4: Serializar a JSON...\n";
    $json = json_encode($resultado_final);
    
    if ($json === false) {
        echo "   ❌ ERROR: " . json_last_error_msg() . "\n";
        exit(1);
    }
    
    echo "   ✅ JSON válido, tamaño: " . strlen($json) . " bytes\n";
    echo "   ✅ Primeros 200 caracteres:\n";
    echo "      " . substr($json, 0, 200) . "...\n\n";
    
    echo "✅ PRUEBA COMPLETADA - SIN ERRORES\n";
    echo "El endpoint debería devolver este JSON correctamente al frontend.\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR ENCONTRADO:\n";
    echo "   Tipo: " . get_class($e) . "\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    echo "   Trace:\n";
    
    $trace = $e->getTrace();
    foreach ($trace as $i => $frame) {
        echo "   [$i] " . ($frame['file'] ?? 'desconocido') . ":" . ($frame['line'] ?? '?') . "\n";
        echo "       → " . ($frame['function'] ?? 'función') . "\n";
    }
    
    exit(1);
}
