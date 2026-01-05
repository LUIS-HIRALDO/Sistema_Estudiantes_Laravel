<?php
require_once 'app/helpers.php';
require_once 'app/globals.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "--- Asignaturas ---\n";
$stmt = $conn->query("SELECT id_asignatura, nombre, codigo, grado FROM asignaturas LIMIT 5");
$asigs = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($asigs);

echo "\n--- Materias ---\n";
try {
    $stmt = $conn->query("SELECT id, nombre, codigo, grado, profesor_id FROM materias LIMIT 5");
    $mats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($mats);
} catch (Exception $e) { echo "Error materias: " . $e->getMessage(); }
