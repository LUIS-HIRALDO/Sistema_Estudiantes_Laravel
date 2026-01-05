<?php
require_once __DIR__ . '/app/globals.php';
require_once __DIR__ . '/app/helpers.php';

// Cargar variables de entorno
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

require_once __DIR__ . '/app/Config.php';
require_once __DIR__ . '/app/Database.php';

use App\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Agregar columna mostrar_logo_minerd
    $sql = "ALTER TABLE institucion ADD COLUMN mostrar_logo_minerd TINYINT(1) DEFAULT 1";
    $conn->exec($sql);
    echo "Columna mostrar_logo_minerd agregada.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
