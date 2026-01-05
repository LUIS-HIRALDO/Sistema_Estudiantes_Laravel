<?php

namespace App\Middleware;

use App\Models\Usuario;

class AuthMiddleware
{
    public static function check()
    {
        // Obtener header Authorization
        $authorization = null;
        
        // Intentar obtener desde $_SERVER (más compatible)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        } 
        // Fallback a getallheaders() si está disponible
        elseif (function_exists('getallheaders')) {
            $headers = getallheaders();
            $authorization = $headers['Authorization'] ?? null;
        }
        
        if (!$authorization) {
            return null;
        }

        $matches = [];
        if (!preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            return null;
        }

        $token = $matches[1];
        return self::verifyToken($token);
    }

    public static function verifyToken($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            [$header, $payload, $signature] = $parts;
            
            $secret = $_ENV['JWT_SECRET'] ?? 'secret';
            $valid_signature = hash_hmac('sha256', "{$header}.{$payload}", $secret, true);
            $valid_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($valid_signature));
            
            if ($signature !== $valid_signature) {
                return null;
            }

            $decoded = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
            
            if ($decoded['exp'] < time()) {
                return null;
            }

            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function guest()
    {
        return self::check() === null;
    }

    public static function user()
    {
        $data = self::check();
        if (!$data) {
            return null;
        }

        return Usuario::find($data['id']);
    }
}
