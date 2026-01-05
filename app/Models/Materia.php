<?php

namespace App\Models;

class Materia extends Model
{
    protected $table = 'materias';
    protected $fillable = ['nombre', 'descripcion', 'horas_semana', 'profesor_id', 'grado', 'creditos', 'estado', 'codigo'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributes['estado'] = $this->attributes['estado'] ?? 'activo';
    }
}
