<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiante extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'fecha_nacimiento',
        'direccion',
        'genero',
        'nombre_padre',
        'nombre_madre',
        'telefono_emergencia',
        'estado'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }
}
