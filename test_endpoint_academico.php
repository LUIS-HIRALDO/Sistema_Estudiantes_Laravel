<?php
// Test simple del endpoint académicas  
ob_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/globals.php';
require_once __DIR__ . '/app/helpers.php';

require_once __DIR__ . '/app/Controllers/CalificacionesAcademicasController.php';

use App\Controllers\CalificacionesAcademicasController;

// Crear token válido
$secret = $_ENV['JWT_SECRET'] ?? 'secret';
$header = base64_url_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
$payload = base64_url_encode(json_encode([
    'id' => '1',
    'email' => 'test@test.com',
    'iat' => time(),
    'exp' => time() + (7 * 24 * 60 * 60)
]));
$signature = hash_hmac('sha256', "{$header}.{$payload}", $secret, true);
$signature = base64_url_encode($signature);
$token = "{$header}.{$payload}.{$signature}";

$_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";
$_GET['id_anio'] = date('Y');

try {
    ob_end_clean();
    
    $controller = new CalificacionesAcademicasController();
    $controller->index();
    
} catch (\Exception $e) {
    ob_end_clean();
    
    echo "EXCEPCIÓN:\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nTrace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

