<?php
require_once 'app/helpers.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance()->getConnection();

try {
    // Check if column exists
    $stmt = $db->prepare("SHOW COLUMNS FROM institucion LIKE 'email'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Add column
        $sql = "ALTER TABLE institucion ADD COLUMN email VARCHAR(255) AFTER telefono";
        $db->exec($sql);
        echo "Columna 'email' agregada exitosamente a la tabla 'institucion'.\n";
    } else {
        echo "La columna 'email' ya existe en la tabla 'institucion'.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
