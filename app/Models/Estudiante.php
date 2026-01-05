<?php

namespace App\Models;

class Estudiante extends Model
{
    protected $table = 'estudiantes';
    protected $fillable = ['nombre', 'apellido', 'email', 'telefono', 'grado', 'numero_matricula', 'fecha_nacimiento', 'direccion', 'fecha_inscripcion', 'estado', 'usuario_id', 'matricula', 'seccion'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributes['estado'] = $this->attributes['estado'] ?? 'activo';
    }
}
