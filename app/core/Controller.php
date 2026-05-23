<?php
/**
 * Base Controller
 * Provides view rendering, JSON responses, redirects, and CSRF token handling
 */

namespace App\Core;

class Controller
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function view(string $template, array $data = []): void
    {
        extract($data);
        $csrfToken = $this->getCsrfToken();
        $currentUser = $this->getCurrentUser();
        $appName = env('APP_NAME', 'Prism SMM Panel');
        $currency = env('CURRENCY_SYMBOL', '$');

        $templatePath = BASE_PATH . '/templates/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($templatePath)) {
            http_response_code(500);
            echo "Template not found: {$template}";
            return;
        }

        include $templatePath;
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $this->json(['error' => 'Invalid security token. Please refresh and try again.'], 403);
            return false;
        }
        return true;
    }

    protected function getCurrentUser(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return $this->db->fetch(
            "SELECT id, username, email, role, balance, status, avatar, created_at FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }

    protected function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'admin';
    }

    protected function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function input(string $key, $default = null)
    {
        return htmlspecialchars(trim($_POST[$key] ?? $_GET[$key] ?? $default ?? ''), ENT_QUOTES, 'UTF-8');
    }

    protected function paginate(string $table, int $perPage = 20, string $where = '1=1', array $params = []): array
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $total = $this->db->count($table, $where, $params);
        $totalPages = (int) ceil($total / $perPage);
        $rows = $this->db->fetchAll(
            "SELECT * FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data' => $rows,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
            'per_page' => $perPage
        ];
    }
}
