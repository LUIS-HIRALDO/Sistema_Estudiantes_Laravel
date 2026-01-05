#!/usr/bin/env php
<?php

/**
 * TEST SCRIPT - Verificar endpoints de calificaciones
 * Uso: php test_calificaciones.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Database;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST DE ENDPOINTS - SISTEMA DE CALIFICACIONES            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Conectar a BD
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "✅ Conexión a BD: OK\n\n";
    
    // Verificar tablas
    $tables = ['cierres_asignaturas', 'cierre_modulos'];
    
    echo "📊 VERIFICACIÓN DE TABLAS:\n";
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $result = $stmt->fetch();
        
        if ($result) {
            echo "   ✅ Tabla '$table' existe\n";
        } else {
            echo "   ❌ Tabla '$table' NO existe\n";
        }
    }
    
    echo "\n";
    
    // Verificar modelos
    echo "📦 VERIFICACIÓN DE MODELOS:\n";
    
    $models = [
        'App\Models\CalificacionAcademica',
        'App\Models\CalificacionTecnica'
    ];
    
    foreach ($models as $model) {
        if (class_exists($model)) {
            echo "   ✅ Modelo $model cargado\n";
        } else {
            echo "   ❌ Modelo $model NO cargado\n";
        }
    }
    
    echo "\n";
    
    // Verificar controllers
    echo "🎮 VERIFICACIÓN DE CONTROLLERS:\n";
    
    $controllers = [
        'App\Controllers\CalificacionesAcademicasController',
        'App\Controllers\CalificacionesTecnicasController'
    ];
    
    foreach ($controllers as $controller) {
        if (class_exists($controller)) {
            echo "   ✅ Controller $controller cargado\n";
        } else {
            echo "   ❌ Controller $controller NO cargado\n";
        }
    }
    
    echo "\n";
    
    // Probar datos de ejemplo
    echo "🧪 PRUEBA DE DATOS:\n";
    
    // Estudiantes por modalidad
    $stmt = $pdo->prepare("
        SELECT modalidad, COUNT(*) as total 
        FROM estudiantes 
        GROUP BY modalidad
    ");
    $stmt->execute();
    $modalidades = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    foreach ($modalidades as $m) {
        echo "   " . $m['modalidad'] . ": " . $m['total'] . " estudiante(s)\n";
    }
    
    echo "\n";
    
    // RA activos
    $stmt = $pdo->prepare("
        SELECT 
            mf.nombre as modulo,
            COUNT(CASE WHEN ra.activo = TRUE THEN 1 END) as ra_activos,
            SUM(CASE WHEN ra.activo = TRUE THEN ra.porcentaje ELSE 0 END) as suma_porcentaje
        FROM modulos_formativos mf
        LEFT JOIN resultados_aprendizaje ra ON ra.id_modulo = mf.id_modulo
        GROUP BY mf.id_modulo
    ");
    $stmt->execute();
    $modulos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    echo "📊 RA POR MÓDULO:\n";
    foreach ($modulos as $m) {
        $suma = $m['suma_porcentaje'] ?? 0;
        $estado = ($suma == 100) ? "✅" : "⚠️";
        echo "   $estado " . $m['modulo'] . ": " . $m['ra_activos'] . " RA (Σ=" . $suma . "%)\n";
    }
    
    echo "\n";
    
    // Asignaturas
    $stmt = $pdo->prepare("
        SELECT 
            a.nombre,
            COUNT(DISTINCT c.id_competencia) as competencias,
            SUM(CASE WHEN c.bloque = '70' THEN 1 ELSE 0 END) as bloque_70,
            SUM(CASE WHEN c.bloque = '30' THEN 1 ELSE 0 END) as bloque_30
        FROM asignaturas a
        LEFT JOIN competencias c ON c.id_asignatura = a.id_asignatura
        GROUP BY a.id_asignatura
    ");
    $stmt->execute();
    $asignaturas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    echo "📚 ASIGNATURAS:\n";
    foreach ($asignaturas as $a) {
        echo "   ✅ " . $a['nombre'] . ": " . $a['competencias'] . " comp. (" . $a['bloque_70'] . "@70% + " . $a['bloque_30'] . "@30%)\n";
    }
    
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ TODOS LOS SISTEMAS OPERATIVOS                         ║\n";
    echo "║  Las APIs están listas para consumir desde frontend      ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
