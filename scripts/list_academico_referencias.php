<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Database.php';

use App\Database;

$conn = Database::getInstance()->getConnection();

$queries = [
    'estudiantes' => 'SELECT * FROM estudiantes LIMIT 5',
    'asignaturas' => 'SELECT * FROM asignaturas LIMIT 5',
    'competencias' => 'SELECT * FROM competencias LIMIT 5',
    'periodos' => 'SELECT * FROM periodos LIMIT 5',
    'anios_escolares' => 'SELECT * FROM anios_escolares LIMIT 5',
];

foreach ($queries as $label => $sql) {
    echo "=== $label ===\n";
    try {
        $stmt = $conn->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            echo "(sin registros)\n\n";
            continue;
        }
        foreach ($rows as $row) {
            echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    } catch (Throwable $e) {
        echo 'Error: ' . $e->getMessage() . "\n\n";
    }
    echo "\n";
}
