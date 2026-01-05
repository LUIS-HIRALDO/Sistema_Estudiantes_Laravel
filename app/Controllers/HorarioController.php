<?php

namespace App\Controllers;

use App\Models\Horario;

class HorarioController extends Controller
{
    public function __construct()
    {
        $this->model = Horario::class;
    }

    public function porMateria($materiaId)
    {
        $horarios = Horario::where('materia_id', $materiaId);
        return response()->json($horarios, 200);
    }

    public function porDia($dia)
    {
        $horarios = Horario::where('dia', $dia);
        return response()->json($horarios, 200);
    }
}
