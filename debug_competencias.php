<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';

$db = \App\Database::getInstance();
$stmt = $db->getConnection()->query("SELECT id_competencia, nombre, bloque FROM competencias ORDER BY bloque DESC, id_competencia");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo "ID: {$r['id_competencia']} | Bloque: {$r['bloque']} | Nombre: {$r['nombre']}\n";
}
