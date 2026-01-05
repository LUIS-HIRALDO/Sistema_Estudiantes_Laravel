<?php
// Test simple para ver si el autoloader funciona correctamente
// y si hay errores antes de ejecutar el controlador

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Simular una petición HTTP GET a /calificaciones-academicas
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/calificaciones-academicas';

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST DE AUTOLOADING Y BOOTSTRAP                          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    echo "📋 PASO 1: Incluir autoloader...\n";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "   ✅ Autoloader OK\n\n";
    
    echo "📋 PASO 2: Incluir Database...\n";
    require_once __DIR__ . '/app/Database.php';
    echo "   ✅ Database OK\n\n";
    
    echo "📋 PASO 3: Incluir globals...\n";
    require_once __DIR__ . '/app/globals.php';
    echo "   ✅ Globals OK\n\n";
    
    echo "📋 PASO 4: Incluir helpers...\n";
    require_once __DIR__ . '/app/helpers.php';
    echo "   ✅ Helpers OK\n\n";
    
    echo "📋 PASO 5: Validar función response() ...\n";
    $result = response();
    echo "   ✅ response() existe y retorna: " . get_class($result) . "\n\n";
    
    echo "📋 PASO 6: Cargar Controller...\n";
    require_once __DIR__ . '/app/Controllers/Controller.php';
    echo "   ✅ Controller base cargado\n\n";
    
    echo "📋 PASO 7: Cargar CalificacionesAcademicasController...\n";
    require_once __DIR__ . '/app/Controllers/CalificacionesAcademicasController.php';
    echo "   ✅ CalificacionesAcademicasController cargado\n\n";
    
    echo "📋 PASO 8: Instanciar controlador...\n";
    $controller = new \App\Controllers\CalificacionesAcademicasController();
    echo "   ✅ Controlador instanciado\n\n";
    
    echo "📋 PASO 9: Verificar métodos disponibles...\n";
    $methods = get_class_methods($controller);
    echo "   ✅ Métodos encontrados:\n";
    foreach ($methods as $method) {
        if ($method !== '__construct' && strpos($method, '_') === false) {
            echo "      - $method\n";
        }
    }
    echo "\n";
    
    echo "✅ BOOTSTRAP COMPLETADO - SIN ERRORES\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR ENCONTRADO:\n";
    echo "   Tipo: " . get_class($e) . "\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    exit(1);
}
