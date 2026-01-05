<?php
require_once 'app/helpers.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

$db = \App\Database::getInstance();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT * FROM materias");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
