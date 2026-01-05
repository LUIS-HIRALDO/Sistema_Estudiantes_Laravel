<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

use App\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    echo "Verificando columna tipo en profesores...\n";

    // Verificar si la columna ya existe
    $stmt = $conn->query("SHOW COLUMNS FROM profesores LIKE 'tipo'");
    $exists = $stmt->fetch();

    if (!$exists) {
        echo "Agregando columna tipo...\n";
        // Default 'academico' for existing records
        $sql = "ALTER TABLE profesores 
                ADD COLUMN tipo VARCHAR(20) NOT NULL DEFAULT 'academico' AFTER especialidad";
        
        $conn->exec($sql);
        echo "Columna 'tipo' agregada correctamente.\n";
    } else {
        echo "La columna 'tipo' ya existe.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
