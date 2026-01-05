<?php

namespace App\Controllers;

use App\Models\CalificacionAcademica;
use App\Models\Estudiante;
use App\Models\Asignatura;
use App\Models\Periodo;

class CalificacionesAcademicasController extends Controller
{
    /**
     * GET /calificaciones-academicas
     * Obtener todas las calificaciones académicas con filtros
     */
    public function index()
    {
        $this->requireAuth();
        
        $id_estudiante = $_GET['id_estudiante'] ?? null;
        $id_periodo = $_GET['id_periodo'] ?? null;
        $id_asignatura = $_GET['id_asignatura'] ?? null;
        // Si no se envía id_anio, no filtrar por año para encontrar lo que sea que se haya guardado
        // O mejor, usar el año actual si no se envía, pero asegurarnos que el frontend envíe el correcto.
        // Para debug, vamos a permitir null en id_anio si no se envía, para ver todo.
        $id_anio = $_GET['id_anio'] ?? null; 
        $detailed = $_GET['detailed'] ?? false;
        
        try {
            $calificaciones = CalificacionAcademica::obtenerCalificaciones(
                $id_estudiante,
                $id_periodo,
                $id_asignatura,
                $id_anio
            );

            // Si se solicita vista detallada (raw data), retornar sin pivotear
            if ($detailed) {
                return $this->response(['data' => $calificaciones]);
            }
            
            // Paso 1: Agrupar por estudiante+periodo+asignatura para calcular nota final del periodo
            $periodoData = [];
            foreach ($calificaciones as $calif) {
                $idAsignatura = $calif['id_asignatura'] ?? null;
                $key = $calif['id_estudiante'] . '_' . $calif['id_periodo'] . '_' . $idAsignatura;
                
                if (!isset($periodoData[$key])) {
                    $notaFinal = CalificacionAcademica::calcularNotaFinal(
                        $calif['id_estudiante'],
                        $idAsignatura,
                        $calif['id_periodo'],
                        $id_anio
                    );
                    
                    $periodoData[$key] = [
                        'id_estudiante' => $calif['id_estudiante'],
                        'id_asignatura' => $idAsignatura,
                        'id_periodo' => $calif['id_periodo'],
                        'notaFinal' => $notaFinal,
                        // Datos extra para el pivote
                        'estudiante_nombre' => $calif['estudiante_nombre'],
                        'estudiante_apellido' => $calif['estudiante_apellido'],
                        'matricula' => $calif['matricula'],
                        'grado' => $calif['grado'],
                        'asignatura' => $calif['asignatura'],
                        'rp' => $calif['rp'] // Tomamos el RP si existe en alguna nota
                    ];
                } else {
                    // Si encontramos un RP en otra nota del mismo periodo, lo guardamos
                    if (!empty($calif['rp'])) {
                        $periodoData[$key]['rp'] = $calif['rp'];
                    }
                }
            }

            // Paso 2: Pivotear a formato Student+Subject con columnas P1, P2, P3, P4
            $pivoted = [];
            foreach ($periodoData as $pData) {
                $pivotKey = $pData['id_estudiante'] . '_' . $pData['id_asignatura'];
                
                if (!isset($pivoted[$pivotKey])) {
                    $pivoted[$pivotKey] = [
                        'id' => $pivotKey, // ID sintético para el frontend
                        'estudiante_id' => $pData['id_estudiante'],
                        'materia_id' => $pData['id_asignatura'],
                        'p1' => 0,
                        'p2' => 0,
                        'p3' => 0,
                        'p4' => 0,
                        'recuperacion' => null,
                        // Campos informativos extra
                        'estudiante' => $pData['estudiante_nombre'] . ' ' . $pData['estudiante_apellido'],
                        'matricula' => $pData['matricula'],
                        'grado' => $pData['grado'],
                        'asignatura' => $pData['asignatura']
                    ];
                }

                // Asignar nota al periodo correspondiente (asumiendo id_periodo 1..4)
                $pIndex = 'p' . $pData['id_periodo'];
                if (isset($pivoted[$pivotKey][$pIndex])) {
                    $pivoted[$pivotKey][$pIndex] = $pData['notaFinal'];
                }

                // Asignar recuperación si existe
                if (!empty($pData['rp'])) {
                    $pivoted[$pivotKey]['recuperacion'] = $pData['rp'];
                }
            }
            
            return $this->response(['data' => array_values($pivoted)]);
            
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /calificaciones-academicas/competencias/{asignaturaId}
     * Obtener competencias de una asignatura
     */
    public function getCompetencias($asignaturaId)
    {
        $this->requireAuth();
        
        try {
            $db = \App\Database::getInstance();
            $stmt = $db->getConnection()->prepare("
                SELECT * FROM competencias 
                WHERE id_asignatura = ? 
                ORDER BY bloque, id_competencia
            ");
            $stmt->execute([$asignaturaId]);
            $competencias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->response(['data' => $competencias]);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /calificaciones-academicas/asignaturas
     * Obtener lista de asignaturas académicas
     */
    public function getAsignaturas()
    {
        $this->requireAuth();
        
        try {
            $db = \App\Database::getInstance();
            $stmt = $db->getConnection()->query("SELECT * FROM asignaturas WHERE estado = 'activo' ORDER BY nombre");
            $asignaturas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->response(['data' => $asignaturas]);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /calificaciones-academicas/periodos
     * Obtener lista de periodos
     */
    public function getPeriodos()
    {
        $this->requireAuth();
        
        try {
            $db = \App\Database::getInstance();
            $stmt = $db->getConnection()->query("SELECT * FROM periodos ORDER BY numero");
            $periodos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->response(['data' => $periodos]);
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /calificaciones-academicas/{id}
     * Obtener una calificación específica
     */
    public function show($id)
    {
        $this->requireAuth();
        
        try {
            $db = \App\Database::getInstance();
            $stmt = $db->getConnection()->prepare("
                SELECT na.*, c.nombre as competencia, c.bloque, a.nombre as asignatura
                FROM notas_academicas na
                JOIN competencias c ON na.id_competencia = c.id
                JOIN asignaturas a ON c.id_asignatura = a.id
                WHERE na.id = ?
            ");
            $stmt->execute([$id]);
            $calificacion = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$calificacion) {
                return $this->response(['error' => 'Calificación no encontrada'], 404);
            }
            
            return $this->response($calificacion);
            
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /calificaciones-academicas
     * Crear/guardar calificaciones académicas
     */
    public function store()
    {
        $this->requireAuth();
        
        $data = $this->getJson();
        
        // Validar datos requeridos
        if (empty($data['id_estudiante']) || empty($data['id_asignatura']) || 
            empty($data['id_periodo']) || empty($data['id_anio']) || empty($data['notas'])) {
            return $this->response([
                'éxito' => false,
                'mensaje' => 'Faltan datos requeridos'
            ], 422);
        }
        
        $resultado = CalificacionAcademica::guardarCalificaciones(
            $data['id_estudiante'],
            $data['id_asignatura'],
            $data['id_periodo'],
            $data['id_anio'],
            $data['notas']
        );
        
        return $this->response($resultado, $resultado['éxito'] ? 201 : 422);
    }

    /**
     * PUT /calificaciones-academicas/{id}
     * Actualizar una calificación
     */
    public function update($id)
    {
        $this->requireAuth();
        
        $data = $this->getJson();
        
        try {
            $db = \App\Database::getInstance();
            
            // Obtener calificación actual
            $stmt = $db->getConnection()->prepare("SELECT * FROM notas_academicas WHERE id = ?");
            $stmt->execute([$id]);
            $calif = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$calif) {
                return $this->response(['error' => 'Calificación no encontrada'], 404);
            }
            
            // Validar período activo
            $validacion = CalificacionAcademica::validarPeriodoActivo(
                $calif['id_asignatura'],
                $calif['id_periodo'],
                $calif['id_anio']
            );
            
            if (!$validacion['válido']) {
                return $this->response(['error' => $validacion['mensaje']], 422);
            }
            
            // Validar notas
            $validacion_notas = CalificacionAcademica::validarNotas(
                $data['nota'] ?? $calif['nota'],
                $data['rp'] ?? $calif['rp']
            );
            
            if (!$validacion_notas['válido']) {
                return $this->response(['error' => implode(', ', $validacion_notas['errores'])], 422);
            }
            
            // Actualizar
            $sql = "UPDATE notas_academicas SET 
                    nota = ?, 
                    rp = ?, 
                    updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([
                $data['nota'] ?? $calif['nota'],
                $data['rp'] ?? $calif['rp'],
                $id
            ]);
            
            // Calcular nota final
            $notaFinal = CalificacionAcademica::calcularNotaFinal(
                $calif['id_estudiante'],
                $calif['id_asignatura'],
                $calif['id_periodo'],
                $calif['id_anio']
            );
            
            return $this->response([
                'éxito' => true,
                'mensaje' => 'Calificación actualizada',
                'notaFinal' => $notaFinal,
                'estado' => CalificacionAcademica::obtenerEstado($notaFinal)
            ]);
            
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /calificaciones-academicas/{id}
     * Eliminar una calificación
     */
    public function destroy($id)
    {
        $this->requireAuth();
        
        try {
            $db = \App\Database::getInstance();
            
            // Obtener calificación
            $stmt = $db->getConnection()->prepare("SELECT * FROM notas_academicas WHERE id = ?");
            $stmt->execute([$id]);
            $calif = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$calif) {
                return $this->response(['error' => 'Calificación no encontrada'], 404);
            }
            
            // Validar período activo
            $validacion = CalificacionAcademica::validarPeriodoActivo(
                $calif['id_asignatura'],
                $calif['id_periodo'],
                $calif['id_anio']
            );
            
            if (!$validacion['válido']) {
                return $this->response(['error' => $validacion['mensaje']], 422);
            }
            
            // Eliminar
            $stmt = $db->getConnection()->prepare("DELETE FROM notas_academicas WHERE id = ?");
            $stmt->execute([$id]);
            
            return $this->response([
                'éxito' => true,
                'mensaje' => 'Calificación eliminada'
            ]);
            
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /calificaciones-academicas/cerrar-periodo
     * Cerrar un período
     */
    public function cerrarPeriodo()
    {
        $this->requireAuth();
        $this->requireAdmin();
        
        $data = $this->getJson();
        
        if (empty($data['id_asignatura']) || empty($data['id_periodo']) || empty($data['id_anio'])) {
            return $this->response(['error' => 'Faltan datos requeridos'], 422);
        }
        
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        
        $resultado = CalificacionAcademica::cerrarPeriodo(
            $data['id_asignatura'],
            $data['id_periodo'],
            $data['id_anio'],
            $usuario_id
        );
        
        return $this->response($resultado, $resultado['éxito'] ? 200 : 422);
    }

    /**
     * GET /calificaciones-academicas/validar-periodo/{id_asignatura}/{id_periodo}/{id_anio}
     * Validar que período esté activo
     */
    public function validarPeriodo($id_asignatura, $id_periodo, $id_anio)
    {
        $this->requireAuth();
        
        $resultado = CalificacionAcademica::validarPeriodoActivo($id_asignatura, $id_periodo, $id_anio);
        
        return $this->response($resultado);
    }

    /**
     * GET /calificaciones-academicas/calcular/{id_estudiante}/{id_asignatura}/{id_periodo}/{id_anio}
     * Calcular nota final de un período
     */
    public function calcularNotaFinal($id_estudiante, $id_asignatura, $id_periodo, $id_anio)
    {
        $this->requireAuth();
        
        $notaFinal = CalificacionAcademica::calcularNotaFinal(
            $id_estudiante,
            $id_asignatura,
            $id_periodo,
            $id_anio
        );
        
        $bloque70 = CalificacionAcademica::calcularBloque70(
            $id_estudiante,
            $id_asignatura,
            $id_periodo,
            $id_anio
        );
        
        $bloque30 = CalificacionAcademica::calcularBloque30(
            $id_estudiante,
            $id_asignatura,
            $id_periodo,
            $id_anio
        );
        
        return $this->response([
            'notaFinal' => $notaFinal,
            'bloque70' => $bloque70,
            'bloque30' => $bloque30,
            'estado' => CalificacionAcademica::obtenerEstado($notaFinal)
        ]);
    }

    /**
     * GET /calificaciones-academicas/reporte/{id_estudiante}/{id_anio}
     * Obtener reporte de calificaciones por estudiante y año
     */
    public function reporteEstudiante($id_estudiante, $id_anio)
    {
        $this->requireAuth();
        
        try {
            $db = \App\Database::getInstance();
            
            // Obtener datos del estudiante
            $stmt = $db->getConnection()->prepare("SELECT * FROM estudiantes WHERE id = ? AND modalidad = 'ACADEMICA'");
            $stmt->execute([$id_estudiante]);
            $estudiante = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$estudiante) {
                return $this->response(['error' => 'Estudiante no encontrado'], 404);
            }
            
            // Obtener calificaciones
            $calificaciones = CalificacionAcademica::obtenerCalificaciones(
                $id_estudiante,
                null,
                null,
                $id_anio
            );
            
            // Procesar por asignatura y período
            $reporte = [];
            foreach ($calificaciones as $calif) {
                $asignatura = $calif['codigo_asignatura'];
                $periodo = $calif['periodo_nombre'];
                
                if (!isset($reporte[$asignatura])) {
                    $reporte[$asignatura] = [
                        'asignatura' => $calif['asignatura'],
                        'periodos' => []
                    ];
                }
                
                if (!isset($reporte[$asignatura]['periodos'][$periodo])) {
                    $notaFinal = CalificacionAcademica::calcularNotaFinal(
                        $id_estudiante,
                        $calif['id_asignatura'],
                        $calif['id_periodo'],
                        $id_anio
                    );
                    
                    $reporte[$asignatura]['periodos'][$periodo] = [
                        'notaFinal' => $notaFinal,
                        'estado' => CalificacionAcademica::obtenerEstado($notaFinal),
                        'competencias' => []
                    ];
                }
                
                $reporte[$asignatura]['periodos'][$periodo]['competencias'][] = [
                    'competencia' => $calif['competencia'],
                    'bloque' => $calif['bloque'],
                    'nota' => $calif['nota'],
                    'rp' => $calif['rp'],
                    'notaUsada' => $calif['rp'] ?? $calif['nota']
                ];
            }
            
            return $this->response([
                'estudiante' => [
                    'nombre' => $estudiante['nombre'],
                    'apellido' => $estudiante['apellido'],
                    'matricula' => $estudiante['matricula'],
                    'grado' => $estudiante['grado']
                ],
                'anio' => $id_anio,
                'reporte' => $reporte
            ]);
            
        } catch (\Exception $e) {
            return $this->response(['error' => $e->getMessage()], 500);
        }
    }
}
