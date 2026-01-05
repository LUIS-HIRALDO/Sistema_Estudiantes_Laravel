<?php

namespace App\Models;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $fillable = ['estudiante_id', 'concepto', 'monto', 'fecha_pago', 'metodo', 'numero_recibo', 'estado'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributes['estado'] = $this->attributes['estado'] ?? 'pendiente';
    }
}
