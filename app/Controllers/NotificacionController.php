<?php

namespace App\Controllers;

use App\Models\Notificacion;

class NotificacionController extends Controller
{
    public function __construct()
    {
        $this->model = Notificacion::class;
    }

    public function porUsuario($usuarioId)
    {
        $notificaciones = Notificacion::where('usuario_id', $usuarioId);
        return response()->json($notificaciones, 200);
    }

    public function noLeidas($usuarioId)
    {
        $notificaciones = Notificacion::where('usuario_id', $usuarioId);
        $noLeidas = array_filter($notificaciones, fn($n) => !$n->leida);
        return response()->json($noLeidas, 200);
    }

    public function marcarLeida($notificacionId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            return response()->json(['error' => 'Método no permitido'], 405);
        }

        $notificacion = Notificacion::find($notificacionId);
        if (!$notificacion) {
            return response()->json(['error' => 'Notificación no encontrada'], 404);
        }

        $notificacion->leida = true;
        $notificacion->save();

        return response()->json(['message' => 'Notificación marcada como leída', 'data' => $notificacion->toArray()], 200);
    }
}
