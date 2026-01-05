<?php

namespace App\Models;

class Nota extends Model
{
    protected $table = 'notas';
    protected $fillable = ['estudiante_id', 'materia_id', 'parcial_1', 'parcial_2', 'parcial_3', 'promedio', 'estado'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (isset($this->attributes['parcial_1']) && isset($this->attributes['parcial_2']) && isset($this->attributes['parcial_3'])) {
            $this->calcularPromedio();
        }
    }

    public function calcularPromedio()
    {
        if (isset($this->attributes['parcial_1'], $this->attributes['parcial_2'], $this->attributes['parcial_3'])) {
            $promedio = ($this->attributes['parcial_1'] + $this->attributes['parcial_2'] + $this->attributes['parcial_3']) / 3;
            $this->attributes['promedio'] = round($promedio, 2);
        }
    }
}
