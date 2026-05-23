<?php
/**
 * Payment Gateway Configuration
 */

return [
    'gateways' => [
        'paypal' => [
            'enabled' => !empty(env('PAYPAL_CLIENT_ID')),
            'client_id' => env('PAYPAL_CLIENT_ID', ''),
            'secret' => env('PAYPAL_SECRET', ''),
            'mode' => env('PAYPAL_MODE', 'sandbox'),
            'label' => 'PayPal',
            'icon' => 'fab fa-paypal',
            'min_amount' => 1.00,
        ],
        'stripe' => [
            'enabled' => !empty(env('STRIPE_SECRET_KEY')),
            'public_key' => env('STRIPE_PUBLIC_KEY', ''),
            'secret_key' => env('STRIPE_SECRET_KEY', ''),
            'label' => 'Stripe (Card)',
            'icon' => 'fab fa-stripe',
            'min_amount' => 0.50,
        ],
        'razorpay' => [
            'enabled' => !empty(env('RAZORPAY_KEY_ID')),
            'key_id' => env('RAZORPAY_KEY_ID', ''),
            'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
            'label' => 'Razorpay',
            'icon' => 'fas fa-credit-card',
            'min_amount' => 1.00,
        ],
        'coinbase' => [
            'enabled' => !empty(env('COINBASE_API_KEY')),
            'api_key' => env('COINBASE_API_KEY', ''),
            'webhook_secret' => env('COINBASE_WEBHOOK_SECRET', ''),
            'label' => 'Crypto (Coinbase)',
            'icon' => 'fab fa-bitcoin',
            'min_amount' => 5.00,
        ],
        'manual' => [
            'enabled' => true,
            'label' => 'Manual Payment',
            'icon' => 'fas fa-university',
            'min_amount' => 1.00,
            'instructions' => 'Send payment to the provided details and upload proof.',
        ],
    ],
];
