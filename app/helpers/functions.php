<?php
/**
 * Global Helper Functions
 */

function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    // Cast boolean-like strings
    $lower = strtolower($value);
    if ($lower === 'true') return true;
    if ($lower === 'false') return false;
    if ($lower === 'null') return null;
    return $value;
}

function asset(string $path): string
{
    return '/public/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return env('APP_URL', '') . '/' . ltrim($path, '/');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string
{
    $token = $_SESSION['csrf_token'] ?? '';
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

function csrf_token(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

function flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function current_user_role(): string
{
    return $_SESSION['user_role'] ?? 'user';
}

function format_money(float $amount): string
{
    return env('CURRENCY_SYMBOL', '$') . number_format($amount, 2);
}

function format_number(int $number): string
{
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    }
    if ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return (string) $number;
}

function time_ago(string $datetime): string
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'just now';
}

function generate_api_key(): string
{
    return bin2hex(random_bytes(32));
}

function generate_order_id(): string
{
    return 'ORD-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
}

function generate_transaction_id(): string
{
    return 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
}

function sanitize_input(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function is_valid_url(string $url): bool
{
    return (bool) filter_var($url, FILTER_VALIDATE_URL);
}

function is_valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function get_status_badge(string $status): string
{
    $colors = [
        'pending' => 'bg-yellow-500/20 text-yellow-400',
        'processing' => 'bg-blue-500/20 text-blue-400',
        'in_progress' => 'bg-blue-500/20 text-blue-400',
        'completed' => 'bg-green-500/20 text-green-400',
        'partial' => 'bg-orange-500/20 text-orange-400',
        'cancelled' => 'bg-red-500/20 text-red-400',
        'refunded' => 'bg-purple-500/20 text-purple-400',
        'failed' => 'bg-red-500/20 text-red-400',
        'active' => 'bg-green-500/20 text-green-400',
        'inactive' => 'bg-gray-500/20 text-gray-400',
        'banned' => 'bg-red-500/20 text-red-400',
        'open' => 'bg-blue-500/20 text-blue-400',
        'closed' => 'bg-gray-500/20 text-gray-400',
        'answered' => 'bg-green-500/20 text-green-400',
    ];

    $colorClass = $colors[$status] ?? 'bg-gray-500/20 text-gray-400';
    $label = ucfirst(str_replace('_', ' ', $status));
    return "<span class=\"px-2.5 py-1 rounded-full text-xs font-medium {$colorClass}\">{$label}</span>";
}

function get_pagination_html(int $currentPage, int $totalPages, string $baseUrl): string
{
    if ($totalPages <= 1) return '';

    $html = '<nav class="flex items-center gap-1">';

    // Previous
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-sm transition-colors">&laquo;</a>';
    }

    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $currentPage ? 'bg-indigo-600 text-white' : 'bg-white/5 hover:bg-white/10';
        $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="px-3 py-2 rounded-lg ' . $active . ' text-sm transition-colors">' . $i . '</a>';
    }

    // Next
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-sm transition-colors">&raquo;</a>';
    }

    $html .= '</nav>';
    return $html;
}
