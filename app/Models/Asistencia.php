<?php

namespace App\Models;

class Asistencia extends Model
{
    protected $table = 'asistencias';
    protected $fillable = ['estudiante_id', 'materia_id', 'fecha', 'estado', 'observaciones'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributes['estado'] = $this->attributes['estado'] ?? 'presente';
    }
}
