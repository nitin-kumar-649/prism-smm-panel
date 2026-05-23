<?php

namespace App\Middleware;

class RateLimitMiddleware
{
    public function handle(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'rate_limit_' . md5($ip);
        $maxRequests = (int) env('RATE_LIMIT_MAX', 60);
        $window = (int) env('RATE_LIMIT_WINDOW', 60);

        $cacheFile = BASE_PATH . '/storage/cache/' . $key . '.json';

        $data = ['count' => 0, 'reset' => time() + $window];
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true) ?: $data;
        }

        if (time() > ($data['reset'] ?? 0)) {
            $data = ['count' => 0, 'reset' => time() + $window];
        }

        $data['count']++;

        if ($data['count'] > $maxRequests) {
            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: ' . ($data['reset'] - time()));
            echo json_encode(['error' => 'Rate limit exceeded. Try again later.']);
            exit;
        }

        file_put_contents($cacheFile, json_encode($data), LOCK_EX);
        return true;
    }
}
