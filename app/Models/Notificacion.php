<?php

namespace App\Models;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $fillable = ['usuario_id', 'titulo', 'descripcion', 'tipo', 'prioridad', 'leida', 'enlace'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributes['leida'] = $this->attributes['leida'] ?? false;
        $this->attributes['prioridad'] = $this->attributes['prioridad'] ?? 'normal';
    }
}
