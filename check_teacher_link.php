<?php
require_once 'app/helpers.php';
require_once 'app/globals.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "--- Usuarios con rol 'profesor' ---\n";
$stmt = $conn->query("SELECT id, nombre, email FROM usuarios WHERE rol = 'profesor'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "ID: {$u['id']}, {$u['nombre']}, {$u['email']}\n";
}

echo "\n--- Profesores ---\n";
$stmt = $conn->query("SELECT id, nombre, email, usuario_id FROM profesores");
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($teachers as $t) {
    echo "ID: {$t['id']}, {$t['nombre']}, {$t['email']}, Usuario ID: " . ($t['usuario_id'] ?? 'NULL') . "\n";
}
