<?php
$pdo = new PDO('mysql:host=localhost;dbname=sistema_estudiantes', 'root', '');
$result = $pdo->query('SELECT id, nombre, apellido, email FROM estudiantes ORDER BY id');
$rows = $result->fetchAll(PDO::FETCH_ASSOC);

echo "Total de estudiantes: " . count($rows) . "\n\n";
foreach ($rows as $i => $estudiante) {
    echo ($i+1) . ". ID: " . $estudiante['id'] . " | " . $estudiante['nombre'] . " " . $estudiante['apellido'] . " | " . $estudiante['email'] . "\n";
}
