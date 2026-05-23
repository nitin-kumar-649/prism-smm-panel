<?php
/**
 * Core Application Bootstrap
 * Initializes environment, session, routing, and error handling
 */

namespace App\Core;

class App
{
    private static ?App $instance = null;
    private Router $router;
    private array $config = [];

    private function __construct()
    {
        $this->loadEnvironment();
        $this->setTimezone();
        $this->initErrorHandling();
        $this->initSession();
        $this->router = new Router();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnvironment(): void
    {
        $envFile = BASE_PATH . '/.env';
        if (!file_exists($envFile)) {
            die('Environment file not found. Copy .env.example to .env');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    private function setTimezone(): void
    {
        date_default_timezone_set(env('TIMEZONE', 'UTC'));
    }

    private function initErrorHandling(): void
    {
        if (env('APP_DEBUG', false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        set_exception_handler(function (\Throwable $e) {
            Logger::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            if (env('APP_DEBUG', false)) {
                echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            } else {
                http_response_code(500);
                echo 'An internal error occurred.';
            }
        });
    }

    private function initSession(): void
    {
        $sessionPath = BASE_PATH . '/storage/sessions';
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0755, true);
        }

        ini_set('session.save_path', $sessionPath);
        ini_set('session.gc_maxlifetime', env('SESSION_LIFETIME', 7200));
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', '1');

        if (env('APP_ENV') === 'production') {
            ini_set('session.cookie_secure', '1');
        }

        session_name(env('SESSION_NAME', 'prism_session'));
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerate session ID periodically
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - $_SESSION['_created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $this->router->dispatch();
    }
}
