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

    // Agregar columna logo_minerd_url
    // Primero verificamos si existe para no dar error
    $stmt = $conn->query("SHOW COLUMNS FROM institucion LIKE 'logo_minerd_url'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE institucion ADD COLUMN logo_minerd_url VARCHAR(255) DEFAULT NULL";
        $conn->exec($sql);
        echo "Columna logo_minerd_url agregada.\n";
        
        // Establecer el logo por defecto para registros existentes
        $defaultLogo = "https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Logo_del_Ministerio_de_Educaci%C3%B3n_%28Rep%C3%BAblica_Dominicana%29.svg/1200px-Logo_del_Ministerio_de_Educaci%C3%B3n_%28Rep%C3%BAblica_Dominicana%29.svg.png";
        $sql = "UPDATE institucion SET logo_minerd_url = '$defaultLogo' WHERE logo_minerd_url IS NULL";
        $conn->exec($sql);
        echo "Logo por defecto establecido.\n";
    } else {
        echo "La columna logo_minerd_url ya existe.\n";
    }

    // Eliminar la columna anterior si existe (limpieza)
    $stmt = $conn->query("SHOW COLUMNS FROM institucion LIKE 'mostrar_logo_minerd'");
    if ($stmt->rowCount() > 0) {
        $sql = "ALTER TABLE institucion DROP COLUMN mostrar_logo_minerd";
        $conn->exec($sql);
        echo "Columna mostrar_logo_minerd eliminada.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
