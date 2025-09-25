<?php

namespace App\Core;


class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];


    public function get(string $path, $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }


    public function post(string $path, $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }


    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = $this->normalize($uri);


        $handler = $this->routes[$method][$path] ?? null;


        if (!$handler) {
            http_response_code(404);
            echo '404 - Página no encontrada';
            return;
        }


        if (is_array($handler)) {
            // [ControllerClass, 'method']
            [$class, $methodName] = $handler;
            $controller = new $class();
            echo $controller->$methodName();
            return;
        }


        if (is_callable($handler)) {
            echo $handler();
            return;
        }


        echo 'Handler inválido';
    }


    private function normalize(string $path): string
    {
        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    }
}
