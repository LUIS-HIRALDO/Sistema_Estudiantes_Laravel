<?php

// Cargar variables de entorno
$env_file = dirname(__DIR__) . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Database;

echo "Inicializando base de datos MySQL...\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Crear tablas
    echo "Creando tablas...\n";
    $db->createTables();
    echo "✓ Tablas creadas\n";

    // Verificar si ya existen datos
    $result = $conn->query("SELECT COUNT(*) FROM usuarios");
    $count = $result->fetchColumn();
    
    if ($count > 0) {
        echo "⚠ La base de datos ya contiene datos. Saltando inicialización.\n";
        exit(0);
    }

    // Crear roles
    $roles = [
        ['nombre' => 'Administrador', 'descripcion' => 'Acceso completo al sistema'],
        ['nombre' => 'Profesor', 'descripcion' => 'Acceso a calificaciones y asistencia'],
        ['nombre' => 'Estudiante', 'descripcion' => 'Acceso a calificaciones personales'],
        ['nombre' => 'Acudiente', 'descripcion' => 'Acceso de lectura a calificaciones del estudiante'],
    ];

    $stmt = $conn->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?)");
    foreach ($roles as $rol) {
        $stmt->execute([$rol['nombre'], $rol['descripcion']]);
    }
    echo "✓ Roles creados\n";

    // Obtener IDs de roles
    $admin_role = $conn->query("SELECT id FROM roles WHERE nombre = 'Administrador'")->fetch(\PDO::FETCH_ASSOC);
    $profesor_role = $conn->query("SELECT id FROM roles WHERE nombre = 'Profesor'")->fetch(\PDO::FETCH_ASSOC);
    $estudiante_role = $conn->query("SELECT id FROM roles WHERE nombre = 'Estudiante'")->fetch(\PDO::FETCH_ASSOC);

    // Crear usuario administrador
    $admin_email = $_ENV['ADMIN_EMAIL'] ?? 'admin@escuela.com';
    $admin_password = $_ENV['ADMIN_PASSWORD'] ?? 'admin123';
    
    $stmt = $conn->prepare("INSERT INTO usuarios (email, password, nombre, apellido, rol_id, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $admin_email,
        password_hash($admin_password, PASSWORD_BCRYPT),
        'Administrador',
        'Sistema',
        $admin_role['id'],
        'activo'
    ]);
    echo "✓ Usuario administrador creado\n";

    // Crear profesores
    $profesores_data = [
        ['nombre' => 'Juan', 'apellido' => 'García', 'email' => 'juan@escuela.com', 'especialidad' => 'Matemáticas'],
        ['nombre' => 'María', 'apellido' => 'Rodríguez', 'email' => 'maria@escuela.com', 'especialidad' => 'Español'],
        ['nombre' => 'Carlos', 'apellido' => 'López', 'email' => 'carlos@escuela.com', 'especialidad' => 'Ciencias'],
    ];

    $stmt_usuario = $conn->prepare("INSERT INTO usuarios (email, password, nombre, apellido, rol_id, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_profesor = $conn->prepare("INSERT INTO profesores (nombre, apellido, email, especialidad, usuario_id, estado) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($profesores_data as $prof) {
        $stmt_usuario->execute([
            $prof['email'],
            password_hash('profesor123', PASSWORD_BCRYPT),
            $prof['nombre'],
            $prof['apellido'],
            $profesor_role['id'],
            'activo'
        ]);
        $user_id = $conn->lastInsertId();
        
        $stmt_profesor->execute([
            $prof['nombre'],
            $prof['apellido'],
            $prof['email'],
            $prof['especialidad'],
            $user_id,
            'activo'
        ]);
    }
    echo "✓ Profesores creados\n";

    // Crear materias
    $materias_data = [
        ['nombre' => 'Matemáticas', 'codigo' => 'MAT101', 'grado' => '1', 'creditos' => 3],
        ['nombre' => 'Español', 'codigo' => 'ESP101', 'grado' => '1', 'creditos' => 3],
        ['nombre' => 'Ciencias', 'codigo' => 'CIE101', 'grado' => '1', 'creditos' => 3],
        ['nombre' => 'Inglés', 'codigo' => 'ING101', 'grado' => '1', 'creditos' => 2],
    ];

    $stmt_materia = $conn->prepare("INSERT INTO materias (nombre, codigo, grado, creditos, estado) VALUES (?, ?, ?, ?, ?)");
    foreach ($materias_data as $materia) {
        $stmt_materia->execute([
            $materia['nombre'],
            $materia['codigo'],
            $materia['grado'],
            $materia['creditos'],
            'activo'
        ]);
    }
    echo "✓ Materias creadas\n";

    // Crear estudiantes
    $estudiantes_data = [
        ['nombre' => 'Pedro', 'apellido' => 'Sánchez', 'email' => 'pedro@escuela.com'],
        ['nombre' => 'Ana', 'apellido' => 'Martínez', 'email' => 'ana@escuela.com'],
        ['nombre' => 'Luis', 'apellido' => 'Hernández', 'email' => 'luis@escuela.com'],
    ];

    $stmt_usuario = $conn->prepare("INSERT INTO usuarios (email, password, nombre, apellido, rol_id, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_estudiante = $conn->prepare("INSERT INTO estudiantes (nombre, apellido, email, grado, seccion, matricula, usuario_id, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($estudiantes_data as $est) {
        $stmt_usuario->execute([
            $est['email'],
            password_hash('estudiante123', PASSWORD_BCRYPT),
            $est['nombre'],
            $est['apellido'],
            $estudiante_role['id'],
            'activo'
        ]);
        $user_id = $conn->lastInsertId();
        
        $stmt_estudiante->execute([
            $est['nombre'],
            $est['apellido'],
            $est['email'],
            '1',
            'A',
            uniqid('MAT'),
            $user_id,
            'activo'
        ]);
    }
    echo "✓ Estudiantes creados\n";

    echo "\n✅ Base de datos inicializada correctamente\n";
    echo "\nCredenciales:\n";
    echo "  Email: " . $admin_email . "\n";
    echo "  Contraseña: " . $admin_password . "\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
