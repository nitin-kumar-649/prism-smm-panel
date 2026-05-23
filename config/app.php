<?php
/**
 * Application Configuration
 */

return [
    'name' => env('APP_NAME', 'Prism SMM Panel'),
    'url' => env('APP_URL', 'http://localhost'),
    'env' => env('APP_ENV', 'development'),
    'debug' => env('APP_DEBUG', false),
    'timezone' => env('TIMEZONE', 'UTC'),
    'currency' => env('CURRENCY', 'USD'),
    'currency_symbol' => env('CURRENCY_SYMBOL', '$'),

    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 7200),
        'name' => env('SESSION_NAME', 'prism_session'),
    ],

    'otp' => [
        'expiry' => env('OTP_EXPIRY', 300),
        'length' => env('OTP_LENGTH', 6),
    ],

    'upload' => [
        'max_size' => env('MAX_UPLOAD_SIZE', 5242880),
        'allowed_extensions' => explode(',', env('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf')),
    ],

    'rate_limit' => [
        'max_requests' => env('RATE_LIMIT_MAX', 60),
        'window' => env('RATE_LIMIT_WINDOW', 60),
    ],

    'order_statuses' => [
        'pending', 'processing', 'in_progress', 'completed', 'partial', 'cancelled', 'refunded', 'failed'
    ],

    'user_roles' => ['user', 'admin'],
];
