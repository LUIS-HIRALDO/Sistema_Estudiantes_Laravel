<?php
$pdo = new PDO('mysql:host=localhost;dbname=sistema_estudiantes', 'root', '');

$profesores = [
    ['nombre' => 'Roberto', 'apellido' => 'Martínez', 'email' => 'roberto.martinez@escuela.com', 'especialidad' => 'Física', 'estado' => 'activo'],
    ['nombre' => 'Sandra', 'apellido' => 'González', 'email' => 'sandra.gonzalez@escuela.com', 'especialidad' => 'Historia', 'estado' => 'activo'],
];

foreach ($profesores as $prof) {
    $stmt = $pdo->prepare('
        INSERT INTO profesores (nombre, apellido, email, especialidad, estado, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ');
    
    $result = $stmt->execute([
        $prof['nombre'],
        $prof['apellido'],
        $prof['email'],
        $prof['especialidad'],
        $prof['estado']
    ]);
    
    if ($result) {
        echo "✓ Creado: {$prof['nombre']} {$prof['apellido']}\n";
    } else {
        echo "✗ Error al crear: {$prof['nombre']}\n";
    }
}

echo "\nVerificando total de profesores...\n";
$result = $pdo->query('SELECT COUNT(*) as total FROM profesores');
$data = $result->fetch(PDO::FETCH_ASSOC);
echo "Total de profesores: " . $data['total'] . "\n";
