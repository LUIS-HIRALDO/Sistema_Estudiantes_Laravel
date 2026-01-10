<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'grado',
        'seccion',
        'ano_escolar',
        'capacidad',
        'estado'
    ];

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }
}
