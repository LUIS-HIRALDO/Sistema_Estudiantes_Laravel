<?php
$pdo = new PDO('mysql:host=localhost;dbname=sistema_estudiantes', 'root', '');

// Verificar estructura de tabla profesores
echo "=== ESTRUCTURA DE LA TABLA PROFESORES ===\n";
$result = $pdo->query('DESCRIBE profesores');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ") " . ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
}

// Verificar estructura de tabla profesores
echo "=== ESTRUCTURA DE LA TABLA PROFESORES ===\n";
$result = $pdo->query('DESCRIBE profesores');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ") " . ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
}

echo "\n=== PROFESORES EN LA BASE DE DATOS ===\n";
$result = $pdo->query('SELECT * FROM profesores');
$profesores = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Total: " . count($profesores) . " profesores\n\n";

if (!empty($profesores)) {
    foreach ($profesores as $i => $prof) {
        echo ($i+1) . ". ID: " . $prof['id'] . " | UserID: " . ($prof['usuario_id'] ?? 'NULL') . " | " . $prof['nombre'] . " | " . $prof['email'] . "\n";
    }
}
