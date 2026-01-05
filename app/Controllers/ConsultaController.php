<?php

namespace App\Controllers;

class ConsultaController extends Controller
{
    public function consultar($matricula)
    {
        // No requireAuth() porque es público
        
        if (empty($matricula)) {
            return $this->response(['error' => 'Matrícula requerida'], 400);
        }

        $db = \App\Database::getInstance();
        $conn = $db->getConnection();

        // 1. Buscar en Estudiantes Académicos
        $stmt = $conn->prepare("SELECT * FROM estudiantes WHERE matricula = ?");
        $stmt->execute([$matricula]);
        $estudiante = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($estudiante) {
            return $this->consultarAcademico($estudiante, $conn);
        }

        // 2. Buscar en Estudiantes Técnicos
        $stmt = $conn->prepare("SELECT * FROM estudiantes_tecnicos WHERE matricula = ?");
        $stmt->execute([$matricula]);
        $estudianteTecnico = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($estudianteTecnico) {
            return $this->consultarTecnico($estudianteTecnico, $conn);
        }

        return $this->response(['error' => 'Estudiante no encontrado'], 404);
    }

    private function consultarAcademico($estudiante, $conn)
    {
        // Obtener calificaciones académicas
        // Estructura: Asignatura -> Periodo -> Competencias (70% y 30%)
        
        $sql = "
            SELECT 
                a.nombre as asignatura,
                p.nombre as periodo,
                p.id_periodo,
                c.bloque,
                c.nombre as competencia,
                n.nota,
                n.rp
            FROM notas_academicas n
            JOIN competencias c ON n.id_competencia = c.id_competencia
            JOIN asignaturas a ON c.id_asignatura = a.id_asignatura
            JOIN periodos p ON n.id_periodo = p.id_periodo
            WHERE n.id_estudiante = ?
            ORDER BY a.nombre, p.id_periodo, c.bloque ASC, c.id_competencia
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$estudiante['id']]);
        $notasRaw = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Agrupar por Asignatura -> Periodo
        $asignaturas = [];
        
        foreach ($notasRaw as $row) {
            $asig = $row['asignatura'];
            $per = $row['periodo']; // P1, P2...
            
            if (!isset($asignaturas[$asig])) {
                $asignaturas[$asig] = [];
            }
            if (!isset($asignaturas[$asig][$per])) {
                $asignaturas[$asig][$per] = [
                    'bloque70' => [],
                    'bloque30' => [],
                    'detalles' => []
                ];
            }
            
            // Lógica de nota efectiva: Si hay RP, sustituye a la nota
            $notaEfectiva = ($row['rp'] !== null && $row['rp'] > 0) ? $row['rp'] : $row['nota'];
            
            if ($row['bloque'] == '70') {
                $asignaturas[$asig][$per]['bloque70'][] = $notaEfectiva;
            } else {
                $asignaturas[$asig][$per]['bloque30'][] = $notaEfectiva;
            }

            // Guardar detalle para visualización
            $asignaturas[$asig][$per]['detalles'][] = [
                'competencia' => $row['competencia'],
                'nota' => $row['nota'],
                'rp' => $row['rp'],
                'bloque' => $row['bloque']
            ];
        }

        // Calcular nota final por periodo y formatear para la vista
        $tabla = [];
        
        foreach ($asignaturas as $asigName => $periodos) {
            foreach ($periodos as $perName => $data) {
                
                // Calcular promedio bloque 70
                $prom70 = 0;
                if (count($data['bloque70']) > 0) {
                    $prom70 = array_sum($data['bloque70']) / count($data['bloque70']);
                }
                
                // Calcular promedio bloque 30
                $prom30 = 0;
                if (count($data['bloque30']) > 0) {
                    $prom30 = array_sum($data['bloque30']) / count($data['bloque30']);
                }
                
                // Nota final del periodo
                $notaFinal = ($prom70 * 0.70) + ($prom30 * 0.30);
                
                $tabla[] = [
                    'modulo' => $asigName,
                    'ra' => $perName, // Mostramos el Periodo como si fuera un RA
                    'nota' => round($notaFinal, 0), // Redondeamos a entero
                    'valor' => 100,
                    'desglose' => $data['detalles']
                ];
            }
        }

        return $this->response([
            'estudiante' => $estudiante['nombre'] . ' ' . $estudiante['apellido'],
            'tipo' => 'Académico',
            'notas' => $tabla
        ]);
    }

    private function consultarTecnico($estudiante, $conn)
    {
        $sql = "
            SELECT 
                m.nombre as modulo,
                cra.numero_ra,
                cra.nota_final_ra as nota,
                cra.valor_porcentual,
                cra.estado_ra
            FROM calificaciones_ra_tecnicas cra
            JOIN modulos_formativos m ON cra.id_modulo = m.id_modulo
            WHERE cra.id_estudiante = ?
            ORDER BY m.nombre, cra.numero_ra
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$estudiante['id']]); 
        
        $notas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $tabla = [];
        foreach ($notas as $row) {
            $tabla[] = [
                'modulo' => $row['modulo'],
                'ra' => 'RA ' . $row['numero_ra'],
                'nota' => $row['nota'],
                'valor' => $row['valor_porcentual']
            ];
        }

        return $this->response([
            'estudiante' => $estudiante['nombre'] . ' ' . $estudiante['apellido'],
            'tipo' => 'Técnico',
            'notas' => $tabla
        ]);
    }
}
