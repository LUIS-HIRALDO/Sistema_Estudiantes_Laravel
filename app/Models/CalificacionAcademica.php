<?php

namespace App\Models;

use PDO;

class CalificacionAcademica extends Model
{
    protected $table = 'notas_academicas';
    protected $fillable = [
        'id_estudiante',
        'id_competencia',
        'id_periodo',
        'id_anio',
        'nota',
        'rp'
    ];

    /**
     * Obtener calificaciones académicas con filtros
     * 
     * @param int $id_estudiante
     * @param int $id_periodo
     * @param int $id_asignatura
     * @param int $id_anio
     * @return array
     */
    public static function obtenerCalificaciones($id_estudiante = null, $id_periodo = null, $id_asignatura = null, $id_anio = null)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT 
                na.id_nota,
                    na.id_estudiante,
                    na.id_competencia,
                    na.id_periodo,
                na.id_anio,
                    na.nota,
                    na.rp,
                c.id_asignatura,
                    c.nombre as competencia,
                    c.bloque,
                    a.nombre as asignatura,
                    a.codigo as codigo_asignatura,
                    e.nombre as estudiante_nombre,
                    e.apellido as estudiante_apellido,
                    e.matricula,
                    p.nombre as periodo_nombre,
                    e.grado
                FROM notas_academicas na
                JOIN competencias c ON na.id_competencia = c.id_competencia
                JOIN asignaturas a ON c.id_asignatura = a.id_asignatura
                JOIN estudiantes e ON na.id_estudiante = e.id
                JOIN periodos p ON na.id_periodo = p.id_periodo
                WHERE 1=1";
        
        $params = [];

        if ($id_anio) {
            $sql .= " AND na.id_anio = ?";
            $params[] = $id_anio;
        }
        
        if ($id_estudiante) {
            $sql .= " AND na.id_estudiante = ?";
            $params[] = $id_estudiante;
        }
        
        if ($id_periodo) {
            $sql .= " AND na.id_periodo = ?";
            $params[] = $id_periodo;
        }
        
        if ($id_asignatura) {
            $sql .= " AND c.id_asignatura = ?";
            $params[] = $id_asignatura;
        }
        
        $sql .= " ORDER BY e.nombre, a.nombre, c.bloque, na.id_periodo";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validar que todas las competencias tengan nota en un período
     * 
     * @param int $id_estudiante
     * @param int $id_asignatura
     * @param int $id_periodo
     * @param int $id_anio
     * @return array {válido: bool, faltantes: array, mensaje: string}
     */
    public static function validarCompletitud($id_estudiante, $id_asignatura, $id_periodo, $id_anio)
    {
        $db = \App\Database::getInstance();
        
        // Obtener todas las competencias de la asignatura
        $sqlCompetencias = "SELECT id_competencia FROM competencias WHERE id_asignatura = ?";
        $stmtComp = $db->getConnection()->prepare($sqlCompetencias);
        $stmtComp->execute([$id_asignatura]);
        $competencias = $stmtComp->fetchAll(PDO::FETCH_COLUMN);
        
        // Obtener notas registradas
        $sqlNotas = "SELECT id_competencia FROM notas_academicas WHERE id_estudiante = ? AND id_asignatura = ? AND id_periodo = ? AND id_anio = ?";
        $stmtNotas = $db->getConnection()->prepare($sqlNotas);
        $stmtNotas->execute([$id_estudiante, $id_asignatura, $id_periodo, $id_anio]);
        $notasRegistradas = $stmtNotas->fetchAll(PDO::FETCH_COLUMN);
        
        // Comparar
        $faltantes = array_diff($competencias, $notasRegistradas);
        
        return [
            'válido' => empty($faltantes),
            'faltantes' => $faltantes,
            'mensaje' => empty($faltantes) ? 'OK' : 'Faltan ' . count($faltantes) . ' competencia(s)'
        ];
    }

    /**
     * Validar rango de notas (0-100)
     * 
     * @param float $nota
     * @param float $rp
     * @return array {válido: bool, errores: array}
     */
    public static function validarNotas($nota, $rp = null)
    {
        $errores = [];
        
        if ($nota !== null && ($nota < 0 || $nota > 100)) {
            $errores[] = 'Nota debe estar entre 0 y 100';
        }
        
        if ($rp !== null && ($rp < 0 || $rp > 100)) {
            $errores[] = 'RP debe estar entre 0 y 100';
        }
        
        return [
            'válido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Validar que período no esté cerrado
     * 
     * @param int $id_asignatura
     * @param int $id_periodo
     * @param int $id_anio
     * @return array {válido: bool, cerrado: bool, mensaje: string}
     */
    public static function validarPeriodoActivo($id_asignatura, $id_periodo, $id_anio)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT COUNT(*) as cerrado FROM cierres_asignaturas 
                WHERE id_asignatura = ? AND id_periodo = ? AND id_anio = ? AND bloqueado = TRUE";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$id_asignatura, $id_periodo, $id_anio]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $cerrado = $resultado['cerrado'] > 0;
        
        return [
            'válido' => !$cerrado,
            'cerrado' => $cerrado,
            'mensaje' => $cerrado ? 'Período cerrado, no se puede editar' : 'OK'
        ];
    }

    /**
     * Calcular bloque 70%
     * 
     * @param int $id_estudiante
     * @param int $id_asignatura
     * @param int $id_periodo
     * @param int $id_anio
     * @return float
     */
    public static function calcularBloque70($id_estudiante, $id_asignatura, $id_periodo, $id_anio)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT AVG(CASE WHEN na.rp IS NOT NULL THEN na.rp ELSE na.nota END) as promedio
                FROM notas_academicas na
                JOIN competencias c ON na.id_competencia = c.id_competencia
                WHERE na.id_estudiante = ?
                  AND c.id_asignatura = ?
                  AND na.id_periodo = ?
                  AND na.id_anio = ?
                  AND c.bloque = '70'";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$id_estudiante, $id_asignatura, $id_periodo, $id_anio]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $promedio = $resultado['promedio'] ?? 0;
        
        return round($promedio * 0.70, 2);
    }

    /**
     * Calcular bloque 30%
     * 
     * @param int $id_estudiante
     * @param int $id_asignatura
     * @param int $id_periodo
     * @param int $id_anio
     * @return float
     */
    public static function calcularBloque30($id_estudiante, $id_asignatura, $id_periodo, $id_anio)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT AVG(CASE WHEN na.rp IS NOT NULL THEN na.rp ELSE na.nota END) as promedio
                FROM notas_academicas na
                JOIN competencias c ON na.id_competencia = c.id_competencia
                WHERE na.id_estudiante = ?
                  AND c.id_asignatura = ?
                  AND na.id_periodo = ?
                  AND na.id_anio = ?
                  AND c.bloque = '30'";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$id_estudiante, $id_asignatura, $id_periodo, $id_anio]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $promedio = $resultado['promedio'] ?? 0;
        
        return round($promedio * 0.30, 2);
    }

    /**
     * Calcular nota final del período
     * 
     * @param int $id_estudiante
     * @param int $id_asignatura
     * @param int $id_periodo
     * @param int $id_anio
     * @return float
     */
    public static function calcularNotaFinal($id_estudiante, $id_asignatura, $id_periodo, $id_anio)
    {
        $bloque70 = self::calcularBloque70($id_estudiante, $id_asignatura, $id_periodo, $id_anio);
        $bloque30 = self::calcularBloque30($id_estudiante, $id_asignatura, $id_periodo, $id_anio);
        
        return round($bloque70 + $bloque30, 2);
    }

    /**
     * Obtener estado de calificación (APROBADO/REPROBADO)
     * 
     * @param float $notaFinal
     * @return string
     */
    public static function obtenerEstado($notaFinal)
    {
        if ($notaFinal >= 70) {
            return 'APROBADO';
        }
        return 'REPROBADO';
    }

    /**
     * Guardar calificaciones académicas (transacción)
     * 
     * @param int $id_estudiante
     * @param int $id_asignatura
     * @param int $id_periodo
     * @param int $id_anio
     * @param array $notas Array de [id_competencia => ['nota' => x, 'rp' => y]]
     * @return array {éxito: bool, mensaje: string, notaFinal: float}
     */
    public static function guardarCalificaciones($id_estudiante, $id_asignatura, $id_periodo, $id_anio, $notas)
    {
        $db = \App\Database::getInstance();
        
        try {
            // Validaciones previas
            $validacion_periodo = self::validarPeriodoActivo($id_asignatura, $id_periodo, $id_anio);
            if (!$validacion_periodo['válido']) {
                return [
                    'éxito' => false,
                    'mensaje' => $validacion_periodo['mensaje']
                ];
            }
            
            // Validar cada nota
            foreach ($notas as $id_competencia => $datos) {
                $validacion = self::validarNotas($datos['nota'] ?? null, $datos['rp'] ?? null);
                if (!$validacion['válido']) {
                    return [
                        'éxito' => false,
                        'mensaje' => 'Error en competencia ' . $id_competencia . ': ' . implode(', ', $validacion['errores'])
                    ];
                }
            }
            
            // Iniciar transacción
            $db->beginTransaction();
            
            // Insertar/actualizar notas
            $sql = "INSERT INTO notas_academicas (id_estudiante, id_competencia, id_periodo, id_anio, nota, rp, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                    nota = VALUES(nota),
                    rp = VALUES(rp),
                    updated_at = NOW()";
            
            $stmt = $db->getConnection()->prepare($sql);
            
            foreach ($notas as $id_competencia => $datos) {
                $nota = $datos['nota'];
                $rp = $datos['rp'];

                // Si hay RP, la nota P1 se vuelve 0 (o se mantiene si ya era 0)
                // Si no hay RP, se guarda la nota P1 tal cual
                if ($rp !== null && $rp !== '') {
                    $nota = 0; // P1 se anula visualmente
                } else {
                    // Si no hay RP, aseguramos que nota tenga valor (o 0 si es null)
                    $nota = ($nota !== null && $nota !== '') ? $nota : 0;
                    $rp = null; // Aseguramos que RP sea null
                }

                $stmt->execute([
                    $id_estudiante,
                    $id_competencia,
                    $id_periodo,
                    $id_anio,
                    $nota,
                    $rp
                ]);
            }
            
            // Calcular nota final
            $notaFinal = self::calcularNotaFinal($id_estudiante, $id_asignatura, $id_periodo, $id_anio);
            
            // Confirmar transacción
            $db->commit();
            
            return [
                'éxito' => true,
                'mensaje' => 'Calificaciones guardadas correctamente',
                'notaFinal' => $notaFinal,
                'estado' => self::obtenerEstado($notaFinal)
            ];
            
        } catch (\Exception $e) {
            $db->rollBack();
            return [
                'éxito' => false,
                'mensaje' => 'Error al guardar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cerrar período (solo admin)
     * 
     * @param int $id_asignatura
     * @param int $id_periodo
     * @param int $id_anio
     * @param int $usuario_id
     * @return array {éxito: bool, mensaje: string}
     */
    public static function cerrarPeriodo($id_asignatura, $id_periodo, $id_anio, $usuario_id)
    {
        $db = \App\Database::getInstance();
        
        try {
            // Verificar que todas las notas estén completas
            $sql = "SELECT COUNT(DISTINCT e.id) as total_estudiantes,
                           COUNT(DISTINCT na.id_estudiante) as con_notas
                    FROM estudiantes e
                    JOIN notas_academicas na ON e.id = na.id_estudiante
                    WHERE e.modalidad = 'ACADEMICA'
                      AND na.id_asignatura = ?
                      AND na.id_periodo = ?
                      AND na.id_anio = ?";
            
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([$id_asignatura, $id_periodo, $id_anio]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado['total_estudiantes'] !== $resultado['con_notas']) {
                return [
                    'éxito' => false,
                    'mensaje' => 'Faltan notas para ' . ($resultado['total_estudiantes'] - $resultado['con_notas']) . ' estudiante(s)'
                ];
            }
            
            // Insertar cierre
            $sql = "INSERT INTO cierres_asignaturas (id_asignatura, id_periodo, id_anio, fecha_cierre, usuario_cierre, bloqueado)
                    VALUES (?, ?, ?, NOW(), ?, TRUE)
                    ON DUPLICATE KEY UPDATE
                    fecha_cierre = NOW(),
                    usuario_cierre = ?,
                    bloqueado = TRUE";
            
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([$id_asignatura, $id_periodo, $id_anio, $usuario_id, $usuario_id]);
            
            return [
                'éxito' => true,
                'mensaje' => 'Período cerrado correctamente'
            ];
            
        } catch (\Exception $e) {
            return [
                'éxito' => false,
                'mensaje' => 'Error al cerrar período: ' . $e->getMessage()
            ];
        }
    }
}
