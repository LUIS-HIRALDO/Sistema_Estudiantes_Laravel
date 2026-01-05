<?php

/**
 * Script de pruebas rápidas
 * Uso: php scripts/test.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

echo "========================================\n";
echo "  PRUEBAS DEL SISTEMA\n";
echo "========================================\n\n";

// 1. Verificar carga de variables de entorno
echo "1. Verificando variables de entorno...\n";
$env_file = dirname(__DIR__) . '/.env';
if (file_exists($env_file)) {
    echo "   ✓ Archivo .env existe\n";
} else {
    echo "   ✗ Archivo .env NO existe\n";
    echo "   ℹ Copia .env.example a .env\n";
}

// 2. Verificar conexión a MySQL
echo "\n2. Verificando conexión a MySQL...\n";
try {
    // Cargar variables de entorno (si no se han cargado)
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

    $pdo = \App\Database::getInstance()->getConnection();
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    echo "   ✓ Conexión a MySQL establecida\n";
    echo "   ℹ Base de datos: " . ($dbName ?: ($_ENV['DB_DATABASE'] ?? 'sistema_estudiantes')) . "\n";
} catch (\Exception $e) {
    echo "   ✗ Error conectando a MySQL\n";
    echo "   Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Verificar tablas principales
echo "\n3. Verificando tablas...\n";
try {
    $pdo = \App\Database::getInstance()->getConnection();
    $tables = [
        'usuarios',
        'roles',
        'estudiantes',
        'profesores',
        'materias',
        'notas_academicas',
        'notas_tecnicas',
        'asistencias',
        'pagos',
        'horarios',
        'notificaciones',
        'comentarios'
    ];

    foreach ($tables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '" . $table . "'")->fetchColumn();
        if ($exists) {
            $count = $pdo->query("SELECT COUNT(*) FROM `" . $table . "`")->fetchColumn();
            echo "   ✓ $table: $count registros\n";
        } else {
            echo "   ✗ $table: tabla no encontrada\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ Error verificando tablas\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

// 4. Verificar usuarios
echo "\n4. Verificando usuarios...\n";
try {
    $usuarios = \App\Models\Usuario::all();
    echo "   ✓ Total de usuarios: " . count($usuarios) . "\n";
    
    if (count($usuarios) === 0) {
        echo "   ℹ No hay usuarios. Ejecuta: php scripts/seed.php\n";
    } else {
        echo "   ✓ Usuario admin encontrado\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error verificando usuarios\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

// 5. Verificar directorios y permisos
echo "\n5. Verificando directorios...\n";
$dirs = [
    'app/',
    'config/',
    'public/',
    'resources/',
    'vendor/',
];

foreach ($dirs as $dir) {
    $path = dirname(__DIR__) . '/' . $dir;
    if (is_dir($path)) {
        echo "   ✓ $dir existe\n";
    } else {
        echo "   ✗ $dir NO existe\n";
    }
}

// 6. Verificar archivos de configuración
echo "\n6. Verificando archivos de configuración...\n";
$config_files = [
    'config/app.php',
    'config/database.php',
    'config/auth.php',
];

foreach ($config_files as $file) {
    $path = dirname(__DIR__) . '/' . $file;
    if (file_exists($path)) {
        echo "   ✓ $file existe\n";
    } else {
        echo "   ✗ $file NO existe\n";
    }
}

// 7. Test de helpers
echo "\n7. Verificando funciones auxiliares...\n";
try {
    if (function_exists('env')) {
        echo "   ✓ env() disponible\n";
    }
    if (function_exists('response')) {
        echo "   ✓ response() disponible\n";
    }
    if (function_exists('generateToken')) {
        echo "   ✓ generateToken() disponible\n";
    }
    if (function_exists('verifyToken')) {
        echo "   ✓ verifyToken() disponible\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error cargando funciones\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

// Resumen final
echo "\n========================================\n";
echo "  RESUMEN\n";
echo "========================================\n";
echo "\nSi todo está correcto ✓, puedes:\n";
echo "1. Ejecutar: php scripts/seed.php (para inicializar datos)\n";
echo "2. Ejecutar: php -S localhost:8000 -t public\n";
echo "3. Acceder a: http://localhost:8000\n";
echo "\n";
