<?php

namespace App\Models;

class Comentario extends Model
{
    protected $table = 'comentarios';
    protected $fillable = ['estudiante_id', 'profesor_id', 'materia_id', 'contenido', 'sentimiento', 'fecha'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributes['sentimiento'] = $this->attributes['sentimiento'] ?? 'neutro';
    }
}
