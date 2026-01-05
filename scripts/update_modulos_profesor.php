<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';

use App\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    echo "Verificando columna id_profesor en modulos_formativos...\n";

    // Verificar si la columna ya existe
    $stmt = $conn->query("SHOW COLUMNS FROM modulos_formativos LIKE 'id_profesor'");
    $exists = $stmt->fetch();

    if (!$exists) {
        echo "Agregando columna id_profesor...\n";
        $sql = "ALTER TABLE modulos_formativos 
                ADD COLUMN id_profesor INT NULL,
                ADD CONSTRAINT fk_modulo_profesor 
                FOREIGN KEY (id_profesor) REFERENCES profesores(id) 
                ON DELETE SET NULL";
        
        $conn->exec($sql);
        echo "Columna agregada y llave foránea creada correctamente.\n";
    } else {
        echo "La columna id_profesor ya existe.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
