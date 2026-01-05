<?php
require_once 'app/helpers.php';
require_once 'app/globals.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    echo "Agregando columna cedula...\n";

    // Usuarios
    $stmt = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'cedula'");
    if (!$stmt->fetch()) {
        $conn->exec("ALTER TABLE usuarios ADD COLUMN cedula VARCHAR(20) NULL AFTER apellido");
        echo "Columna 'cedula' agregada a usuarios.\n";
    } else {
        echo "Columna 'cedula' ya existe en usuarios.\n";
    }

    // Profesores
    $stmt = $conn->query("SHOW COLUMNS FROM profesores LIKE 'cedula'");
    if (!$stmt->fetch()) {
        $conn->exec("ALTER TABLE profesores ADD COLUMN cedula VARCHAR(20) NULL AFTER email");
        echo "Columna 'cedula' agregada a profesores.\n";
    } else {
        echo "Columna 'cedula' ya existe en profesores.\n";
    }

    echo "Migración completada.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
