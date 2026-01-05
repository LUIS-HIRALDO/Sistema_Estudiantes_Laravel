<?php
require_once 'app/helpers.php';
require_once 'app/globals.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "--- Estructura de USUARIOS ---\n";
$stmt = $conn->query("DESCRIBE usuarios");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "{$col['Field']} - {$col['Type']}\n";
}

echo "\n--- Estructura de PROFESORES ---\n";
try {
    $stmt = $conn->query("DESCRIBE profesores");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']} - {$col['Type']}\n";
    }
} catch (Exception $e) {
    echo "Tabla profesores no encontrada.\n";
}
