<?php

namespace App\Controllers;

class Controller
{
    protected $model;

    public function index()
    {
        $items = $this->model::all();
        $data = array_map(function($item) {
            return $item->toArray();
        }, $items);
        // Envolver en { data: [...] } para consistencia con el frontend
        return \response()->json(['data' => $data], 200);
    }

    public function show($id)
    {
        $item = $this->model::find($id);
        if (!$item) {
            return \response()->json(['error' => 'Recurso no encontrado'], 404);
        }
        return \response()->json($item->toArray(), 200);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $item = $this->model::create($data);

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

        return \response()->json(['message' => 'Recurso actualizado exitosamente', 'data' => $item->toArray()], 200);
    }

    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        $item = $this->model::find($id);
        if (!$item) {
            return \response()->json(['error' => 'Recurso no encontrado'], 404);
        }

        $item->delete();
        return \response()->json(['message' => 'Recurso eliminado exitosamente'], 200);
    }

    protected function getAuthUser()
    {
        $token = $this->getBearerToken();
        if (!$token) return null;
        
        $payload = \verifyToken($token);
        if (!$payload || !isset($payload['id'])) return null;
        
        return \App\Models\Usuario::find($payload['id']);
    }

    protected function getBearerToken()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    protected function response($data, $status = 200)
    {
        \App\Response::json($data, $status);
    }

    protected function requireAuth()
    {
        if (!\isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
    }

    protected function requireAdmin()
    {
        if (!isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        // Check if user is admin - implement based on your logic
    }

    protected function getJson()
    {
        return json_decode(file_get_contents('php://input'), true);
    }
}
