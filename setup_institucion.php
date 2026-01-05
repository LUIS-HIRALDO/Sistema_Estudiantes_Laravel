<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';

try {
    $db = \App\Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/database/institucion.sql');
    $db->getConnection()->exec($sql);
    echo "Tabla institucion creada correctamente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
