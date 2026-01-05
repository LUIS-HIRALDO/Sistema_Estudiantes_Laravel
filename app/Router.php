<?php

namespace App;

class Router
{
    private $routes = [];
    private $params = [];

    public function register($method, $path, $action)
    {
        $this->routes["{$method}:{$path}"] = $action;
    }

    public function get($path, $action)
    {
        $this->register('GET', $path, $action);
    }

    public function post($path, $action)
    {
        $this->register('POST', $path, $action);
    }

    public function put($path, $action)
    {
        $this->register('PUT', $path, $action);
    }

    public function patch($path, $action)
    {
        $this->register('PATCH', $path, $action);
    }

    public function delete($path, $action)
    {
        $this->register('DELETE', $path, $action);
    }

    public function match($method, $uri)
    {
        $route_key = "{$method}:{$uri}";
        
        foreach ($this->routes as $pattern => $action) {
            $regex = preg_replace_callback('/{(\w+)}/', function($matches) {
                return '(?<' . $matches[1] . '>[^/]+)';
            }, $pattern);
            $regex = str_replace(':', '\:', $regex);
            
            if (preg_match("~^{$regex}$~", $route_key, $matches)) {
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $this->params[$key] = $value;
                    }
                }
                return $action;
            }
        }
        
        return null;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
