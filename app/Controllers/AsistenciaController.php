<?php

namespace App\Controllers;

use App\Models\Asistencia;

class AsistenciaController extends Controller
{
    public function __construct()
    {
        $this->model = Asistencia::class;
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return response()->json(['error' => 'Método no permitido'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $asistencia = Asistencia::create($data);

        return response()->json(['message' => 'Asistencia registrada exitosamente', 'data' => $asistencia->toArray()], 201);
    }

    public function porEstudiante($estudianteId)
    {
        $asistencias = Asistencia::where('estudiante_id', $estudianteId);
        return response()->json($asistencias, 200);
    }

    public function porMateria($materiaId)
    {
        $asistencias = Asistencia::where('materia_id', $materiaId);
        return response()->json($asistencias, 200);
    }

    public function porcentajeAsistencia($estudianteId, $materiaId)
    {
        $asistencias = Asistencia::where('estudiante_id', $estudianteId);
        $asistencias = array_filter($asistencias, fn($a) => $a->materia_id == $materiaId);

        if (empty($asistencias)) {
            return response()->json(['porcentaje' => 0], 200);
        }

        $presentes = count(array_filter($asistencias, fn($a) => $a->estado === 'presente'));
        $porcentaje = ($presentes / count($asistencias)) * 100;

        return response()->json(['porcentaje' => round($porcentaje, 2)], 200);
    }
}
