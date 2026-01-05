<?php

namespace App\Models;

class Horario extends Model
{
    protected $table = 'horarios';
    protected $fillable = ['materia_id', 'dia', 'hora_inicio', 'hora_fin', 'salon', 'profesor_id'];
}
