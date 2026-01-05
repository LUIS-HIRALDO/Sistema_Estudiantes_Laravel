<?php

namespace App\Controllers;

use App\Models\Tarea;

class TareaController extends Controller
{
    public function __construct()
    {
        $this->model = Tarea::class;
    }

    public function pendientes()
    {
        $tareas = Tarea::all();
        $pendientes = array_filter($tareas, fn($t) => $t->estado === 'pendiente');
        return response()->json($pendientes, 200);
    }

    public function porMateria($materiaId)
    {
        $tareas = Tarea::where('materia_id', $materiaId);
        return response()->json($tareas, 200);
    }

    public function marcarCompleta($tareaId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            return response()->json(['error' => 'Método no permitido'], 405);
        }

        $tarea = Tarea::find($tareaId);
        if (!$tarea) {
            return response()->json(['error' => 'Tarea no encontrada'], 404);
        }

        $tarea->estado = 'completada';
        $tarea->save();

        return response()->json(['message' => 'Tarea marcada como completada', 'data' => $tarea->toArray()], 200);
    }
}
