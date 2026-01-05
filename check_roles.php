<?php
require_once 'app/helpers.php';
require_once 'app/globals.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT id, email, rol FROM usuarios");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Usuarios encontrados: " . count($users) . "\n";
foreach ($users as $user) {
    echo "ID: {$user['id']}, Email: {$user['email']}, Rol: {$user['rol']}\n";
}

// Check if roles table exists
try {
    $stmt = $conn->query("SELECT * FROM roles");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nRoles encontrados:\n";
    foreach ($roles as $role) {
        print_r($role);
    }
} catch (Exception $e) {
    echo "\nTabla roles no existe o error: " . $e->getMessage() . "\n";
}
