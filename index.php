<?php
/**
 * Prism SMM Panel - Entry Point
 * All requests are routed through this file
 */

define('BASE_PATH', __DIR__);

// Autoloader
spl_autoload_register(function (string $class) {
    $map = [
        'App\\Core\\' => 'app/core/',
        'App\\Controllers\\' => 'app/controllers/',
        'App\\Models\\' => 'app/models/',
        'App\\Middleware\\' => 'app/middleware/',
        'App\\Services\\' => 'app/services/',
        'App\\Helpers\\' => 'app/helpers/',
        'App\\Validators\\' => 'app/validators/',
    ];

    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relativeClass = substr($class, strlen($prefix));
            $file = BASE_PATH . '/' . $dir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Load helpers
require_once BASE_PATH . '/app/helpers/functions.php';

// Boot application
$app = \App\Core\App::getInstance();
$router = $app->getRouter();

// Load routes
require_once BASE_PATH . '/routes/web.php';
require_once BASE_PATH . '/routes/api.php';

// Dispatch
$app->run();
