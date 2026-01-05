<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Models/CalificacionTecnica.php';

use App\Models\CalificacionTecnica;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  SIMULAR EXACTAMENTE EL ENDPOINT DE TÉCNICAS              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Simular parámetros GET
    $_GET['id_anio'] = date('Y');
    
    $id_estudiante = $_GET['id_estudiante'] ?? null;
    $id_modulo = $_GET['id_modulo'] ?? null;
    $id_anio = $_GET['id_anio'] ?? date('Y');
    
    echo "Parámetros:\n";
    echo "  id_estudiante: " . ($id_estudiante ?? 'null') . "\n";
    echo "  id_modulo: " . ($id_modulo ?? 'null') . "\n";
    echo "  id_anio: $id_anio\n\n";
    
    // Paso 1: obtenerCalificaciones
    echo "1️⃣ Llamando CalificacionTecnica::obtenerCalificaciones()\n";
    $calificaciones = CalificacionTecnica::obtenerCalificaciones(
        $id_estudiante,
        $id_modulo,
        $id_anio
    );
    echo "   ✅ Registros obtenidos: " . count($calificaciones) . "\n";
    
    if (count($calificaciones) > 0) {
        echo "   Campos del primer registro:\n";
        foreach (array_keys($calificaciones[0]) as $campo) {
            echo "     - $campo\n";
        }
    }
    
    // Paso 2: Agrupar y calcular (como hace el controlador)
    echo "\n2️⃣ Agrupando y calculando (como en el controlador)\n";
    $resultado = [];
    $contador = 0;
    
    foreach ($calificaciones as $calif) {
        $contador++;
        echo "   Procesando registro $contador\n";
        
        $key = $calif['id_estudiante'] . '_' . $calif['id_modulo'];
        
        if (!isset($resultado[$key])) {
            // Calcular nota final
            echo "     - Calculando notaFinal...\n";
            $notaFinal = CalificacionTecnica::calcularNotaFinal(
                $calif['id_estudiante'],
                $calif['id_modulo'],
                $id_anio
            );
            echo "     - Calculando estado...\n";
            $estado = CalificacionTecnica::obtenerEstado($notaFinal);
            
            $resultado[$key] = [
                'id_estudiante' => $calif['id_estudiante'],
                'estudiante' => $calif['estudiante_nombre'] . ' ' . $calif['estudiante_apellido'],
                'matricula' => $calif['matricula'],
                'grado' => $calif['grado'],
                'especialidad' => $calif['especialidad'],
                'modulo' => $calif['modulo'],
                'codigo_modulo' => $calif['codigo_modulo'],
                'id_modulo' => $calif['id_modulo'],
                'ra' => [],
                'notaFinal' => $notaFinal,
                'estado' => $estado
            ];
        }
        
        // Aquí está el error - usa 'id' pero debería ser 'id_nota'
        $resultado[$key]['ra'][] = [
            'id' => $calif['id_nota'],  // CAMBIO AQUÍ
            'id_ra' => $calif['id_ra'],
            'numero_ra' => $calif['numero_ra'],
            'codigo_ra' => $calif['codigo_ra'],
            'descripcion' => $calif['descripcion'],
            'activo' => $calif['activo'],
            'porcentaje' => $calif['porcentaje'],
            'nota' => $calif['nota'],
            'rp' => $calif['rp'],
            'notaUsada' => $calif['rp'] ?? $calif['nota']
        ];
    }
    
    echo "\n3️⃣ Resultado final:\n";
    echo "   Registros agrupados: " . count($resultado) . "\n";
    
    // Paso 3: Convertir a JSON
    echo "\n4️⃣ Convirtiendo a JSON\n";
    $json = json_encode(array_values($resultado));
    
    if ($json === false) {
        echo "   ❌ ERROR en json_encode: " . json_last_error_msg() . "\n";
    } else {
        echo "   ✅ JSON válido\n";
        echo "   Tamaño: " . strlen($json) . " bytes\n";
    }
    
    echo "\n✅ PRUEBA COMPLETADA - SIN ERRORES\n";
    
} catch (\Exception $e) {
    echo "\n❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Código: " . $e->getCode() . "\n";
}
