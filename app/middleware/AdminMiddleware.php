<?php

namespace App\Middleware;

class AdminMiddleware
{
    public function handle(): bool
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo 'Access denied.';
            exit;
        }

        return true;
    }
}
