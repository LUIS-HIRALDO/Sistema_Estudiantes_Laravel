<?php
// Simular la llamada al endpoint sin autenticación
// para ver qué error devuelve

error_reporting(E_ALL);
ini_set('display_errors', '0');  // No mostrar en pantalla
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/php_error.log');

// Simular headers como si viniera de JavaScript
header('Content-Type: application/json; charset=utf-8');

// Incluir todo lo necesario
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/globals.php';
require_once __DIR__ . '/app/helpers.php';

// No enviar Authorization header (sin autenticación)
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/calificaciones-academicas';
$_GET['id_anio'] = date('Y');

// No hay Authorization header
$_SERVER['HTTP_AUTHORIZATION'] = null;

use App\Controllers\CalificacionesAcademicasController;

try {
    echo "Instanciando controlador...\n";
    $controller = new CalificacionesAcademicasController();
    
    echo "Llamando a index()...\n";
    $controller->index();
    
    echo "Finalizó sin error.\n";
    
} catch (\Exception $e) {
    echo "ERROR CAPTURADO:\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nTrace:\n";
    echo $e->getTraceAsString();
    
    exit(1);
}
