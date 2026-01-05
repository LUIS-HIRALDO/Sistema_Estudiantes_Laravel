<?php

require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Controllers/CalificacionesAcademicasController.php';

use App\Controllers\CalificacionesAcademicasController;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST DE CONTROLADOR - CALIFICACIONES ACADÉMICAS           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Simular petición GET
    $_GET['id_anio'] = date('Y');
    
    // Mock de autenticación
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test-token';
    
    $controller = new CalificacionesAcademicasController();
    
    // Capturar salida
    ob_start();
    
    // Llamar índice (obtener todos)
    $controller->index();
    
    $output = ob_get_clean();
    
    echo "✅ Respuesta del controlador (JSON):\n\n";
    
    // Validar que es JSON válido
    $json = json_decode($output, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON válido\n";
        echo "   Total registros: " . count($json) . "\n";
        if (!empty($json)) {
            echo "   Campos: " . implode(', ', array_keys($json[0])) . "\n";
            echo "\n   Primer registro:\n";
            echo "   - Estudiante: {$json[0]['estudiante']}\n";
            echo "   - Asignatura: {$json[0]['asignatura']}\n";
            echo "   - Nota Final: {$json[0]['notaFinal']}\n";
            echo "   - Estado: {$json[0]['estado']}\n";
        }
    } else {
        echo "❌ JSON inválido: " . json_last_error_msg() . "\n";
        echo "Salida: " . substr($output, 0, 200) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}
