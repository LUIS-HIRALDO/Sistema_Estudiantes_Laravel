<?php

// Incluir funciones globales primero
require_once dirname(__DIR__) . '/app/globals.php';
require_once dirname(__DIR__) . '/app/helpers.php';

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

// Configurar error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivar salida de errores al navegador para no romper JSON
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/error.log');

// Limpiar cache de opcodes
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Headers
header('Content-Type: application/json; charset=utf-8');

// Router simple
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Limpiar la ruta - detectar base_path automáticamente
$script_name = dirname($_SERVER['SCRIPT_NAME']);
if ($script_name !== '/' && strpos($uri, $script_name) === 0) {
    $uri = substr($uri, strlen($script_name));
}

// Remover index.php de la URI
if (strpos($uri, '/index.php') === 0) {
    $uri = substr($uri, 10); // strlen('/index.php') = 10
}

$uri = trim($uri, '/');

// Servir archivos estáticos
$static_extensions = ['html', 'css', 'js', 'json', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
$path_parts = explode('.', $uri);
$extension = end($path_parts);

if (!empty($uri) && in_array($extension, $static_extensions)) {
    $file = __DIR__ . '/' . $uri;
    if (file_exists($file)) {
        // Determinar content-type
        $mimes = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        header('Content-Type: ' . ($mimes[$extension] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
}

// Si no hay extensión, intentar servir index.html o continuar con API
if (empty($uri) || $uri === 'index') {
    $index_file = __DIR__ . '/index.html';
    if (file_exists($index_file)) {
        header('Content-Type: text/html');
        readfile($index_file);
        exit;
    }
}

// Headers de CORS para API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Database;
use App\Response;

// Inicializar base de datos
try {
    $db = Database::getInstance();
} catch (\Exception $e) {
    Response::json(['error' => 'Error de conexión a la base de datos'], 500);
}

// Rutas
$routes = [
    // Auth
    'POST:auth/register' => 'App\Controllers\AuthController@register',
    'POST:auth/login' => 'App\Controllers\AuthController@login',
    'GET:auth/profile' => 'App\Controllers\AuthController@profile',
    'POST:auth/change-password' => 'App\Controllers\AuthController@changePassword',

    // Usuarios
    'GET:usuarios' => 'App\Controllers\UsuarioController@index',
    'POST:usuarios' => 'App\Controllers\UsuarioController@store',
    'GET:usuarios/{id}' => 'App\Controllers\UsuarioController@show',
    'PUT:usuarios/{id}' => 'App\Controllers\UsuarioController@update',
    'DELETE:usuarios/{id}' => 'App\Controllers\UsuarioController@destroy',

    // Estudiantes
    'GET:estudiantes' => 'App\Controllers\EstudianteController@index',
    'POST:estudiantes' => 'App\Controllers\EstudianteController@store',
    'GET:estudiantes/{id}' => 'App\Controllers\EstudianteController@show',
    'PUT:estudiantes/{id}' => 'App\Controllers\EstudianteController@update',
    'DELETE:estudiantes/{id}' => 'App\Controllers\EstudianteController@destroy',
    'GET:estudiantes/grado/{grado}' => 'App\Controllers\EstudianteController@porGrado',

    // Estudiantes Técnicos
    'GET:estudiantes-tecnicos' => 'App\Controllers\EstudianteTecnicoController@index',
    'POST:estudiantes-tecnicos' => 'App\Controllers\EstudianteTecnicoController@store',
    'GET:estudiantes-tecnicos/{id}' => 'App\Controllers\EstudianteTecnicoController@show',
    'PUT:estudiantes-tecnicos/{id}' => 'App\Controllers\EstudianteTecnicoController@update',
    'DELETE:estudiantes-tecnicos/{id}' => 'App\Controllers\EstudianteTecnicoController@destroy',
    'GET:estudiantes-tecnicos/grado/{grado}' => 'App\Controllers\EstudianteTecnicoController@porGrado',

    // Profesores
    'GET:profesores' => 'App\Controllers\ProfesorController@index',
    'GET:profesores/mis-materias' => 'App\Controllers\ProfesorController@misMaterias',
    'POST:profesores' => 'App\Controllers\ProfesorController@store',
    'GET:profesores/{id}' => 'App\Controllers\ProfesorController@show',
    'PUT:profesores/{id}' => 'App\Controllers\ProfesorController@update',
    'POST:profesores/{id}/reset-password' => 'App\Controllers\ProfesorController@resetPassword',
    'DELETE:profesores/{id}' => 'App\Controllers\ProfesorController@destroy',
    'GET:profesores/con-materias' => 'App\Controllers\ProfesorController@conMaterias',

    // Materias
    'GET:materias' => 'App\Controllers\MateriaController@index',
    'POST:materias' => 'App\Controllers\MateriaController@store',
    'GET:materias/{id}' => 'App\Controllers\MateriaController@show',
    'PUT:materias/{id}' => 'App\Controllers\MateriaController@update',
    'DELETE:materias/{id}' => 'App\Controllers\MateriaController@destroy',
    'GET:materias/grado/{grado}' => 'App\Controllers\MateriaController@porGrado',
    'GET:materias/profesor/{profesorId}' => 'App\Controllers\MateriaController@porProfesor',
    'PUT:materias/{id}/profesor' => 'App\Controllers\MateriaController@asignarProfesor',

    // Notas
    'GET:notas' => 'App\Controllers\NotaController@index',
    'POST:notas' => 'App\Controllers\NotaController@store',
    'GET:notas/{id}' => 'App\Controllers\NotaController@show',
    'PUT:notas/{id}' => 'App\Controllers\NotaController@update',
    'DELETE:notas/{id}' => 'App\Controllers\NotaController@destroy',
    'GET:notas/estudiante/{estudianteId}' => 'App\Controllers\NotaController@porEstudiante',
    'GET:notas/materia/{materiaId}' => 'App\Controllers\NotaController@porMateria',
    'GET:notas/estadisticas' => 'App\Controllers\NotaController@estadisticas',

    // Asistencias
    'GET:asistencias' => 'App\Controllers\AsistenciaController@index',
    'POST:asistencias' => 'App\Controllers\AsistenciaController@registrar',
    'GET:asistencias/{id}' => 'App\Controllers\AsistenciaController@show',
    'PUT:asistencias/{id}' => 'App\Controllers\AsistenciaController@update',
    'DELETE:asistencias/{id}' => 'App\Controllers\AsistenciaController@destroy',
    'GET:asistencias/estudiante/{estudianteId}' => 'App\Controllers\AsistenciaController@porEstudiante',
    'GET:asistencias/materia/{materiaId}' => 'App\Controllers\AsistenciaController@porMateria',
    'GET:asistencias/porcentaje/{estudianteId}/{materiaId}' => 'App\Controllers\AsistenciaController@porcentajeAsistencia',

    // Pagos
    'GET:pagos' => 'App\Controllers\PagoController@index',
    'POST:pagos' => 'App\Controllers\PagoController@store',
    'GET:pagos/{id}' => 'App\Controllers\PagoController@show',
    'PUT:pagos/{id}' => 'App\Controllers\PagoController@update',
    'DELETE:pagos/{id}' => 'App\Controllers\PagoController@destroy',
    'GET:pagos/estudiante/{estudianteId}' => 'App\Controllers\PagoController@porEstudiante',
    'GET:pagos/estado/{estado}' => 'App\Controllers\PagoController@porEstado',
    'GET:pagos/estadisticas' => 'App\Controllers\PagoController@estadisticas',

    // Horarios
    'GET:horarios' => 'App\Controllers\HorarioController@index',
    'POST:horarios' => 'App\Controllers\HorarioController@store',
    'GET:horarios/{id}' => 'App\Controllers\HorarioController@show',
    'PUT:horarios/{id}' => 'App\Controllers\HorarioController@update',
    'DELETE:horarios/{id}' => 'App\Controllers\HorarioController@destroy',
    'GET:horarios/materia/{materiaId}' => 'App\Controllers\HorarioController@porMateria',
    'GET:horarios/dia/{dia}' => 'App\Controllers\HorarioController@porDia',

    // Tareas
    'GET:tareas' => 'App\Controllers\TareaController@index',
    'POST:tareas' => 'App\Controllers\TareaController@store',
    'GET:tareas/{id}' => 'App\Controllers\TareaController@show',
    'PUT:tareas/{id}' => 'App\Controllers\TareaController@update',
    'DELETE:tareas/{id}' => 'App\Controllers\TareaController@destroy',
    'GET:tareas/pendientes' => 'App\Controllers\TareaController@pendientes',
    'GET:tareas/materia/{materiaId}' => 'App\Controllers\TareaController@porMateria',
    'PUT:tareas/{id}/completar' => 'App\Controllers\TareaController@marcarCompleta',

    // Notificaciones
    'GET:notificaciones' => 'App\Controllers\NotificacionController@index',
    'POST:notificaciones' => 'App\Controllers\NotificacionController@store',
    'GET:notificaciones/{id}' => 'App\Controllers\NotificacionController@show',
    'PUT:notificaciones/{id}' => 'App\Controllers\NotificacionController@update',
    'DELETE:notificaciones/{id}' => 'App\Controllers\NotificacionController@destroy',
    'GET:notificaciones/usuario/{usuarioId}' => 'App\Controllers\NotificacionController@porUsuario',
    'GET:notificaciones/usuario/{usuarioId}/no-leidas' => 'App\Controllers\NotificacionController@noLeidas',
    'PUT:notificaciones/{id}/leida' => 'App\Controllers\NotificacionController@marcarLeida',

    // Comentarios
    'GET:comentarios' => 'App\Controllers\ComentarioController@index',
    'POST:comentarios' => 'App\Controllers\ComentarioController@store',
    'GET:comentarios/{id}' => 'App\Controllers\ComentarioController@show',
    'PUT:comentarios/{id}' => 'App\Controllers\ComentarioController@update',
    'DELETE:comentarios/{id}' => 'App\Controllers\ComentarioController@destroy',
    'GET:comentarios/estudiante/{estudianteId}' => 'App\Controllers\ComentarioController@porEstudiante',
    'GET:comentarios/profesor/{profesorId}' => 'App\Controllers\ComentarioController@porProfesor',
    'GET:comentarios/materia/{materiaId}' => 'App\Controllers\ComentarioController@porMateria',

    // Calificaciones Académicas
    'GET:calificaciones-academicas' => 'App\Controllers\CalificacionesAcademicasController@index',
    'GET:calificaciones-academicas/asignaturas' => 'App\Controllers\CalificacionesAcademicasController@getAsignaturas',
    'GET:calificaciones-academicas/periodos' => 'App\Controllers\CalificacionesAcademicasController@getPeriodos',
    'GET:calificaciones-academicas/{id}' => 'App\Controllers\CalificacionesAcademicasController@show',
    'POST:calificaciones-academicas' => 'App\Controllers\CalificacionesAcademicasController@store',
    'PUT:calificaciones-academicas/{id}' => 'App\Controllers\CalificacionesAcademicasController@update',
    'DELETE:calificaciones-academicas/{id}' => 'App\Controllers\CalificacionesAcademicasController@destroy',
    'GET:calificaciones-academicas/competencias/{asignaturaId}' => 'App\Controllers\CalificacionesAcademicasController@getCompetencias',
    'POST:calificaciones-academicas/cerrar-periodo' => 'App\Controllers\CalificacionesAcademicasController@cerrarPeriodo',
    'GET:calificaciones-academicas/validar-periodo/{id_asignatura}/{id_periodo}/{id_anio}' => 'App\Controllers\CalificacionesAcademicasController@validarPeriodo',
    'GET:calificaciones-academicas/calcular/{id_estudiante}/{id_asignatura}/{id_periodo}/{id_anio}' => 'App\Controllers\CalificacionesAcademicasController@calcularNotaFinal',
    'GET:calificaciones-academicas/reporte/{id_estudiante}/{id_anio}' => 'App\Controllers\CalificacionesAcademicasController@reporteEstudiante',

    // Calificaciones Técnicas
    'GET:calificaciones-tecnicas' => 'App\Controllers\CalificacionesTecnicasController@index',
    'GET:calificaciones-tecnicas/modulos' => 'App\Controllers\CalificacionesTecnicasController@getModulos',
    'POST:calificaciones-tecnicas/modulos/crear' => 'App\Controllers\CalificacionesTecnicasController@storeModulo',
    'PUT:calificaciones-tecnicas/modulos/{id}' => 'App\Controllers\CalificacionesTecnicasController@updateModulo',
    'GET:calificaciones-tecnicas/estudiantes' => 'App\Controllers\CalificacionesTecnicasController@getEstudiantesTecnicos',
    'GET:calificaciones-tecnicas/estudiante/{estudianteId}/modulo/{moduloId}' => 'App\Controllers\CalificacionesTecnicasController@getCalificaciones',
    'GET:calificaciones-tecnicas/{id}' => 'App\Controllers\CalificacionesTecnicasController@show',
    'POST:calificaciones-tecnicas/guardar' => 'App\Controllers\CalificacionesTecnicasController@store',
    'POST:calificaciones-tecnicas' => 'App\Controllers\CalificacionesTecnicasController@store',
    'PUT:calificaciones-tecnicas/{id}' => 'App\Controllers\CalificacionesTecnicasController@update',
    'DELETE:calificaciones-tecnicas/{id}' => 'App\Controllers\CalificacionesTecnicasController@destroy',
    'POST:calificaciones-tecnicas/cerrar-modulo' => 'App\Controllers\CalificacionesTecnicasController@cerrarModulo',
    'GET:calificaciones-tecnicas/validar-modulo/{id_modulo}/{id_anio}' => 'App\Controllers\CalificacionesTecnicasController@validarModulo',
    'GET:calificaciones-tecnicas/validar-porcentajes/{id_modulo}' => 'App\Controllers\CalificacionesTecnicasController@validarPorcentajes',
    'GET:calificaciones-tecnicas/calcular/{id_estudiante}/{id_modulo}/{id_anio}' => 'App\Controllers\CalificacionesTecnicasController@calcularNotaFinal',
    'GET:calificaciones-tecnicas/ra-activos/{id_modulo}' => 'App\Controllers\CalificacionesTecnicasController@raActivos',
    'GET:calificaciones-tecnicas/reporte/{id_estudiante}/{id_anio}' => 'App\Controllers\CalificacionesTecnicasController@reporteEstudiante',

    // Roles
    'GET:roles' => 'App\Controllers\RolController@index',
    'POST:roles' => 'App\Controllers\RolController@store',
    'GET:roles/{id}' => 'App\Controllers\RolController@show',
    'PUT:roles/{id}' => 'App\Controllers\RolController@update',
    'DELETE:roles/{id}' => 'App\Controllers\RolController@destroy',

    // Institución
    'GET:institucion' => 'App\Controllers\InstitucionController@index',
    'POST:institucion' => 'App\Controllers\InstitucionController@update',
    'DELETE:institucion/imagen/{tipo}' => 'App\Controllers\InstitucionController@deleteImage',

    // Consulta Pública
    'GET:consulta/notas/{matricula}' => 'App\Controllers\ConsultaController@consultar',
];

$route_key = "{$method}:{$uri}";
$controller_action = null;
$params = [];

foreach ($routes as $pattern => $action) {
    $regex = preg_replace_callback('/{(\w+)}/', function($matches) {
        return '(?<' . $matches[1] . '>[^/]+)';
    }, $pattern);
    $regex = str_replace(':', '\:', $regex);
    
    if (preg_match("~^{$regex}$~", $route_key, $matches)) {
        $controller_action = $action;
        foreach ($matches as $key => $value) {
            if (!is_numeric($key)) {
                $params[$key] = $value;
            }
        }
        break;
    }
}

if (!$controller_action) {
    Response::json(['error' => 'Ruta no encontrada'], 404);
}

[$controller_class, $method_name] = explode('@', $controller_action);

try {
    if (!class_exists($controller_class)) {
        Response::json(['error' => "Controlador no encontrado: $controller_class"], 500);
    }
    
    $controller = new $controller_class();
    
    if (!method_exists($controller, $method_name)) {
        Response::json(['error' => "Método no encontrado: $method_name"], 500);
    }
    
    $result = $controller->$method_name(...array_values($params));
    
    if (!is_null($result)) {
        echo $result;
    }
} catch (\Exception $e) {
    Response::json(['error' => $e->getMessage()], 500);
}
