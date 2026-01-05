<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';

try {
    $db = \App\Database::getInstance();
    $stmt = $db->getConnection()->query("SELECT * FROM institucion");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Filas en institucion: " . count($rows) . "\n";
    if (count($rows) > 0) {
        print_r($rows[0]);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
