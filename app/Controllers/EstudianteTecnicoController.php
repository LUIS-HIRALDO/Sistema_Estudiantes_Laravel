<?php

namespace App\Controllers;

use App\Models\EstudianteTecnico;

class EstudianteTecnicoController extends Controller
{
    public function __construct()
    {
        $this->model = EstudianteTecnico::class;
    }

    public function porGrado($grado)
    {
        $estudiantes = EstudianteTecnico::where('grado', $grado);
        return \response()->json($estudiantes, 200);
    }
}
