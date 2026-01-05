<?php

namespace App\Models;

class EstudianteTecnico extends Model
{
    protected $table = 'estudiantes_tecnicos';
    protected $fillable = [
        'nombre', 
        'apellido', 
        'matricula', 
        'email', 
        'telefono', 
        'grado', 
        'seccion', 
        'especialidad'
    ];
}
