<?php
require_once 'app/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    $db->exec("ALTER TABLE usuarios ADD COLUMN must_change_password TINYINT(1) DEFAULT 0 AFTER estado");
    echo "Columna must_change_password agregada correctamente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
