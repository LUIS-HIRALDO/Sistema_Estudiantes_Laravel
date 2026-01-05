<?php

namespace App;

class Logger
{
    private static $log_file;

    public static function init($path = null)
    {
        self::$log_file = $path ?? dirname(__DIR__) . '/logs/app.log';
        
        $log_dir = dirname(self::$log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
    }

    public static function log($message, $level = 'INFO')
    {
        if (!self::$log_file) {
            self::init();
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$level] $message\n";

        file_put_contents(self::$log_file, $log_message, FILE_APPEND);
    }

    public static function info($message)
    {
        self::log($message, 'INFO');
    }

    public static function warning($message)
    {
        self::log($message, 'WARNING');
    }

    public static function error($message)
    {
        self::log($message, 'ERROR');
    }

    public static function debug($message, $data = null)
    {
        if (env('APP_DEBUG', false)) {
            if ($data) {
                $message .= ': ' . json_encode($data);
            }
            self::log($message, 'DEBUG');
        }
    }

    public static function exception(\Exception $e)
    {
        self::error("Exception: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
        self::debug("Stack trace:", $e->getTrace());
    }

    public static function getLogFile()
    {
        return self::$log_file;
    }

    public static function clearLogs()
    {
        if (self::$log_file && file_exists(self::$log_file)) {
            unlink(self::$log_file);
        }
    }
}

Logger::init();
