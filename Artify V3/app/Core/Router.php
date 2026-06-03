<?php
namespace App\Core;

/**
 * Routeur HTTP minimaliste.
 * Mappe une URL vers une méthode de Controller.
 */
final class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $action): void { $this->routes['GET'][$path] = $action; }
    public function post(string $path, array $action): void { $this->routes['POST'][$path] = $action; }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim(str_replace('/artify', '', $path), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        $routes = $this->routes[$method] ?? [];
        if (!isset($routes[$path])) {
            // Match dynamique : /forum/sujet/{id}
            foreach ($routes as $r => $action) {
                $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $r) . '$#';
                if (preg_match($regex, $path, $m)) {
                    $params = array_filter($m, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
                    [$class, $method] = $action;
                    (new $class)->$method($params);
                    return;
                }
            }
            http_response_code(404);
            echo "404 — Route introuvable : $path";
            return;
        }
        [$class, $method] = $routes[$path];
        (new $class)->$method();
    }
}
