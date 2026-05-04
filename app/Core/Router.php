<?php

namespace App\Core;

final class Router
{
    private array $routes = [];
    private static array $named = [];

    public function get(string $path, array|callable|string $handler, ?string $name = null): void
    {
        $this->add('GET', $path, $handler, $name);
    }

    public function post(string $path, array|callable|string $handler, ?string $name = null): void
    {
        $this->add('POST', $path, $handler, $name);
    }

    public function put(string $path, array|callable|string $handler, ?string $name = null): void
    {
        $this->add('PUT', $path, $handler, $name);
    }

    private function add(string $method, string $path, array|callable|string $handler, ?string $name): void
    {
        $path = '/' . trim($path, '/');
        $route = compact('method', 'path', 'handler', 'name');
        $this->routes[] = $route;

        if ($name) {
            self::$named[$name] = $path;
        }
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $params = $this->match($route['path'], $request->path());
            if ($params === null) {
                continue;
            }

            if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
                Csrf::validate((string) $request->input('_token'));
            }

            return $this->call($route['handler'], $params, $request);
        }

        throw new HttpException(404, 'The requested page was not found.');
    }

    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
        if (! preg_match('#^' . $pattern . '$#', $requestPath, $matches)) {
            return null;
        }

        return array_filter($matches, is_string(...), ARRAY_FILTER_USE_KEY);
    }

    private function call(array|callable|string $handler, array $params, Request $request): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return $controller->{$method}($request, ...array_values($params));
        }

        if (is_string($handler) && class_exists($handler)) {
            return (new $handler())($request, ...array_values($params));
        }

        return $handler($request, ...array_values($params));
    }

    public static function path(string $name, array $params = []): string
    {
        $path = self::$named[$name] ?? $name;
        foreach ($params as $value) {
            $path = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', (string) $value, $path, 1);
        }

        return $path;
    }
}
