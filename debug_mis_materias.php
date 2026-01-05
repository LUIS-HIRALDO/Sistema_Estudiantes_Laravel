<?php
require_once 'app/helpers.php';
require_once 'app/Config.php';
require_once 'app/Database.php';
require_once 'app/Models/Model.php';
require_once 'app/Models/Usuario.php';
require_once 'app/Models/Profesor.php';

use App\Models\Usuario;
use App\Models\Profesor;
use App\Database;

try {
    echo "=== DEBUG MIS MATERIAS ===\n";
    
    // 1. Simular autenticación (obtener usuario)
    $email = 'luishiraldo8@gmail.com';
    echo "Buscando usuario: $email\n";
    
    $usuarios = Usuario::where('email', $email);
    $usuario = !empty($usuarios) ? $usuarios[0] : null;
    
    if (!$usuario) {
        die("Error: Usuario no encontrado\n");
    }
    
    echo "Usuario encontrado: ID " . $usuario->getId() . "\n";
    
    // 2. Lógica de ProfesorController::misMaterias
    
    // Buscar profesor asociado al usuario
    echo "Buscando profesor por usuario_id: " . $usuario->getId() . "\n";
    $profesores = Profesor::where('usuario_id', $usuario->getId());
    $profesor = !empty($profesores) ? $profesores[0] : null;
    
    if (!$profesor) {
        echo "No encontrado por ID. Buscando por email (fallback)...\n";
        // Intentar buscar por email si no está vinculado por ID (fallback)
        $profesores = Profesor::where('email', $usuario->email);
        $profesor = !empty($profesores) ? $profesores[0] : null;

        // Si se encontró por email, vincularlo automáticamente
        if ($profesor) {
            echo "Encontrado por email. Vinculando...\n";
            $profesor->usuario_id = $usuario->getId();
            $profesor->save();
        }
    }

    if (!$profesor) {
        echo "NO SE ENCONTRÓ PERFIL DE PROFESOR.\n";
        // Si no existe perfil de profesor, retornar estructura vacía
        $response = [
            'profesor' => [
                'id' => null,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email' => $usuario->email
            ],
            'academicas' => [],
            'tecnicas' => []
        ];
        print_r($response);
        exit;
    }
    
    echo "Profesor encontrado: ID " . $profesor->getId() . "\n";

    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Obtener materias académicas
    echo "Consultando materias académicas para profesor_id " . $profesor->getId() . "...\n";
    $stmtAcademicas = $conn->prepare("SELECT * FROM materias WHERE profesor_id = ?");
    $stmtAcademicas->execute([$profesor->getId()]);
    $academicas = $stmtAcademicas->fetchAll(PDO::FETCH_ASSOC);
    echo "Materias académicas encontradas: " . count($academicas) . "\n";

    // Obtener módulos técnicos
    echo "Consultando módulos técnicos para id_profesor " . $profesor->getId() . "...\n";
    $stmtTecnicas = $conn->prepare("SELECT * FROM modulos_formativos WHERE id_profesor = ?");
    $stmtTecnicas->execute([$profesor->getId()]);
    $tecnicas = $stmtTecnicas->fetchAll(PDO::FETCH_ASSOC);
    echo "Módulos técnicos encontrados: " . count($tecnicas) . "\n";

    $response = [
        'profesor' => $profesor->toArray(),
        'academicas' => $academicas,
        'tecnicas' => $tecnicas
    ];
    
    echo "\n=== RESPUESTA FINAL ===\n";
    print_r($response);

} catch (Exception $e) {
    echo "Excepción: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
