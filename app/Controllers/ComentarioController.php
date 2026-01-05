<?php

namespace App\Controllers;

use App\Models\Comentario;

class ComentarioController extends Controller
{
    public function __construct()
    {
        $this->model = Comentario::class;
    }

    public function porEstudiante($estudianteId)
    {
        $comentarios = Comentario::where('estudiante_id', $estudianteId);
        return response()->json($comentarios, 200);
    }

    public function porProfesor($profesorId)
    {
        $comentarios = Comentario::where('profesor_id', $profesorId);
        return response()->json($comentarios, 200);
    }

    public function porMateria($materiaId)
    {
        $comentarios = Comentario::where('materia_id', $materiaId);
        return response()->json($comentarios, 200);
    }
}
