<?php
// Simular entorno web
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/calificaciones-academicas';

// Cargar autoloader y entorno
require_once __DIR__ . '/vendor/autoload.php';
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Helpers
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/globals.php';

// Generar token para usuario ID 5 (Pedro)
$usuario = new \App\Models\Usuario(['id' => 5, 'email' => 'pedro@escuela.com']);
$token = generateToken($usuario);

// Configurar headers
$_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";

// Configurar parámetros GET
$_GET['id_estudiante'] = 1; // Pedro Sánchez
$_GET['id_anio'] = 2026;

// Capturar salida
ob_start();

try {
    $controller = new \App\Controllers\CalificacionesAcademicasController();
    $controller->index();
} catch (Throwable $e) {
    echo "EXCEPCIÓN CAPTURADA: " . $e->getMessage();
}

$output = ob_get_clean();

echo "--- INICIO SALIDA ---\n";
echo $output;
echo "\n--- FIN SALIDA ---\n";

// Análisis de la salida
$json = json_decode($output, true);
if ($json) {
    echo "\nEstructura del primer elemento:\n";
    if (!empty($json['data'])) {
        print_r($json['data'][0]);
    } else {
        echo "Data vacía.\n";
    }
} else {
    echo "JSON inválido.\n";
}
