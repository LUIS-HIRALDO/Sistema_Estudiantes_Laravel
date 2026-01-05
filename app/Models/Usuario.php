<?php

namespace App\Models;

class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $fillable = ['email', 'password', 'nombre', 'apellido', 'cedula', 'rol', 'estado', 'profesor_id', 'estudiante_id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!isset($this->attributes['estado'])) {
            $this->attributes['estado'] = 'activo';
        }
    }

    public function setPassword($password)
    {
        $this->attributes['password'] = password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->attributes['password'] ?? '');
    }
}
