<?php
require_once __DIR__ . '/app/Database.php';

try {
    $db = \App\Database::getInstance()->getConnection();
    
    $standard = [
        'Lengua Española',
        'Matemática',
        'Ciencias Sociales',
        'Ciencias Naturales',
        'Inglés',
        'Educación Física',
        'Formación Humana',
        'Educación Artística'
    ];
    
    $placeholders = implode(',', array_fill(0, count($standard), '?'));
    
    $sql = "SELECT * FROM materias WHERE nombre NOT IN ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute($standard);
    $others = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== MATERIAS NO ESTANDAR ===\n";
    if (empty($others)) {
        echo "No se encontraron materias fuera del estándar.\n";
    } else {
        foreach ($others as $m) {
            echo "ID: {$m['id']} | Nombre: {$m['nombre']} | Grado: {$m['grado']}\n";
        }
    }

} catch (Exception $e) {
    echo $e->getMessage();
}
