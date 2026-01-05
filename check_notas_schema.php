<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';

$db = \App\Database::getInstance();
$table = 'notas_academicas';
echo "Tabla: $table\n";
try {
    $result = $db->getConnection()->query("DESC $table");
    $cols = $result->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  - " . $c['Field'] . " (" . $c['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
