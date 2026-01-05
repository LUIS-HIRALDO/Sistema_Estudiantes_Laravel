<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Models\Profesor;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->model = Usuario::class;
    }

    public function show($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return \response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $data = $usuario->toArray();

        // Si es profesor, adjuntar datos del perfil y asignaciones
        if ($usuario->rol === 'profesor') {
            $db = \App\Database::getInstance()->getConnection();
            
            // Buscar perfil de profesor
            $stmt = $db->prepare("SELECT * FROM profesores WHERE usuario_id = ?");
            $stmt->execute([$id]);
            $profesor = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($profesor) {
                $data['profesor'] = $profesor;
                $data['profesor']['asignaciones'] = [];

                if ($profesor['tipo'] === 'academico') {
                    $stmtMat = $db->prepare("SELECT id FROM materias WHERE profesor_id = ?");
                    $stmtMat->execute([$profesor['id']]);
                    $data['profesor']['asignaciones'] = $stmtMat->fetchAll(\PDO::FETCH_COLUMN);
                } elseif ($profesor['tipo'] === 'tecnico') {
                    $stmtMod = $db->prepare("SELECT nombre FROM modulos_formativos WHERE id_profesor = ?");
                    $stmtMod->execute([$profesor['id']]);
                    $data['profesor']['asignaciones'] = $stmtMod->fetchAll(\PDO::FETCH_COLUMN);
                }
            }
        }

        return \response()->json($data, 200);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        
        if (!$data) {
            return \response()->json(['error' => 'Datos inválidos', 'received' => $raw], 400);
        }

        $db = \App\Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            // Filtrar datos para usuario (evitar campos extra como 'tipo', 'especialidad')
            $usuarioData = array_intersect_key($data, array_flip(['nombre', 'apellido', 'email', 'password', 'rol', 'estado', 'cedula']));
            
            // Crear usuario
            $usuario = new Usuario($usuarioData);
            
            // Lógica de contraseña
            if (isset($data['password']) && !empty($data['password'])) {
                $usuario->setPassword($data['password']);
            } elseif (($data['rol'] ?? '') === 'profesor' && !empty($data['cedula'])) {
                $usuario->setPassword($data['cedula']);
            }
            
            $usuario->save();

            // Si es profesor, crear perfil y asignar materias
            if (($data['rol'] ?? '') === 'profesor') {
                if (empty($data['cedula'])) throw new \Exception("Cédula requerida para profesores");

                $profesor = new Profesor();
                $profesor->usuario_id = $usuario->getId();
                $profesor->nombre = $usuario->nombre;
                $profesor->apellido = $usuario->apellido;
                $profesor->email = $usuario->email;
                $profesor->cedula = $data['cedula'];
                $profesor->tipo = $data['tipo'] ?? 'academico';
                $profesor->estado = 'activo';
                $profesor->save();

                // Asignar materias/módulos
                $this->handleProfessorAssignments($profesor->getId(), $data);

                // Vincular usuario con profesor
                $usuario->profesor_id = $profesor->getId();
                $usuario->save();

                // Forzar cambio de contraseña
                $stmt = $db->prepare("UPDATE usuarios SET must_change_password = 1 WHERE id = ?");
                $stmt->execute([$usuario->getId()]);
            }

            $db->commit();
            return \response()->json(['message' => 'Usuario creado exitosamente', 'data' => $usuario->toArray()], 201);

        } catch (\Exception $e) {
            $db->rollBack();
            return \response()->json(['error' => 'Error al crear usuario: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return \response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $db = \App\Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            // Si es profesor, eliminar perfil y desasignar materias
            if ($usuario->rol === 'profesor') {
                // Buscar profesor por usuario_id
                $stmt = $db->prepare("SELECT id FROM profesores WHERE usuario_id = ?");
                $stmt->execute([$id]);
                $profesorId = $stmt->fetchColumn();

                if ($profesorId) {
                    // Desasignar materias
                    $db->prepare("UPDATE modulos_formativos SET id_profesor = NULL WHERE id_profesor = ?")->execute([$profesorId]);
                    $db->prepare("UPDATE materias SET profesor_id = NULL WHERE profesor_id = ?")->execute([$profesorId]);
                    
                    // Eliminar profesor
                    $db->prepare("DELETE FROM profesores WHERE id = ?")->execute([$profesorId]);
                }
            }

            $usuario->delete();
            $db->commit();
            return \response()->json(['message' => 'Usuario y datos asociados eliminados exitosamente'], 200);

        } catch (\Exception $e) {
            $db->rollBack();
            return \response()->json(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    private function handleProfessorAssignments($profesorId, $data)
    {
        $db = \App\Database::getInstance()->getConnection();
        $tipo = $data['tipo'] ?? 'academico';
        $asignaciones = $data['especialidad'] ?? []; // En el frontend enviaremos esto como array de IDs o Nombres

        if (empty($asignaciones)) return;
        if (!is_array($asignaciones)) $asignaciones = [$asignaciones];

        if ($tipo === 'tecnico') {
            $stmt = $db->prepare("UPDATE modulos_formativos SET id_profesor = ? WHERE nombre = ?");
            foreach ($asignaciones as $nombre) {
                $stmt->execute([$profesorId, trim($nombre)]);
            }
        } elseif ($tipo === 'academico') {
            $ids = array_filter($asignaciones, 'is_numeric');
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $params = array_merge([$profesorId], $ids);
                $stmt = $db->prepare("UPDATE materias SET profesor_id = ? WHERE id IN ($placeholders)");
                $stmt->execute($params);
            }
        }
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        /** @var Usuario|null $usuario */
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return \response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        // Si se proporciona contraseña, hashearla
        if (isset($data['password']) && !empty($data['password'])) {
            $usuario->setPassword($data['password']);
            unset($data['password']); // No pasar la contraseña nuevamente a fill()
        }
        
        $usuario->fill($data);
        $usuario->save();

        // Sincronizar relación con profesor mediante Cédula
        if (($data['rol'] ?? $usuario->rol) === 'profesor' && !empty($data['cedula'])) {
            $this->syncProfesorByCedula($usuario, $data['cedula']);
        }

        return \response()->json(['message' => 'Usuario actualizado exitosamente', 'data' => $usuario->toArray()], 200);
    }

    private function syncProfesorByCedula($usuario, $cedula)
    {
        $db = \App\Database::getInstance();
        $conn = $db->getConnection();

        // Desvincular este usuario de cualquier otro profesor para evitar duplicados en usuario_id (UNIQUE)
        $clear = $conn->prepare("UPDATE profesores SET usuario_id = NULL WHERE usuario_id = ?");
        $clear->execute([$usuario->getId()]);

        // Buscar profesor por cédula
        $stmt = $conn->prepare("SELECT id FROM profesores WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $profesor = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($profesor) {
            // Si existe, actualizar usuario_id y datos básicos
            $update = $conn->prepare("UPDATE profesores SET usuario_id = ?, nombre = ?, apellido = ?, email = ? WHERE id = ?");
            $update->execute([
                $usuario->getId(),
                $usuario->nombre,
                $usuario->apellido,
                $usuario->email,
                $profesor['id']
            ]);
            
            // Actualizar referencia en usuario
            $usuario->profesor_id = $profesor['id'];
            $usuario->save();
        } else {
            // Si no existe, crear nuevo profesor
            $insert = $conn->prepare("INSERT INTO profesores (nombre, apellido, email, cedula, usuario_id, estado) VALUES (?, ?, ?, ?, ?, 'activo')");
            $insert->execute([
                $usuario->nombre,
                $usuario->apellido,
                $usuario->email,
                $cedula,
                $usuario->getId()
            ]);
            
            $profesorId = $conn->lastInsertId();
            
            // Actualizar referencia en usuario
            $usuario->profesor_id = $profesorId;
            $usuario->save();
        }
    }
}
