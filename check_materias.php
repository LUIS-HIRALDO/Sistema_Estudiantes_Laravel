<?php
$pdo = new PDO('mysql:host=localhost;dbname=sistema_estudiantes', 'root', '');

// Verificar estructura de tabla materias
echo "=== ESTRUCTURA DE LA TABLA MATERIAS ===\n";
$result = $pdo->query('DESCRIBE materias');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ") " . ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
}

echo "\n=== MATERIAS EN LA BASE DE DATOS ===\n";
$result = $pdo->query('SELECT * FROM materias');
$materias = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Total: " . count($materias) . " materias\n\n";

if (!empty($materias)) {
    foreach ($materias as $i => $mat) {
        echo ($i+1) . ". ID: " . $mat['id'] . " | " . $mat['nombre'];
        if (isset($mat['descripcion'])) {
            echo " | Desc: " . substr($mat['descripcion'], 0, 30) . "...";
        }
        echo "\n";
    }
}
