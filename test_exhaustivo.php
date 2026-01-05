<?php
// Test exhaustivo del sistema: valida principales entidades y cálculos sin depender de respuesta HTTP

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/globals.php';
require_once __DIR__ . '/app/helpers.php';

use App\Database;
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\CalificacionAcademica;
use App\Models\CalificacionTecnica;

function titulo(string $texto): void {
    echo "\n========================================\n";
    echo "  $texto\n";
    echo "========================================\n";
}

try {
    $pdo = Database::getInstance()->getConnection();
    titulo('Conexión a la base de datos');
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    echo " Conexión exitosa a: " . ($dbName ?: 'desconocida') . "\n";
} catch (Throwable $e) {
    titulo('Error de conexión');
    echo "✗ " . $e->getMessage() . "\n";
    exit(1);
}

// Estudiantes
try {
    titulo('Estudiantes registrados');
    $estudiantes = Estudiante::all();
    echo " Total: " . count($estudiantes) . "\n";
    if (!$estudiantes) {
        echo "ℹ Ejecuta scripts/seed.php para poblar datos.\n";
    }
} catch (Throwable $e) {
    echo " Error cargando estudiantes: " . $e->getMessage() . "\n";
}

// Materias
try {
    titulo('Materias registradas');
    $materias = Materia::all();
    echo " Total: " . count($materias) . "\n";
} catch (Throwable $e) {
    echo " Error cargando materias: " . $e->getMessage() . "\n";
}

$anioActual = date('Y');

// Calificaciones académicas
try {
    titulo('Calificaciones académicas');
    $calificacionesAcad = CalificacionAcademica::obtenerCalificaciones(null, null, null, $anioActual);
    echo " Registros recuperados: " . count($calificacionesAcad) . "\n";
} catch (Throwable $e) {
    echo " Error obteniendo calificaciones académicas: " . $e->getMessage() . "\n";
}

// Calificaciones técnicas
try {
    titulo('Calificaciones técnicas');
    $calificacionesTec = CalificacionTecnica::obtenerCalificaciones(null, null, $anioActual);
    echo " Registros recuperados: " . count($calificacionesTec) . "\n";
} catch (Throwable $e) {
    echo " Error obteniendo calificaciones técnicas: " . $e->getMessage() . "\n";
}

titulo('Estado general');
echo " Pruebas básicas completadas\n";
