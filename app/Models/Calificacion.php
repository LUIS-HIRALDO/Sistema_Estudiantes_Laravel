<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    protected $fillable = [
        'estudiante_id',
        'materia_id',
        'calificacion',
        'periodo',
        'observaciones'
    ];

    protected $casts = [
        'calificacion' => 'decimal:2',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }
}
