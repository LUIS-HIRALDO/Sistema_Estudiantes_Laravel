<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';

$db = \App\Database::getInstance();
$result = $db->getConnection()->query('DESC notas_academicas');
$cols = $result->fetchAll(\PDO::FETCH_ASSOC);

echo "CAMPOS EN notas_academicas:\n";
foreach ($cols as $c) {
    echo $c['Field'] . " | " . $c['Type'] . "\n";
}
