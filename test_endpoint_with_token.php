<?php
// Capturar CUALQUIER error que se genere

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "ERROR CAPTURADO:\n";
    echo "Tipo: $errno\n";
    echo "Mensaje: $errstr\n";
    echo "Archivo: $errfile\n";
    echo "Línea: $errline\n";
    exit(1);
});

set_exception_handler(function($e) {
    echo "EXCEPCIÓN CAPTURADA:\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
});

// No enviar headers aquí para no conflictuar
// header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/globals.php';
require_once __DIR__ . '/app/helpers.php';

require_once __DIR__ . '/app/Controllers/CalificacionesAcademicasController.php';

use App\Controllers\CalificacionesAcademicasController;
use App\Models\CalificacionAcademica;

// Simular GET sin autenticación para ver qué falla
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['id_anio'] = date('Y');

// Crear un token simulado válido para pruebas
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

// Enviar el token
$_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";

echo "Token: $token\n";
echo "Autenticado: " . (isAuthenticated() ? 'SÍ' : 'NO') . "\n\n";

try {
    echo "Llamando a CalificacionesAcademicasController->index()...\n";
    $controller = new CalificacionesAcademicasController();
    $controller->index();
    echo "Resultado: respuesta enviada\n";
} catch (\Exception $e) {
    echo "EXCEPCIÓN:\n";
    echo get_class($e) . ": " . $e->getMessage() . "\n";
    echo "En: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
