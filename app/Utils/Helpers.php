<?php

namespace App\Utils;

class DateHelper
{
    public static function now()
    {
        return date('Y-m-d H:i:s');
    }

    public static function parse($date)
    {
        if (is_string($date)) {
            return date('Y-m-d H:i:s', strtotime($date));
        }

        if (is_int($date)) {
            return date('Y-m-d H:i:s', $date);
        }

        return $date;
    }

    public static function format($date, $format = 'Y-m-d H:i:s')
    {
        if (is_string($date)) {
            return date($format, strtotime($date));
        }

        return date($format, $date);
    }
}

class StringHelper
{
    public static function slug($string)
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }

    public static function camelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    public static function snakeCase($string)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=[A-Z][a-z]|\b)|[A-Za-z][a-z0-9]*)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    public static function truncate($string, $length = 100, $suffix = '...')
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        return substr($string, 0, $length) . $suffix;
    }
}

class ArrayHelper
{
    public static function only(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    public static function except(array $array, array $keys)
    {
        return array_diff_key($array, array_flip($keys));
    }

    public static function get(array $array, $key, $default = null)
    {
        if (strpos($key, '.') === false) {
            return $array[$key] ?? $default;
        }

        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    public static function merge(array $array1, array $array2)
    {
        return array_merge($array1, $array2);
    }

    public static function pluck(array $array, $key)
    {
        return array_map(function($item) use ($key) {
            return $item[$key] ?? null;
        }, $array);
    }
}
