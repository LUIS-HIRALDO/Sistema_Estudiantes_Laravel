<?php

namespace App\Config;

class Config
{
    protected static $config = [];

    public static function set($key, $value)
    {
        self::$config[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }

    public static function load()
    {
        self::$config = [
            'app_name' => env('APP_NAME', 'Sistema Estudiantes'),
            'app_env' => env('APP_ENV', 'production'),
            'app_debug' => env('APP_DEBUG', false),
            'app_url' => env('APP_URL', 'http://localhost:8000'),
            'timezone' => 'America/Santo_Domingo',
            'locale' => 'es',
            'database' => [
                'default' => 'mysql',
                'connections' => [
                    'mysql' => [
                        'driver' => 'mysql',
                        'host' => env('DB_HOST', 'localhost'),
                        'port' => env('DB_PORT', 3306),
                        'database' => env('DB_DATABASE', 'sistema_estudiantes'),
                        'username' => env('DB_USERNAME', 'root'),
                        'password' => env('DB_PASSWORD', ''),
                    ]
                ]
            ],
            'jwt' => [
                'secret' => env('JWT_SECRET', 'secret'),
                'algo' => 'HS256',
                'expiration' => 7 * 24 * 60 * 60, // 7 días
            ]
        ];
    }
}

Config::load();
