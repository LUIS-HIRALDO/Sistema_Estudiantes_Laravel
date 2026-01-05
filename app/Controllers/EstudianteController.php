<?php

namespace App\Controllers;

use App\Models\Estudiante;

class EstudianteController extends Controller
{
    public function __construct()
    {
        $this->model = Estudiante::class;
    }

    public function porGrado($grado)
    {
        $estudiantes = Estudiante::where('grado', $grado);
        return \response()->json($estudiantes, 200);
    }

    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        $estudiante = Estudiante::find($id);
        if (!$estudiante) {
            return \response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        $db = \App\Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            // 1. Eliminar notas académicas
            $db->prepare("DELETE FROM notas_academicas WHERE id_estudiante = ?")->execute([$id]);

            // 2. Eliminar notas técnicas
            $db->prepare("DELETE FROM notas_tecnicas WHERE id_estudiante = ?")->execute([$id]);

            // 3. Eliminar asistencias
            $db->prepare("DELETE FROM asistencias WHERE estudiante_id = ?")->execute([$id]);

            // 4. Eliminar pagos
            $db->prepare("DELETE FROM pagos WHERE estudiante_id = ?")->execute([$id]);

            // 5. Eliminar comentarios
            $db->prepare("DELETE FROM comentarios WHERE estudiante_id = ?")->execute([$id]);

            // 6. Obtener usuario asociado antes de eliminar estudiante
            $usuarioId = $estudiante->usuario_id;

            // 7. Eliminar estudiante
            $estudiante->delete();

            // 8. Eliminar usuario asociado si existe
            if ($usuarioId) {
                $db->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$usuarioId]);
            }

            $db->commit();
            return \response()->json(['message' => 'Estudiante y datos asociados eliminados exitosamente'], 200);

        } catch (\Exception $e) {
            $db->rollBack();
            return \response()->json(['error' => 'Error al eliminar estudiante: ' . $e->getMessage()], 500);
        }
    }
}
