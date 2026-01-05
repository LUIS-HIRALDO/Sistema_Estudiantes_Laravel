<?php

namespace App\Controllers;

use App\Models\Rol;

class RolController extends Controller
{
    public function __construct()
    {
        $this->model = Rol::class;
    }
}
