<?php

namespace App\Models;

class Tarea extends Model
{
    protected $table = 'tareas';
    protected $fillable = ['titulo', 'descripcion', 'materia_id', 'profesor_id', 'fecha_vencimiento', 'puntos', 'estado'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributes['estado'] = $this->attributes['estado'] ?? 'pendiente';
    }
}
