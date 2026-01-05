<?php

namespace App\Models;

class Profesor extends Model
{
    protected $table = 'profesores';
    protected $fillable = ['nombre', 'apellido', 'email', 'cedula', 'telefono', 'especialidad', 'tipo', 'materia_id', 'estado', 'titulo', 'fecha_contratacion', 'usuario_id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributes['estado'] = $this->attributes['estado'] ?? 'activo';
    }
}
