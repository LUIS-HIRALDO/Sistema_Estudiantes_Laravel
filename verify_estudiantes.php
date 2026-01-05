<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';

$pdo = \App\Database::getInstance()->getConnection();
$result = $pdo->query('SELECT id, nombre, apellido, email FROM estudiantes ORDER BY id');
$rows = $result->fetchAll(PDO::FETCH_ASSOC);

echo "Total de estudiantes: " . count($rows) . "\n\n";
foreach ($rows as $estudiante) {
    echo "ID: " . $estudiante['id'] . " | Nombre: " . $estudiante['nombre'] . " " . $estudiante['apellido'] . " | Email: " . $estudiante['email'] . "\n";
}
