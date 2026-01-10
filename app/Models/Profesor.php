<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesor extends Model
{
    protected $table = 'profesores';

    protected $fillable = [
        'codigo',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'especialidad',
        'fecha_contratacion',
        'estado'
    ];

    protected $casts = [
        'fecha_contratacion' => 'date',
    ];

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }
}
