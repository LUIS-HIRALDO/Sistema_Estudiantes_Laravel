<?php

namespace App\Controllers;

use App\Models\Pago;

class PagoController extends Controller
{
    public function __construct()
    {
        $this->model = Pago::class;
    }

    public function porEstudiante($estudianteId)
    {
        $pagos = Pago::where('estudiante_id', $estudianteId);
        return response()->json($pagos, 200);
    }

    public function porEstado($estado)
    {
        $pagos = Pago::where('estado', $estado);
        return response()->json($pagos, 200);
    }

    public function estadisticas()
    {
        $pagos = Pago::all();
        $pagados = array_filter($pagos, fn($p) => $p->estado === 'pagado');
        $monto_total = array_sum(array_map(fn($p) => $p->monto, $pagos));
        $monto_pagado = array_sum(array_map(fn($p) => $p->monto, $pagados));

        return response()->json([
            'total_pagos' => count($pagos),
            'pagados' => count($pagados),
            'pendientes' => count($pagos) - count($pagados),
            'monto_total' => $monto_total,
            'monto_pagado' => $monto_pagado,
            'monto_pendiente' => $monto_total - $monto_pagado,
        ], 200);
    }
}
