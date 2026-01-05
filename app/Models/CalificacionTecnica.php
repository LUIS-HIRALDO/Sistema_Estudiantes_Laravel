<?php

namespace App\Models;

use PDO;

class CalificacionTecnica extends Model
{
    protected $table = 'notas_tecnicas';
    protected $fillable = [
        'id_estudiante',
        'id_ra',
        'id_anio',
        'nota',
        'rp'
    ];

    /**
     * Obtener calificaciones técnicas con filtros
     * 
     * @param int $id_estudiante
     * @param int $id_modulo
     * @param int $id_anio
     * @return array
     */
    public static function obtenerCalificaciones($id_estudiante = null, $id_modulo = null, $id_anio = null)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT 
                    nt.id_nota,
                    nt.id_estudiante,
                    nt.id_ra,
                    nt.nota,
                    nt.rp,
                    ra.codigo_ra,
                    ra.numero_ra,
                    ra.descripcion,
                    ra.activo,
                    ra.porcentaje,
                    ra.id_modulo,
                    mf.nombre as modulo,
                    mf.codigo as codigo_modulo,
                    e.nombre as estudiante_nombre,
                    e.apellido as estudiante_apellido,
                    e.matricula,
                    e.grado,
                    e.especialidad
                FROM notas_tecnicas nt
                JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id_ra
                JOIN modulos_formativos mf ON ra.id_modulo = mf.id_modulo
                JOIN estudiantes e ON nt.id_estudiante = e.id
                WHERE nt.id_anio = ? AND e.modalidad = 'TECNICA'";
        
        $params = [$id_anio];
        
        if ($id_estudiante) {
            $sql .= " AND nt.id_estudiante = ?";
            $params[] = $id_estudiante;
        }
        
        if ($id_modulo) {
            $sql .= " AND ra.id_modulo = ?";
            $params[] = $id_modulo;
        }
        
        $sql .= " ORDER BY e.nombre, mf.nombre, ra.numero_ra";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener RA activos de un módulo
     * 
     * @param int $id_modulo
     * @return array
     */
    public static function obtenerRAActivos($id_modulo)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT id_ra, numero_ra, codigo_ra, descripcion, porcentaje, activo
                FROM resultados_aprendizaje
                WHERE id_modulo = ? AND activo = TRUE
                ORDER BY numero_ra";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$id_modulo]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validar suma de porcentajes de RA activos
     * 
     * @param int $id_modulo
     * @return array {válido: bool, suma: float, faltante: float, mensaje: string}
     */
    public static function validarPorcentajes($id_modulo)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT SUM(porcentaje) as suma
                FROM resultados_aprendizaje
                WHERE id_modulo = ? AND activo = TRUE";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$id_modulo]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $suma = $resultado['suma'] ?? 0;
        
        $válido = $suma == 100;
        $faltante = 100 - $suma;
        
        $mensaje = $válido ? 'OK' : 'Porcentajes suman ' . $suma . '%, falta ' . $faltante . '%';
        
        return [
            'válido' => $válido,
            'suma' => $suma,
            'faltante' => $faltante,
            'mensaje' => $mensaje
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
        
        if ($nota === null && $rp === null) {
            $errores[] = 'Se requiere nota o RP';
        }
        
        return [
            'válido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Validar que módulo no esté cerrado
     * 
     * @param int $id_modulo
     * @param int $id_anio
     * @return array {válido: bool, cerrado: bool, mensaje: string}
     */
    public static function validarModuloActivo($id_modulo, $id_anio)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT COUNT(*) as cerrado FROM cierre_modulos 
                WHERE id_modulo = ? AND id_anio = ? AND bloqueado = TRUE";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$id_modulo, $id_anio]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $cerrado = $resultado['cerrado'] > 0;
        
        return [
            'válido' => !$cerrado,
            'cerrado' => $cerrado,
            'mensaje' => $cerrado ? 'Módulo cerrado, no se puede editar' : 'OK'
        ];
    }

    /**
     * Calcular nota final ponderada del módulo
     * 
     * @param int $id_estudiante
     * @param int $id_modulo
     * @param int $id_anio
     * @return float
     */
    public static function calcularNotaFinal($id_estudiante, $id_modulo, $id_anio)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT SUM(
                    CASE WHEN nt.rp IS NOT NULL THEN nt.rp ELSE nt.nota END 
                    * (ra.porcentaje / 100)
                ) as notaFinal
                FROM notas_tecnicas nt
                JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id_ra
                WHERE nt.id_estudiante = ?
                  AND ra.id_modulo = ?
                  AND nt.id_anio = ?
                  AND ra.activo = TRUE";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$id_estudiante, $id_modulo, $id_anio]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $notaFinal = $resultado['notaFinal'] ?? 0;
        
        return round($notaFinal, 2);
    }

    /**
     * Obtener desglose de cálculo (para auditoría)
     * 
     * @param int $id_estudiante
     * @param int $id_modulo
     * @param int $id_anio
     * @return array
     */
    public static function obtenerDesglose($id_estudiante, $id_modulo, $id_anio)
    {
        $db = \App\Database::getInstance();
        
        $sql = "SELECT 
                    ra.numero_ra,
                    ra.codigo_ra,
                    ra.descripcion,
                    ra.porcentaje,
                    CASE WHEN nt.rp IS NOT NULL THEN nt.rp ELSE nt.nota END as notaUsada,
                    CASE WHEN nt.rp IS NOT NULL THEN nt.rp ELSE nt.nota END * (ra.porcentaje / 100) as aporte
                FROM notas_tecnicas nt
                JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id_ra
                WHERE nt.id_estudiante = ?
                  AND ra.id_modulo = ?
                  AND nt.id_anio = ?
                  AND ra.activo = TRUE
                ORDER BY ra.numero_ra";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$id_estudiante, $id_modulo, $id_anio]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
     * Guardar calificaciones técnicas (transacción)
     * 
     * @param int $id_estudiante
     * @param int $id_modulo
     * @param int $id_anio
     * @param array $notas Array de [id_ra => ['nota' => x, 'rp' => y]]
     * @return array {éxito: bool, mensaje: string, notaFinal: float, estado: string}
     */
    public static function guardarCalificaciones($id_estudiante, $id_modulo, $id_anio, $notas)
    {
        $db = \App\Database::getInstance();
        
        try {
            // Validaciones previas
            $validacion_porcentajes = self::validarPorcentajes($id_modulo);
            if (!$validacion_porcentajes['válido']) {
                return [
                    'éxito' => false,
                    'mensaje' => $validacion_porcentajes['mensaje']
                ];
            }
            
            $validacion_modulo = self::validarModuloActivo($id_modulo, $id_anio);
            if (!$validacion_modulo['válido']) {
                return [
                    'éxito' => false,
                    'mensaje' => $validacion_modulo['mensaje']
                ];
            }
            
            // Obtener RA activos
            $raActivos = self::obtenerRAActivos($id_modulo);
            $raActivosIds = array_column($raActivos, 'id_ra');
            
            // Validar que todos los RA activos tengan nota
            foreach ($raActivos as $ra) {
                if (!isset($notas[$ra['id_ra']])) {
                    return [
                        'éxito' => false,
                        'mensaje' => 'RA' . $ra['numero_ra'] . ' sin nota registrada'
                    ];
                }
                
                $validacion = self::validarNotas($notas[$ra['id_ra']]['nota'] ?? null, $notas[$ra['id_ra']]['rp'] ?? null);
                if (!$validacion['válido']) {
                    return [
                        'éxito' => false,
                        'mensaje' => 'Error en RA' . $ra['numero_ra'] . ': ' . implode(', ', $validacion['errores'])
                    ];
                }
            }
            
            // Iniciar transacción
            $db->beginTransaction();
            
            // Insertar/actualizar notas
            $sql = "INSERT INTO notas_tecnicas (id_estudiante, id_ra, id_anio, nota, rp, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                    nota = VALUES(nota),
                    rp = VALUES(rp),
                    updated_at = NOW()";
            
            $stmt = $db->getConnection()->prepare($sql);
            
            foreach ($raActivosIds as $id_ra) {
                $stmt->execute([
                    $id_estudiante,
                    $id_ra,
                    $id_anio,
                    $notas[$id_ra]['nota'] ?? null,
                    $notas[$id_ra]['rp'] ?? null
                ]);
            }
            
            // Calcular nota final
            $notaFinal = self::calcularNotaFinal($id_estudiante, $id_modulo, $id_anio);
            
            // Confirmar transacción
            $db->commit();
            
            return [
                'éxito' => true,
                'mensaje' => 'Calificaciones guardadas correctamente',
                'notaFinal' => $notaFinal,
                'estado' => self::obtenerEstado($notaFinal),
                'desglose' => self::obtenerDesglose($id_estudiante, $id_modulo, $id_anio)
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
     * Cerrar módulo (solo admin)
     * 
     * @param int $id_modulo
     * @param int $id_anio
     * @param int $usuario_id
     * @return array {éxito: bool, mensaje: string}
     */
    public static function cerrarModulo($id_modulo, $id_anio, $usuario_id)
    {
        $db = \App\Database::getInstance();
        
        try {
            // Validar porcentajes
            $validacion = self::validarPorcentajes($id_modulo);
            if (!$validacion['válido']) {
                return [
                    'éxito' => false,
                    'mensaje' => 'No se puede cerrar: ' . $validacion['mensaje']
                ];
            }
            
            // Verificar que todas las notas estén completas
            $sql = "SELECT COUNT(DISTINCT e.id) as total_estudiantes,
                           COUNT(DISTINCT nt.id_estudiante) as con_notas
                    FROM estudiantes e
                    JOIN notas_tecnicas nt ON e.id = nt.id_estudiante
                    WHERE e.modalidad = 'TECNICA'
                      AND nt.id_anio = ?
                      AND EXISTS (
                        SELECT 1 FROM resultados_aprendizaje ra 
                        WHERE ra.id_modulo = ? AND ra.activo = TRUE
                      )";
            
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([$id_anio, $id_modulo]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado['total_estudiantes'] > 0 && $resultado['total_estudiantes'] !== $resultado['con_notas']) {
                return [
                    'éxito' => false,
                    'mensaje' => 'Faltan notas para ' . ($resultado['total_estudiantes'] - $resultado['con_notas']) . ' estudiante(s)'
                ];
            }
            
            // Insertar cierre
            $sql = "INSERT INTO cierre_modulos (id_modulo, id_anio, fecha_cierre, usuario_cierre, bloqueado)
                    VALUES (?, ?, NOW(), ?, TRUE)
                    ON DUPLICATE KEY UPDATE
                    fecha_cierre = NOW(),
                    usuario_cierre = ?,
                    bloqueado = TRUE";
            
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([$id_modulo, $id_anio, $usuario_id, $usuario_id]);
            
            return [
                'éxito' => true,
                'mensaje' => 'Módulo cerrado correctamente'
            ];
            
        } catch (\Exception $e) {
            return [
                'éxito' => false,
                'mensaje' => 'Error al cerrar módulo: ' . $e->getMessage()
            ];
        }
    }
}
