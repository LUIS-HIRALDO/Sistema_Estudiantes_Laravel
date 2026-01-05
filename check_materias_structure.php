<?php
require_once 'app/helpers.php';
require_once 'app/globals.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "--- Tablas relacionadas con Materias ---\n";
$stmt = $conn->query("SHOW TABLES LIKE '%materia%'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

echo "\n--- Estructura de materias (si existe) ---\n";
try {
    $stmt = $conn->query("DESCRIBE materias");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']} - {$col['Type']}\n";
    }
} catch (Exception $e) { echo "No existe tabla materias\n"; }

echo "\n--- Estructura de asignaturas (si existe) ---\n";
try {
    $stmt = $conn->query("DESCRIBE asignaturas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']} - {$col['Type']}\n";
    }
} catch (Exception $e) { echo "No existe tabla asignaturas\n"; }

echo "\n--- Estructura de profesor_materia (si existe) ---\n";
try {
    $stmt = $conn->query("DESCRIBE profesor_materia");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']} - {$col['Type']}\n";
    }
} catch (Exception $e) { echo "No existe tabla profesor_materia\n"; }
