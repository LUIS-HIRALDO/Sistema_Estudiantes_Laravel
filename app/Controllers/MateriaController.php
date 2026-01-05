<?php

namespace App\Controllers;

use App\Models\Materia;

class MateriaController extends Controller
{
    public function __construct()
    {
        $this->model = Materia::class;
    }

    public function index()
    {
        $db = \App\Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->query("
            SELECT m.*, p.nombre as prof_nombre, p.apellido as prof_apellido 
            FROM materias m 
            LEFT JOIN profesores p ON m.profesor_id = p.id
            ORDER BY m.nombre ASC
        ");
        $materias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return \response()->json(['data' => $materias], 200);
    }

    public function porGrado($grado)
    {
        $materias = Materia::where('grado', $grado);
        return response()->json($materias, 200);
    }

    public function porProfesor($profesorId)
    {
        $materias = Materia::where('profesor_id', $profesorId);
        return response()->json($materias, 200);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        // Crear en Materias
        $item = $this->model::create($data);
        
        // Sincronizar con Asignaturas
        $this->syncAsignatura($item);

        return \response()->json(['message' => 'Recurso creado exitosamente', 'data' => $item->toArray()], 201);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        $item = $this->model::find($id);
        if (!$item) {
            return \response()->json(['error' => 'Recurso no encontrado'], 404);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $item->fill($data);
        $item->save();
        
        // Sincronizar con Asignaturas
        $this->syncAsignatura($item);

        return \response()->json(['message' => 'Recurso actualizado exitosamente', 'data' => $item->toArray()], 200);
    }

    private function syncAsignatura($materia)
    {
        try {
            $db = \App\Database::getInstance();
            $conn = $db->getConnection();
            
            // Buscar si existe asignatura con el mismo nombre
            $stmt = $conn->prepare("SELECT * FROM asignaturas WHERE nombre = ?");
            $stmt->execute([$materia->nombre]);
            $asignatura = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($asignatura) {
                // Actualizar
                $stmtUpd = $conn->prepare("UPDATE asignaturas SET grado = ?, codigo = ? WHERE id_asignatura = ?");
                $stmtUpd->execute([$materia->grado, $materia->codigo, $asignatura['id_asignatura']]);
            } else {
                // Crear
                $stmtIns = $conn->prepare("INSERT INTO asignaturas (nombre, codigo, grado, estado) VALUES (?, ?, ?, ?)");
                $stmtIns->execute([$materia->nombre, $materia->codigo, $materia->grado, $materia->estado]);
            }
        } catch (\Exception $e) {
            // Log error but don't stop execution
            error_log("Error syncing asignatura: " . $e->getMessage());
        }
    }
}
