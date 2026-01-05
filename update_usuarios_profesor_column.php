<?php
require_once 'app/helpers.php';
require_once 'app/globals.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    echo "Agregando columna profesor_id a usuarios...\n";

    // Verificar si existe
    $stmt = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'profesor_id'");
    if ($stmt->fetch()) {
        echo "La columna 'profesor_id' ya existe.\n";
    } else {
        $conn->exec("ALTER TABLE usuarios ADD COLUMN profesor_id INT NULL AFTER rol");
        echo "Columna 'profesor_id' agregada.\n";
    }

    // Sincronizar datos existentes (basado en profesores.usuario_id)
    echo "Sincronizando relaciones existentes...\n";
    $stmt = $conn->query("SELECT id, usuario_id FROM profesores WHERE usuario_id IS NOT NULL");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($links as $link) {
        $profId = $link['id'];
        $userId = $link['usuario_id'];
        
        $update = $conn->prepare("UPDATE usuarios SET profesor_id = ? WHERE id = ?");
        $update->execute([$profId, $userId]);
        echo "Usuario $userId vinculado con Profesor $profId\n";
    }

    echo "Migración completada.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
