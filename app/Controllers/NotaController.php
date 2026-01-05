<?php

namespace App\Controllers;

use App\Models\Nota;

class NotaController extends Controller
{
    public function __construct()
    {
        $this->model = Nota::class;
    }

    public function porEstudiante($estudianteId)
    {
        $notas = Nota::where('estudiante_id', $estudianteId);
        return response()->json($notas, 200);
    }

    public function porMateria($materiaId)
    {
        $notas = Nota::where('materia_id', $materiaId);
        return response()->json($notas, 200);
    }

    public function estadisticas()
    {
        $notas = Nota::all();
        $promedios = array_map(fn($nota) => $nota->promedio, $notas);

        return response()->json([
            'total' => count($notas),
            'promedio_general' => count($promedios) > 0 ? array_sum($promedios) / count($promedios) : 0,
            'maxima' => max($promedios),
            'minima' => min($promedios),
        ], 200);
    }
}
