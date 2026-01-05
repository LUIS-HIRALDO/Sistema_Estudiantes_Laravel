<?php
require_once 'app/Database.php';
$db = \App\Database::getInstance();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT id, email, rol FROM usuarios");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($users);
