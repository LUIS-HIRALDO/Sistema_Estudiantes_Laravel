<?php
require_once __DIR__ . '/app/globals.php';
require_once __DIR__ . '/app/helpers.php';

// Mock session for testing if needed, or just rely on the controller not checking session if we don't call requireAuth
// But the controller calls requireAuth.
// We can simulate a token or bypass auth for this debug script.
// However, since we are running this via CLI or browser, let's try to instantiate the controller and call the method directly, 
// mocking the necessary parts.

// Actually, the easiest way to debug "Error cargando datos iniciales" which comes from a fetch error or 500 error
// is to see the actual error output.

// Let's try to run a curl command to the endpoint if possible, or simulate the request.

// We will simulate the request to `getModulos` and `getEstudiantesTecnicos`.

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/calificaciones-tecnicas/modulos';

// We need to bypass requireAuth in Controller.php or set up a valid session/token.
// Since we can't easily generate a valid JWT token without the secret key (which is in .env),
// let's try to modify the controller temporarily or use a known user.

// Better approach: Check the error log.
$logFile = __DIR__ . '/logs/error.log';
if (file_exists($logFile)) {
    echo "Last 10 lines of error log:\n";
    $lines = file($logFile);
    $last = array_slice($lines, -10);
    foreach ($last as $l) echo $l;
} else {
    echo "No error log found.\n";
}

// Let's try to execute the query directly to see if it works.
try {
    $db = \App\Database::getInstance();
    echo "\nTesting Database Connection...\n";
    $stmt = $db->getConnection()->query("SELECT 1");
    echo "Connection OK.\n";

    echo "\nTesting Modulos Query...\n";
    $stmt = $db->getConnection()->query("SELECT * FROM modulos_formativos WHERE estado = 'activo' ORDER BY nombre");
    $modulos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    echo "Modulos found: " . count($modulos) . "\n";
    print_r($modulos);

    echo "\nTesting Estudiantes Tecnicos Query...\n";
    $stmt = $db->getConnection()->query("SELECT * FROM estudiantes_tecnicos ORDER BY apellido, nombre");
    $estudiantes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    echo "Estudiantes found: " . count($estudiantes) . "\n";
    print_r($estudiantes);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
