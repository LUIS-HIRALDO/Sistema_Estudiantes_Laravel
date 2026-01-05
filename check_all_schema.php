<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';

$db = \App\Database::getInstance();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  ESTRUCTURA DE TABLAS RELACIONADAS                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$tables = ['competencias', 'asignaturas', 'estudiantes', 'periodos'];

foreach ($tables as $table) {
    echo "Tabla: $table\n";
    $result = $db->getConnection()->query("DESC $table");
    $cols = $result->fetchAll(\PDO::FETCH_ASSOC);
    
    foreach ($cols as $c) {
        $key = $c['Key'] === 'PRI' ? ' [PRIMARY]' : '';
        echo "  - " . $c['Field'] . " (" . $c['Type'] . ")" . $key . "\n";
    }
    echo "\n";
}
