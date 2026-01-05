<?php
$pdo = new PDO('mysql:host=localhost;dbname=sistema_estudiantes', 'root', '');

// Verificar estructura de tabla notas
echo "=== ESTRUCTURA DE LA TABLA NOTAS ===\n";
$result = $pdo->query('DESCRIBE notas');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ") " . ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
}

echo "\n=== NOTAS EN LA BASE DE DATOS ===\n";
$result = $pdo->query('SELECT COUNT(*) as total FROM notas');
$data = $result->fetch(PDO::FETCH_ASSOC);
echo "Total de notas registradas: " . $data['total'] . "\n";

// Verificar relaciones
echo "\n=== MATERIAS DISPONIBLES ===\n";
$result = $pdo->query('SELECT id, nombre FROM materias');
$materias = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($materias as $mat) {
    echo "- ID: " . $mat['id'] . " | " . $mat['nombre'] . "\n";
}
