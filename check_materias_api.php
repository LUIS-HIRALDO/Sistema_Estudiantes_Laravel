<?php
require_once 'app/helpers.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "--- GET /materias simulation ---\n";
$stmt = $conn->query("SELECT * FROM materias");
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if we have professor info
if (count($materias) > 0) {
    print_r($materias[0]);
} else {
    echo "No materias found.\n";
}

// Check if we can get professor names
echo "\n--- Checking Professor Names ---\n";
$stmt = $conn->query("
    SELECT m.id, m.nombre, m.profesor_id, p.nombre as prof_nombre, p.apellido as prof_apellido 
    FROM materias m 
    LEFT JOIN profesores p ON m.profesor_id = p.id
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
