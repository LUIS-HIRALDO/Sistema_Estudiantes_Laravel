<?php

namespace App\Controllers;

use App\Models\Profesor;
use App\Models\Usuario;

class ProfesorController extends Controller
{
    public function __construct()
    {
        $this->model = Profesor::class;
    }

    public function store()
    {
        $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        // Validaciones básicas
        if (empty($data['nombre']) || empty($data['apellido']) || empty($data['email']) || empty($data['cedula'])) {
            return $this->response(['error' => 'Faltan datos obligatorios (Nombre, Apellido, Email, Cédula)'], 400);
        }

        // Procesar especialidad (puede ser array o string)
        $modulosToAssign = [];
        if (isset($data['especialidad']) && is_array($data['especialidad'])) {
            $modulosToAssign = $data['especialidad'];
            $data['especialidad'] = implode(", ", $data['especialidad']); // Guardar como string legible
        } elseif (isset($data['especialidad'])) {
            $modulosToAssign = [$data['especialidad']];
        }

        // Verificar si el email ya existe en usuarios
        $existingUser = Usuario::where('email', $data['email']);
        if (!empty($existingUser)) {
            return $this->response(['error' => 'El email ya está registrado en el sistema'], 409);
        }

        try {
            $db = \App\Database::getInstance()->getConnection();
            $db->beginTransaction();

            // Validar disponibilidad de módulos antes de crear nada
            if (isset($data['tipo'])) {
                if ($data['tipo'] === 'tecnico') {
                    $this->validarDisponibilidadModulos($modulosToAssign);
                } elseif ($data['tipo'] === 'academico') {
                    $this->validarDisponibilidadMaterias($modulosToAssign);
                }
            }

            // 1. Crear Usuario
            $usuario = new Usuario();
            $usuario->nombre = $data['nombre'];
            $usuario->apellido = $data['apellido'];
            $usuario->email = $data['email'];
            $usuario->cedula = $data['cedula'];
            $usuario->rol = 'profesor';
            $usuario->estado = 'activo';
            $usuario->setPassword($data['cedula']); // Contraseña inicial = Cédula
            $usuario->save();

            // Actualizar must_change_password
            $stmt = $db->prepare("UPDATE usuarios SET must_change_password = 1 WHERE id = ?");
            $stmt->execute([$usuario->getId()]);

            // 2. Crear Profesor
            $profesor = new Profesor();
            $profesor->usuario_id = $usuario->getId();
            $profesor->nombre = $data['nombre'];
            $profesor->apellido = $data['apellido'];
            $profesor->email = $data['email'];
            $profesor->cedula = $data['cedula'];
            $profesor->especialidad = $data['especialidad'] ?? '';
            $profesor->telefono = $data['telefono'] ?? '';
            $profesor->tipo = $data['tipo'] ?? 'Planta';
            $profesor->estado = 'activo';
            $profesor->save();

            // Asignación automática de módulos
            if (isset($data['tipo'])) {
                if ($data['tipo'] === 'tecnico') {
                    $this->asignarModulos($profesor->getId(), $modulosToAssign);
                } elseif ($data['tipo'] === 'academico') {
                    $this->asignarMateriasAcademicas($profesor->getId(), $modulosToAssign);
                }
            }

            // 3. Vincular Usuario con Profesor
            $usuario->profesor_id = $profesor->getId();
            $usuario->save();

            $db->commit();

            return $this->response(['message' => 'Profesor y usuario creados exitosamente', 'data' => $profesor->toArray()], 201);

        } catch (\Exception $e) {
            $db->rollBack();
            return $this->response(['error' => 'Error al crear profesor: ' . $e->getMessage()], 500);
        }
    }

    public function update($id)
    {
        $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $profesor = Profesor::find($id);
        if (!$profesor) {
            return $this->response(['error' => 'Profesor no encontrado'], 404);
        }

        // Procesar especialidad
        $modulosToAssign = [];
        if (isset($data['especialidad']) && is_array($data['especialidad'])) {
            $modulosToAssign = $data['especialidad'];
            $data['especialidad'] = implode(", ", $data['especialidad']);
        } elseif (isset($data['especialidad'])) {
            $modulosToAssign = [$data['especialidad']];
        }

        try {
            $db = \App\Database::getInstance()->getConnection();
            $db->beginTransaction();

            // Validar disponibilidad de módulos antes de actualizar
            if (isset($data['tipo'])) {
                if ($data['tipo'] === 'tecnico') {
                    $this->validarDisponibilidadModulos($modulosToAssign, $id);
                } elseif ($data['tipo'] === 'academico') {
                    $this->validarDisponibilidadMaterias($modulosToAssign, $id);
                }
            }

            // Actualizar Profesor
            $profesor->fill($data);
            $profesor->save();

            // Gestionar asignaciones
            if (isset($data['tipo'])) {
                if ($data['tipo'] === 'tecnico') {
                    $this->desasignarMateriasAcademicas($profesor->getId()); // Limpiar académicas si cambió a técnico
                    $this->asignarModulos($profesor->getId(), $modulosToAssign);
                } else {
                    $this->desasignarModulos($profesor->getId()); // Limpiar técnicas si cambió a académico
                    $this->asignarMateriasAcademicas($profesor->getId(), $modulosToAssign);
                }
            }

            // Sincronizar Usuario
            if ($profesor->usuario_id) {
                $usuario = Usuario::find($profesor->usuario_id);
                if ($usuario) {
                    if (isset($data['nombre'])) $usuario->nombre = $data['nombre'];
                    if (isset($data['apellido'])) $usuario->apellido = $data['apellido'];
                    if (isset($data['email'])) $usuario->email = $data['email'];
                    if (isset($data['cedula'])) $usuario->cedula = $data['cedula'];
                    $usuario->save();
                }
            }

            $db->commit();
            return $this->response(['message' => 'Profesor actualizado correctamente', 'data' => $profesor->toArray()]);

        } catch (\Exception $e) {
            $db->rollBack();
            return $this->response(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function resetPassword($id)
    {
        $this->requireAuth();
        // Verificar rol de admin si es necesario, por ahora asumimos que requireAuth valida acceso básico
        
        $profesor = Profesor::find($id);
        if (!$profesor) {
            return $this->response(['error' => 'Profesor no encontrado'], 404);
        }

        if (!$profesor->usuario_id) {
            return $this->response(['error' => 'Este profesor no tiene un usuario vinculado'], 400);
        }

        $usuario = Usuario::find($profesor->usuario_id);
        if (!$usuario) {
            return $this->response(['error' => 'Usuario vinculado no encontrado'], 404);
        }

        if (empty($profesor->cedula)) {
            return $this->response(['error' => 'El profesor no tiene cédula registrada para usar como contraseña'], 400);
        }

        try {
            $usuario->setPassword($profesor->cedula);
            $usuario->save();

            $db = \App\Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE usuarios SET must_change_password = 1 WHERE id = ?");
            $stmt->execute([$usuario->getId()]);

            return $this->response(['message' => 'Contraseña restablecida a la cédula del profesor']);
        } catch (\Exception $e) {
            return $this->response(['error' => 'Error al restablecer contraseña: ' . $e->getMessage()], 500);
        }
    }

    public function conMaterias()
    {
        $profesores = Profesor::all();
        foreach ($profesores as $profesor) {
            if ($profesor->materia_id) {
                $profesor->materia = Profesor::find($profesor->materia_id);
            }
        }
        return response()->json($profesores, 200);
    }

    public function misMaterias()
    {
        $this->requireAuth();
        $usuario = $this->getAuthUser();
        
        if (!$usuario) {
            return $this->response(['error' => 'Usuario no autenticado'], 401);
        }
        
        // Buscar profesor asociado al usuario
        $profesores = Profesor::where('usuario_id', $usuario->getId());
        $profesor = !empty($profesores) ? $profesores[0] : null;
        
        if (!$profesor) {
            // Intentar buscar por email si no está vinculado por ID (fallback)
            $profesores = Profesor::where('email', $usuario->email);
            $profesor = !empty($profesores) ? $profesores[0] : null;

            // Si se encontró por email, vincularlo automáticamente
            if ($profesor) {
                $profesor->usuario_id = $usuario->getId();
                $profesor->save();
            }
        }

        if (!$profesor) {
            // Si no existe perfil de profesor, retornar estructura vacía para no romper el frontend
            return $this->response([
                'profesor' => [
                    'id' => null,
                    'nombre' => $usuario->nombre,
                    'apellido' => $usuario->apellido,
                    'email' => $usuario->email
                ],
                'academicas' => [],
                'tecnicas' => []
            ]);
        }

        $db = \App\Database::getInstance();
        $conn = $db->getConnection();

        // Obtener materias académicas
        $stmtAcademicas = $conn->prepare("SELECT * FROM materias WHERE profesor_id = ?");
        $stmtAcademicas->execute([$profesor->getId()]);
        $academicas = $stmtAcademicas->fetchAll(\PDO::FETCH_ASSOC);

        // Obtener módulos técnicos
        $stmtTecnicas = $conn->prepare("SELECT * FROM modulos_formativos WHERE id_profesor = ?");
        $stmtTecnicas->execute([$profesor->getId()]);
        $tecnicas = $stmtTecnicas->fetchAll(\PDO::FETCH_ASSOC);

        return $this->response([
            'profesor' => $profesor->toArray(),
            'academicas' => $academicas,
            'tecnicas' => $tecnicas
        ]);
    }

    public function show($id)
    {
        $this->requireAuth();
        $profesor = Profesor::find($id);
        if (!$profesor) {
            return $this->response(['error' => 'Profesor no encontrado'], 404);
        }
        
        // Obtener módulos asignados actualmente
        $db = \App\Database::getInstance();
        // Usamos TRIM para asegurar que no haya espacios fantasma que rompan la comparación en el frontend
        $stmt = $db->getConnection()->prepare("SELECT TRIM(nombre) FROM modulos_formativos WHERE id_profesor = ?");
        $stmt->execute([$id]);
        $modulos = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        // Obtener materias académicas asignadas
        $stmtMat = $db->getConnection()->prepare("SELECT id FROM materias WHERE profesor_id = ?");
        $stmtMat->execute([$id]);
        $materias = $stmtMat->fetchAll(\PDO::FETCH_COLUMN);

        $data = $profesor->toArray();
        $data['modulos_asignados'] = $modulos;
        $data['materias_asignadas'] = $materias;

        return $this->response($data);
    }



    public function destroy($id)
    {
        $this->requireAuth();
        
        $profesor = Profesor::find($id);
        if (!$profesor) {
            return $this->response(['error' => 'Profesor no encontrado'], 404);
        }

        try {
            $db = \App\Database::getInstance()->getConnection();
            $db->beginTransaction();

            // 1. Liberar materias/módulos
            $this->desasignarModulos($id);
            $this->desasignarMateriasAcademicas($id);

            // 2. Eliminar Usuario asociado si existe
            if ($profesor->usuario_id) {
                $usuario = Usuario::find($profesor->usuario_id);
                if ($usuario) {
                    $usuario->delete();
                }
            }

            // 3. Eliminar Profesor
            $profesor->delete();

            $db->commit();
            return $this->response(['message' => 'Profesor y usuario asociado eliminados exitosamente']);

        } catch (\Exception $e) {
            $db->rollBack();
            return $this->response(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    private function asignarModulos($profesorId, $nombresModulos)
    {
        $db = \App\Database::getInstance();
        $conn = $db->getConnection();
        
        // 1. Limpiar asignaciones previas de este profesor
        $this->desasignarModulos($profesorId);

        // 2. Asignar los nuevos módulos seleccionados
        if (!empty($nombresModulos)) {
            $stmt = $conn->prepare("UPDATE modulos_formativos SET id_profesor = ? WHERE nombre = ?");
            foreach ($nombresModulos as $nombre) {
                $stmt->execute([$profesorId, trim($nombre)]);
            }
        }
    }

    private function desasignarModulos($profesorId)
    {
        $db = \App\Database::getInstance();
        $conn = $db->getConnection();
        $conn->prepare("UPDATE modulos_formativos SET id_profesor = NULL WHERE id_profesor = ?")->execute([$profesorId]);
    }

    private function validarDisponibilidadModulos($nombresModulos, $profesorId = null)
    {
        if (empty($nombresModulos)) return;

        $db = \App\Database::getInstance();
        $conn = $db->getConnection();
        
        // Log para depuración
        \App\Logger::log("Validando módulos: " . json_encode($nombresModulos) . " para Profesor ID: " . ($profesorId ?? 'NULL'));
        
        $placeholders = implode(',', array_fill(0, count($nombresModulos), '?'));
        
        // Usar LEFT JOIN para detectar asignaciones incluso si el profesor no existe (integridad referencial rota)
        // Y TRIM para evitar problemas de espacios
        $sql = "SELECT m.nombre, m.id_profesor, p.nombre as p_nombre, p.apellido as p_apellido 
                FROM modulos_formativos m 
                LEFT JOIN profesores p ON m.id_profesor = p.id 
                WHERE TRIM(m.nombre) IN ($placeholders)";
                
        $stmt = $conn->prepare($sql);
        
        // Asegurar que los nombres de entrada también estén limpios
        $nombresLimpios = array_map('trim', $nombresModulos);
        $stmt->execute($nombresLimpios);
        
        $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        \App\Logger::log("Resultados validación: " . json_encode($resultados));
        
        foreach ($resultados as $row) {
            // Si tiene profesor asignado Y (es un profesor nuevo O es un profesor distinto al actual)
            if ($row['id_profesor'] && ($profesorId === null || $row['id_profesor'] != $profesorId)) {
                $nombreProfesor = $row['p_nombre'] ? "{$row['p_nombre']} {$row['p_apellido']}" : "Profesor ID {$row['id_profesor']}";
                throw new \Exception("El módulo '{$row['nombre']}' ya pertenece a {$nombreProfesor}");
            }
        }
    }

    private function asignarMateriasAcademicas($profesorId, $materiasIds)
    {
        $db = \App\Database::getInstance();
        $conn = $db->getConnection();
        
        // 1. Limpiar asignaciones previas
        $this->desasignarMateriasAcademicas($profesorId);

        // 2. Asignar nuevas
        if (!empty($materiasIds)) {
            // Validar que sean IDs numéricos
            $ids = array_filter($materiasIds, 'is_numeric');
            if (empty($ids)) return;

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "UPDATE materias SET profesor_id = ? WHERE id IN ($placeholders)";
            
            $params = array_merge([$profesorId], $ids);
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
    }

    private function desasignarMateriasAcademicas($profesorId)
    {
        $db = \App\Database::getInstance();
        $conn = $db->getConnection();
        $conn->prepare("UPDATE materias SET profesor_id = NULL WHERE profesor_id = ?")->execute([$profesorId]);
    }

    private function validarDisponibilidadMaterias($materiasIds, $profesorId = null)
    {
        if (empty($materiasIds)) return;
        $ids = array_filter($materiasIds, 'is_numeric');
        if (empty($ids)) return;

        $db = \App\Database::getInstance();
        $conn = $db->getConnection();
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT m.nombre, m.profesor_id, p.nombre as p_nombre, p.apellido as p_apellido 
                FROM materias m 
                LEFT JOIN profesores p ON m.profesor_id = p.id 
                WHERE m.id IN ($placeholders)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($ids);
        $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            if ($row['profesor_id'] && ($profesorId === null || $row['profesor_id'] != $profesorId)) {
                 $nombreProfesor = $row['p_nombre'] ? "{$row['p_nombre']} {$row['p_apellido']}" : "Profesor ID {$row['profesor_id']}";
                throw new \Exception("La materia '{$row['nombre']}' ya pertenece a {$nombreProfesor}");
            }
        }
    }
}
