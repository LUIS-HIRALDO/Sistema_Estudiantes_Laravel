<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';

try {
    $db = \App\Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if columns exist
    $columns = $conn->query("SHOW COLUMNS FROM institucion")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('firma_url', $columns)) {
        $conn->exec("ALTER TABLE institucion ADD COLUMN firma_url VARCHAR(255) AFTER logo_url");
        echo "Columna firma_url agregada.\n";
    }
    
    if (!in_array('sello_url', $columns)) {
        $conn->exec("ALTER TABLE institucion ADD COLUMN sello_url VARCHAR(255) AFTER firma_url");
        echo "Columna sello_url agregada.\n";
    }
    
    echo "Esquema actualizado correctamente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
