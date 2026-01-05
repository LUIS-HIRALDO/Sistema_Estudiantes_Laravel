<?php

namespace App\Controllers;

use App\Database;
use App\Response;

class InstitucionController extends Controller
{
    public function index()
    {
        $db = Database::getInstance();
        $stmt = $db->getConnection()->query("SELECT * FROM institucion LIMIT 1");
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$data) {
            // Retornar valores por defecto si no hay datos
            return $this->response([
                'nombre' => '',
                'codigo' => '',
                'tanda' => '',
                'telefono' => '',
                'email' => '',
                'distrito' => '',
                'regional' => '',
                'provincia' => '',
                'municipio' => ''
            ]);
        }
        
        return $this->response($data);
    }

    public function update($id = null)
    {
        try {
            // Determinar si es JSON o FormData
            $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
            $data = [];

            if (strpos($contentType, 'application/json') !== false) {
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
            } else {
                $data = $_POST;
            }
            
            if (!$data) {
                return $this->response(['error' => 'Datos inválidos o vacíos'], 400);
            }

            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Verificar si existe registro
            $stmt = $conn->query("SELECT id FROM institucion LIMIT 1");
            $exists = $stmt->fetch();
            
            // Validar campos requeridos (al menos nombre)
            if (empty($data['nombre'])) {
                return $this->response(['error' => 'El nombre del centro es obligatorio'], 400);
            }

            // Manejo de subida de archivos (Logo, Firma, Sello, Logo Minerd)
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filesToUpload = [
                'logo' => 'logo_url', 
                'firma' => 'firma_url', 
                'sello' => 'sello_url',
                'logo_minerd' => 'logo_minerd_url'
            ];
            $uploadedPaths = [];

            foreach ($filesToUpload as $inputName => $dbColumn) {
                if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
                    $filename = $inputName . '_institucion_' . time() . '.' . $ext;
                    $targetPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetPath)) {
                        $uploadedPaths[$dbColumn] = 'uploads/logos/' . $filename;
                    }
                }
            }

            // Asegurar que todos los campos existan en $data
            $campos = ['nombre', 'codigo', 'tanda', 'telefono', 'email', 'distrito', 'regional', 'provincia', 'municipio'];
            foreach ($campos as $campo) {
                if (!isset($data[$campo])) $data[$campo] = '';
            }
            
            if ($exists) {
                $sql = "UPDATE institucion SET 
                    nombre = ?, codigo = ?, tanda = ?, telefono = ?, email = ?,
                    distrito = ?, regional = ?, provincia = ?, municipio = ?";
                
                $params = [
                    $data['nombre'], $data['codigo'], $data['tanda'], $data['telefono'], $data['email'],
                    $data['distrito'], $data['regional'], $data['provincia'], $data['municipio']
                ];

                foreach ($uploadedPaths as $col => $path) {
                    $sql .= ", $col = ?";
                    $params[] = $path;
                }

                $sql .= " WHERE id = ?";
                $params[] = $exists['id'];

            } else {
                $cols = "nombre, codigo, tanda, telefono, email, distrito, regional, provincia, municipio";
                $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?";
                $params = [
                    $data['nombre'], $data['codigo'], $data['tanda'], $data['telefono'], $data['email'],
                    $data['distrito'], $data['regional'], $data['provincia'], $data['municipio']
                ];

                foreach ($uploadedPaths as $col => $path) {
                    $cols .= ", $col";
                    $vals .= ", ?";
                    $params[] = $path;
                }

                $sql = "INSERT INTO institucion ($cols) VALUES ($vals)";
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            return $this->response(['message' => 'Datos actualizados correctamente', 'uploads' => $uploadedPaths]);

        } catch (\Exception $e) {
            return $this->response(['error' => 'Error en el servidor: ' . $e->getMessage()], 500);
        }
    }

    public function deleteImage($tipo)
    {
        try {
            $allowedTypes = ['logo', 'firma', 'sello', 'logo_minerd'];
            if (!in_array($tipo, $allowedTypes)) {
                return $this->response(['error' => 'Tipo de imagen no válido'], 400);
            }

            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Obtener ruta actual
            $columna = $tipo . '_url';
            $stmt = $conn->query("SELECT id, $columna FROM institucion LIMIT 1");
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$data) {
                return $this->response(['error' => 'No hay datos de institución'], 404);
            }

            // Eliminar archivo físico si existe (y no es el default externo)
            if (!empty($data[$columna]) && !str_starts_with($data[$columna], 'http')) {
                $filePath = dirname(__DIR__, 2) . '/public/' . $data[$columna];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Actualizar BD
            $stmt = $conn->prepare("UPDATE institucion SET $columna = NULL WHERE id = ?");
            $stmt->execute([$data['id']]);

            return $this->response(['message' => 'Imagen eliminada correctamente']);

        } catch (\Exception $e) {
            return $this->response(['error' => 'Error al eliminar imagen: ' . $e->getMessage()], 500);
        }
    }
}
