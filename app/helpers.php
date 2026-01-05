<?php

/**
 * Funciones auxiliares globales
 */

function env($key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

function dd($var)
{
    var_dump($var);
    die();
}

function base64_url_encode($str)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
}

function base64_url_decode($str)
{
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $str));
}

function generateToken($usuario, $secret = null)
{
    $secret = $secret ?? env('JWT_SECRET', 'secret');
    
    $payload = [
        'id' => (string)$usuario->getId(),
        'email' => $usuario->email,
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60),
    ];

    $header = base64_url_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload_encoded = base64_url_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "{$header}.{$payload_encoded}", $secret, true);
    $signature = base64_url_encode($signature);

    return "{$header}.{$payload_encoded}.{$signature}";
}

function verifyToken($token, $secret = null)
{
    $secret = $secret ?? env('JWT_SECRET', 'secret');
    
    try {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        
        $valid_signature = hash_hmac('sha256', "{$header}.{$payload}", $secret, true);
        $valid_signature = base64_url_encode($valid_signature);
        
        if (!hash_equals($signature, $valid_signature)) {
            return null;
        }

        $decoded = json_decode(base64_url_decode($payload), true);
        
        if ($decoded['exp'] < time()) {
            return null;
        }

        return $decoded;
    } catch (\Exception $e) {
        return null;
    }
}

function getAuthUser()
{
    return \App\Middleware\AuthMiddleware::user();
}

function isAuthenticated()
{
    return \App\Middleware\AuthMiddleware::check() !== null;
}

function abort($status, $message = '')
{
    http_response_code($status);
    \App\Response::json(['error' => $message ?: "Error $status"], $status);
}
