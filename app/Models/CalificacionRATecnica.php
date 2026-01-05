<?php

namespace App\Models;

class CalificacionRATecnica extends Model
{
    protected $table = 'calificaciones_ra_tecnicas';
    protected $primaryKey = 'id_calificacion';
    protected $fillable = [
        'id_estudiante', 
        'id_modulo', 
        'numero_ra', 
        'valor_porcentual', 
        'nota_oportunidad_1', 
        'nota_oportunidad_2', 
        'nota_oportunidad_3', 
        'nota_final_ra', 
        'estado_ra'
    ];
}
