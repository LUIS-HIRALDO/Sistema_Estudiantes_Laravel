<?php

namespace App\Controllers;

use App\Models\CalificacionRATecnica;
use App\Models\EstudianteTecnico;

class CalificacionesTecnicasController extends Controller
{
    /**
     * Verificar si el usuario actual tiene permiso sobre el módulo
     */
    private function verificarAccesoModulo($moduloId)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            throw new \Exception("Usuario no autenticado");
        }

        // Admin tiene acceso total
        if ($user->rol === 'admin') {
            return true;
        }

        // Profesor debe ser el dueño del módulo
        if ($user->rol === 'profesor') {
            $db = \App\Database::getInstance();
            
            // Obtener ID de profesor del usuario actual
            $stmtProf = $db->getConnection()->prepare("SELECT id FROM profesores WHERE usuario_id = ?");
            $stmtProf->execute([$user->id]);
            $profesorId = $stmtProf->fetchColumn();

            if (!$profesorId) {
                throw new \Exception("Usuario profesor sin perfil asociado");
            }

            // Verificar dueño del módulo
            $stmtMod = $db->getConnection()->prepare("SELECT id_profesor FROM modulos_formativos WHERE id_modulo = ?");
            $stmtMod->execute([$moduloId]);
            $ownerId = $stmtMod->fetchColumn();

            if ($ownerId != $profesorId) {
                throw new \Exception("No tiene permiso para acceder a este módulo");
            }
            return true;
        }

        throw new \Exception("Rol no autorizado");
    }

    /**
     * GET /calificaciones-tecnicas/modulos
     * Obtener lista de módulos formativos
     */
    public function getModulos()
    {
        $this->requireAuth();
        
        try {
            $db = \App\Database::getInstance();
            $sql = "
                SELECT m.*, p.nombre as nombre_profesor, p.apellido as apellido_profesor 
                FROM modulos_formativos m
                LEFT JOIN profesores p ON m.id_profesor = p.id
                WHERE m.estado = 'activo' 
                ORDER BY m.nombre
            ";
            $stmt = $db->getConnection()->query($sql);
            $modulos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->response(['data' => $modulos]);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /calificaciones-tecnicas/profesores
     * Obtener lista de profesores activos
     */
    public function getProfesores()
    {
        $this->requireAuth();
        try {
            $db = \App\Database::getInstance();
            // Solo profesores técnicos
            $stmt = $db->getConnection()->query("SELECT id, nombre, apellido FROM profesores WHERE estado = 'activo' AND tipo = 'tecnico' ORDER BY nombre, apellido");
            $profesores = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $this->response(['data' => $profesores]);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /calificaciones-tecnicas/estudiantes
     * Obtener lista de estudiantes técnicos
     */
    public function getEstudiantesTecnicos()
    {
        $this->requireAuth();
        try {
            $db = \App\Database::getInstance();
            $stmt = $db->getConnection()->query("SELECT * FROM estudiantes_tecnicos ORDER BY apellido, nombre");
            $estudiantes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $this->response(['data' => $estudiantes]);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /calificaciones-tecnicas/modulos/crear
     * Crear nuevo módulo técnico
     */
    public function storeModulo()
    {
        $this->requireAuth();
        $data = $this->getJson();

        if (empty($data['nombre']) || empty($data['cantidad_ra'])) {
            return $this->response(['error' => 'Nombre y Cantidad de RAs son obligatorios'], 422);
        }

        try {
            $db = \App\Database::getInstance();
            $stmt = $db->getConnection()->prepare("
                INSERT INTO modulos_formativos (nombre, codigo, especialidad, grado, cantidad_ra, id_profesor)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['nombre'],
                $data['codigo'] ?? '',
                $data['especialidad'] ?? '',
                $data['grado'] ?? '',
                $data['cantidad_ra'],
                $data['id_profesor'] ?? null
            ]);

            return $this->response(['mensaje' => 'Módulo creado correctamente']);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /calificaciones-tecnicas/modulos/{id}
     * Actualizar módulo técnico
     */
    public function updateModulo($id)
    {
        $this->requireAuth();
        $data = $this->getJson();

        try {
            $db = \App\Database::getInstance();
            $stmt = $db->getConnection()->prepare("
                UPDATE modulos_formativos 
                SET nombre = ?, codigo = ?, especialidad = ?, grado = ?, cantidad_ra = ?, id_profesor = ?
                WHERE id_modulo = ?
            ");
            $stmt->execute([
                $data['nombre'],
                $data['codigo'],
                $data['especialidad'],
                $data['grado'],
                $data['cantidad_ra'],
                $data['id_profesor'] ?? null,
                $id
            ]);

            return $this->response(['mensaje' => 'Módulo actualizado correctamente']);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /calificaciones-tecnicas/guardar
     * Guardar calificaciones técnicas con lógica de 3 oportunidades
     */
    public function store()
    {
        $this->requireAuth();
        $data = $this->getJson();

        // Validar datos básicos
        if (empty($data['id_modulo']) || empty($data['id_estudiante']) || empty($data['ras'])) {
            return $this->response(['error' => 'Faltan datos requeridos'], 422);
        }

        // Verificar permisos
        try {
            $this->verificarAccesoModulo($data['id_modulo']);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 403);
        }

        $db = \App\Database::getInstance();
        $conn = $db->getConnection();

        try {
            $conn->beginTransaction();

            // 2. Guardar Calificaciones de cada RA
            foreach ($data['ras'] as $ra) {
                // Verificar si ya existe calificación para este estudiante, modulo y numero_ra
                $stmtCheck = $conn->prepare("SELECT id_calificacion FROM calificaciones_ra_tecnicas WHERE id_estudiante = ? AND id_modulo = ? AND numero_ra = ?");
                $stmtCheck->execute([$data['id_estudiante'], $data['id_modulo'], $ra['numero_ra']]);
                $existing = $stmtCheck->fetch();

                if ($existing) {
                    // Update
                    $stmtUpdate = $conn->prepare("UPDATE calificaciones_ra_tecnicas SET 
                        valor_porcentual = ?, 
                        nota_oportunidad_1 = ?, 
                        nota_oportunidad_2 = ?, 
                        nota_oportunidad_3 = ?, 
                        nota_final_ra = ?, 
                        estado_ra = ?,
                        updated_at = NOW()
                        WHERE id_calificacion = ?");
                    
                    $stmtUpdate->execute([
                        $ra['valor_porcentual'],
                        $ra['nota1'],
                        $ra['nota2'],
                        $ra['nota3'],
                        $ra['nota_final'],
                        $ra['estado'],
                        $existing['id_calificacion']
                    ]);
                } else {
                    // Insert
                    $stmtInsert = $conn->prepare("INSERT INTO calificaciones_ra_tecnicas 
                        (id_estudiante, id_modulo, numero_ra, valor_porcentual, nota_oportunidad_1, nota_oportunidad_2, nota_oportunidad_3, nota_final_ra, estado_ra)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmtInsert->execute([
                        $data['id_estudiante'],
                        $data['id_modulo'],
                        $ra['numero_ra'],
                        $ra['valor_porcentual'],
                        $ra['nota1'],
                        $ra['nota2'],
                        $ra['nota3'],
                        $ra['nota_final'],
                        $ra['estado']
                    ]);
                }
            }

            $conn->commit();
            return $this->response(['mensaje' => 'Calificaciones guardadas correctamente']);

        } catch (\Exception $e) {
            $conn->rollBack();
            return $this->response(['error' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /calificaciones-tecnicas/estudiante/{estudianteId}/modulo/{moduloId}
     * Obtener calificaciones de un estudiante en un módulo
     */
    public function getCalificaciones($estudianteId, $moduloId)
    {
        $this->requireAuth();
        
        try {
            // Verificar permisos
            $this->verificarAccesoModulo($moduloId);

            $db = \App\Database::getInstance();
            $stmt = $db->getConnection()->prepare("
                SELECT * FROM calificaciones_ra_tecnicas 
                WHERE id_estudiante = ? AND id_modulo = ?
                ORDER BY numero_ra ASC
            ");
            $stmt->execute([$estudianteId, $moduloId]);
            $calificaciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->response(['data' => $calificaciones]);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }
}

