<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/globals.php';

use App\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->pdo;

    echo "Verificando columna updated_at en estudiantes_tecnicos...\n";

    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM estudiantes_tecnicos LIKE 'updated_at'");
    $stmt->execute();
    $exists = $stmt->fetch();

    if (!$exists) {
        echo "Agregando columna updated_at...\n";
        $pdo->exec("ALTER TABLE estudiantes_tecnicos ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "Columna agregada exitosamente.\n";
    } else {
        echo "La columna updated_at ya existe.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
