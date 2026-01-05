<?php
require_once 'app/helpers.php';
require_once 'app/globals.php';
require_once 'app/Config.php';
require_once 'app/Database.php';

use App\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    echo "Iniciando migración de rol_id a rol...\n";

    // 1. Verificar si ya existe la columna rol
    $stmt = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'rol'");
    if ($stmt->fetch()) {
        echo "La columna 'rol' ya existe.\n";
    } else {
        // Agregar columna rol
        $conn->exec("ALTER TABLE usuarios ADD COLUMN rol VARCHAR(50) DEFAULT 'estudiante' AFTER apellido");
        echo "Columna 'rol' agregada.\n";
    }

    // 2. Migrar datos
    $map = [
        1 => 'admin',
        2 => 'profesor',
        3 => 'estudiante',
        4 => 'acudiente'
    ];

    foreach ($map as $id => $rol) {
        $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE rol_id = ?");
        $stmt->execute([$rol, $id]);
        $count = $stmt->rowCount();
        echo "Actualizados $count usuarios con rol_id $id a rol '$rol'.\n";
    }
    
    // 3. Eliminar columna rol_id
    $stmt = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'rol_id'");
    if ($stmt->fetch()) {
        $conn->exec("ALTER TABLE usuarios DROP COLUMN rol_id");
        echo "Columna 'rol_id' eliminada.\n";
    } else {
        echo "La columna 'rol_id' no existe o ya fue eliminada.\n";
    }

    echo "Migración completada exitosamente.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
