<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Models\Rol;

class AuthController
{
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $usuario = Usuario::where('email', $data['email'] ?? null);
        if (!empty($usuario)) {
            return \response()->json(['error' => 'El email ya está registrado'], 400);
        }

        $usuario = new Usuario();
        $usuario->fill([
            'email' => $data['email'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'rol' => 'indefinido',
            'estado' => 'inactivo'
        ]);
        $usuario->setPassword($data['password']);
        $usuario->save();

        // Obtener email institucional
        $db = \App\Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT email FROM institucion LIMIT 1");
        $inst = $stmt->fetch(\PDO::FETCH_ASSOC);
        $fromEmail = !empty($inst['email']) ? $inst['email'] : 'no-reply@sistemaestudiantes.com';

        // Enviar correo de notificación
        $to = $data['email'];
        $subject = "Registro en Sistema de Estudiantes - Cuenta Pendiente de Aprobación";
        $message = "Hola " . $data['nombre'] . ",\n\nSu cuenta ha sido creada exitosamente pero se encuentra inhabilitada hasta que un representante le habilite el acceso.\n\nAtentamente,\nAdministración.";
        $headers = "From: " . $fromEmail . "\r\n" .
                   "Reply-To: " . $fromEmail . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        
        // Intentar enviar correo y registrar en log si falla o si estamos en local
        ob_start(); // Capturar cualquier salida de error de mail()
        $mailSent = @mail($to, $subject, $message, $headers);
        $mailOutput = ob_get_clean(); // Limpiar buffer

        // Log del correo para desarrollo local
        $logDir = dirname(__DIR__, 2) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Email a: $to | Subject: $subject | Status: " . ($mailSent ? 'Enviado' : 'Fallido') . "\n";
        if (!$mailSent) {
            $logMessage .= "Error Output: $mailOutput\n";
            $logMessage .= "Contenido:\n$message\n-------------------\n";
        }
        file_put_contents($logDir . '/email_log.txt', $logMessage, FILE_APPEND);

        return \response()->json(['message' => 'Usuario registrado exitosamente. Su cuenta está pendiente de aprobación.', 'usuario' => $usuario->toArray()], 201);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return \response()->json(['error' => 'Método no permitido'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $usuarios = Usuario::where('email', $data['email'] ?? null);

        if (empty($usuarios)) {
            return \response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        /** @var Usuario $usuario */
        $usuario = $usuarios[0];
        if (!$usuario->verifyPassword($data['password'])) {
            return \response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        // Verificar estado (case-insensitive y manejo de nulos)
        $estado = strtolower(trim($usuario->estado ?? ''));
        if ($estado === 'inactivo' || $estado === 'inhabilitado') {
            return \response()->json(['error' => 'Su cuenta se encuentra inhabilitada hasta que un representante le habilite el acceso.'], 403);
        }

        $token = $this->generateToken($usuario);

        return \response()->json([
            'message' => 'Login exitoso',
            'token' => $token,
            'require_password_change' => (bool)($usuario->must_change_password ?? false),
            'usuario' => [
                'id' => (string)$usuario->getId(),
                'email' => $usuario->email,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'rol' => $usuario->rol,
                'profesor_id' => $usuario->profesor_id,
            ]
        ], 200);
    }

    public function changePassword()
    {
        $token = $this->getBearerToken();
        if (!$token) {
            return \response()->json(['error' => 'No autorizado'], 401);
        }

        $userId = $this->verifyToken($token);
        if (!$userId) {
            return \response()->json(['error' => 'Token inválido'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['password'])) {
            return \response()->json(['error' => 'La nueva contraseña es requerida'], 400);
        }

        $usuario = Usuario::find($userId);
        if (!$usuario) {
            return \response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $usuario->setPassword($data['password']);
        $usuario->must_change_password = 0;
        $usuario->save();

        return \response()->json(['message' => 'Contraseña actualizada correctamente']);
    }

    public function profile()
    {
        $token = $this->getBearerToken();
        if (!$token) {
            return \response()->json(['error' => 'No autorizado'], 401);
        }

        $userId = $this->verifyToken($token);
        if (!$userId) {
            return \response()->json(['error' => 'Token inválido'], 401);
        }

        $usuario = Usuario::find($userId);
        if (!$usuario) {
            return \response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return \response()->json($usuario->toArray(), 200);
    }

    private function generateToken($usuario)
    {
        $payload = [
            'id' => (string)$usuario->getId(),
            'email' => $usuario->email,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60),
        ];

        $secret = $_ENV['JWT_SECRET'] ?? 'secret';
        $header = base64_url_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload_encoded = base64_url_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "{$header}.{$payload_encoded}", $secret, true);
        $signature_encoded = base64_url_encode($signature);

        return "{$header}.{$payload_encoded}.{$signature_encoded}";
    }

    private function verifyToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $secret = $_ENV['JWT_SECRET'] ?? 'secret';
        $payload_decoded = json_decode(base64_url_decode($parts[1]), true);

        if ($payload_decoded['exp'] < time()) {
            return null;
        }

        return $payload_decoded['id'] ?? null;
    }

    private function getBearerToken()
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
}

function base64_url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64_url_decode($data)
{
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
}
