<?php

// Script para inicializar datos de prueba en MongoDB
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Cargar .env
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

use App\Database;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\Materia;

echo "Inicializando base de datos...\n";

$db = Database::getInstance();
$db->createIndexes();

// Crear roles predefinidos
echo "Creando roles...\n";

$rolesData = [
    ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema', 'permisos' => ['*']],
    ['nombre' => 'Profesor', 'descripcion' => 'Gestión de materias y calificaciones', 'permisos' => ['materias.view', 'notas.create', 'asistencias.create']],
    ['nombre' => 'Estudiante', 'descripcion' => 'Acceso limitado para estudiantes', 'permisos' => ['notas.view', 'asistencias.view']],
    ['nombre' => 'Padre', 'descripcion' => 'Monitoreo de estudiante', 'permisos' => ['notas.view', 'asistencias.view']],
    ['nombre' => 'Secretaria', 'descripcion' => 'Gestión administrativa', 'permisos' => ['estudiantes.manage', 'profesores.manage', 'pagos.view']],
];

foreach ($rolesData as $roleData) {
    try {
        Rol::create($roleData);
        echo "✓ Rol '{$roleData['nombre']}' creado\n";
    } catch (\Exception $e) {
        echo "✗ Error al crear rol: {$e->getMessage()}\n";
    }
}

// Crear usuario administrador
echo "\nCreando usuario administrador...\n";

try {
    $usuarioAdmin = new Usuario();
    $usuarioAdmin->fill([
        'email' => 'admin@escuela.com',
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'estado' => 'activo'
    ]);
    $usuarioAdmin->setPassword('admin123');
    $usuarioAdmin->save();
    echo "✓ Usuario administrador creado (admin@escuela.com / admin123)\n";
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
}

// Crear estudiantes de ejemplo
echo "\nCreando estudiantes de ejemplo...\n";

$estudiantesData = [
    ['nombre' => 'Juan', 'apellido' => 'Pérez', 'email' => 'juan@escuela.com', 'grado' => '10', 'telefono' => '8095551001'],
    ['nombre' => 'María', 'apellido' => 'García', 'email' => 'maria@escuela.com', 'grado' => '10', 'telefono' => '8095551002'],
    ['nombre' => 'Carlos', 'apellido' => 'López', 'email' => 'carlos@escuela.com', 'grado' => '11', 'telefono' => '8095551003'],
    ['nombre' => 'Ana', 'apellido' => 'Martínez', 'email' => 'ana@escuela.com', 'grado' => '11', 'telefono' => '8095551004'],
];

foreach ($estudiantesData as $estData) {
    try {
        Estudiante::create(array_merge($estData, ['estado' => 'activo']));
        echo "✓ Estudiante '{$estData['nombre']}' creado\n";
    } catch (\Exception $e) {
        echo "✗ Error: {$e->getMessage()}\n";
    }
}

// Crear profesores de ejemplo
echo "\nCreando profesores de ejemplo...\n";

$profesoresData = [
    ['nombre' => 'Fernando', 'apellido' => 'Rodríguez', 'email' => 'fernando@escuela.com', 'especialidad' => 'Matemáticas', 'telefono' => '8095552001'],
    ['nombre' => 'Laura', 'apellido' => 'González', 'email' => 'laura@escuela.com', 'especialidad' => 'Español', 'telefono' => '8095552002'],
    ['nombre' => 'Pedro', 'apellido' => 'Sánchez', 'email' => 'pedro@escuela.com', 'especialidad' => 'Inglés', 'telefono' => '8095552003'],
];

foreach ($profesoresData as $profData) {
    try {
        Profesor::create(array_merge($profData, ['estado' => 'activo']));
        echo "✓ Profesor '{$profData['nombre']}' creado\n";
    } catch (\Exception $e) {
        echo "✗ Error: {$e->getMessage()}\n";
    }
}

// Crear materias de ejemplo
echo "\nCreando materias de ejemplo...\n";

$materiasData = [
    ['nombre' => 'Matemáticas', 'descripcion' => 'Álgebra y geometría', 'grado' => '10', 'horas_semana' => 5, 'creditos' => 3],
    ['nombre' => 'Español', 'descripcion' => 'Literatura y lenguaje', 'grado' => '10', 'horas_semana' => 4, 'creditos' => 3],
    ['nombre' => 'Inglés', 'descripcion' => 'Idioma extranjero', 'grado' => '10', 'horas_semana' => 3, 'creditos' => 2],
    ['nombre' => 'Física', 'descripcion' => 'Ciencias naturales', 'grado' => '11', 'horas_semana' => 4, 'creditos' => 3],
];

foreach ($materiasData as $matData) {
    try {
        Materia::create(array_merge($matData, ['estado' => 'activo']));
        echo "✓ Materia '{$matData['nombre']}' creada\n";
    } catch (\Exception $e) {
        echo "✗ Error: {$e->getMessage()}\n";
    }
}

echo "\n✅ Inicialización completada exitosamente!\n";
echo "\nCredenciales de acceso:\n";
echo "- Email: admin@escuela.com\n";
echo "- Contraseña: admin123\n";
echo "\nAccede a: http://localhost:8000/login.html\n";
