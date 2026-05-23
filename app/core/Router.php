<?php
/**
 * HTTP Router - Maps URLs to controllers
 * Supports GET, POST, middleware groups, and named routes
 */

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewareGroups = [];
    private string $prefix = '';
    private array $currentMiddleware = [];

    public function get(string $path, string $handler, string $name = ''): self
    {
        return $this->addRoute('GET', $path, $handler, $name);
    }

    public function post(string $path, string $handler, string $name = ''): self
    {
        return $this->addRoute('POST', $path, $handler, $name);
    }

    public function group(array $options, callable $callback): self
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->currentMiddleware;

        if (isset($options['prefix'])) {
            $this->prefix .= '/' . trim($options['prefix'], '/');
        }
        if (isset($options['middleware'])) {
            $mw = is_array($options['middleware']) ? $options['middleware'] : [$options['middleware']];
            $this->currentMiddleware = array_merge($this->currentMiddleware, $mw);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;

        return $this;
    }

    private function addRoute(string $method, string $path, string $handler, string $name): self
    {
        $fullPath = $this->prefix . '/' . trim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');
        if ($fullPath !== '/') {
            $fullPath = rtrim($fullPath, '/');
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $this->currentMiddleware,
            'name' => $name
        ];

        return $this;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            $params = $this->matchRoute($route['path'], $uri);
            if ($params !== false && $route['method'] === $method) {
                // Run middleware
                foreach ($route['middleware'] as $middleware) {
                    $middlewareClass = "App\\Middleware\\" . $middleware;
                    if (class_exists($middlewareClass)) {
                        $mw = new $middlewareClass();
                        $result = $mw->handle();
                        if ($result === false) {
                            return;
                        }
                    }
                }

                // Parse handler
                [$controllerName, $action] = explode('@', $route['handler']);
                $controllerClass = "App\\Controllers\\" . $controllerName;

                if (!class_exists($controllerClass)) {
                    http_response_code(500);
                    echo "Controller {$controllerName} not found.";
                    return;
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $action)) {
                    http_response_code(500);
                    echo "Action {$action} not found in {$controllerName}.";
                    return;
                }

                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Route not found']);
        } else {
            include BASE_PATH . '/templates/errors/404.php';
        }
    }

    private function matchRoute(string $routePath, string $uri): array|false
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $uriParts = explode('/', trim($uri, '/'));

        if (count($routeParts) !== count($uriParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[] = $uriParts[$i];
            } elseif ($part !== $uriParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function url(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                $path = $route['path'];
                foreach ($params as $value) {
                    $path = preg_replace('/\{[^}]+\}/', $value, $path, 1);
                }
                return $path;
            }
        }
        return '/';
    }
}
