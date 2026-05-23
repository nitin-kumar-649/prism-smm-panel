<?php
/**
 * Web Routes
 */

/** @var \App\Core\Router $router */

// Public routes
$router->get('/', 'HomeController@index', 'home');
$router->get('/login', 'AuthController@showLogin', 'login');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister', 'register');
$router->post('/register', 'AuthController@register');
$router->get('/forgot-password', 'AuthController@showForgotPassword', 'forgot-password');
$router->post('/forgot-password/send-otp', 'AuthController@sendOtp');
$router->post('/forgot-password/verify-otp', 'AuthController@verifyOtp');
$router->post('/forgot-password/reset', 'AuthController@resetPassword');
$router->get('/logout', 'AuthController@logout', 'logout');
$router->get('/page/{slug}', 'HomeController@page', 'page');

// User routes (authenticated)
$router->group(['prefix' => 'user', 'middleware' => ['AuthMiddleware', 'CsrfMiddleware']], function ($router) {
    $router->get('/', 'UserDashboardController@index', 'user.dashboard');

    // Orders
    $router->get('/new-order', 'UserOrderController@newOrder', 'user.new-order');
    $router->post('/new-order', 'UserOrderController@create');
    $router->get('/bulk-order', 'UserOrderController@bulkOrder', 'user.bulk-order');
    $router->post('/bulk-order', 'UserOrderController@bulkCreate');
    $router->get('/orders', 'UserOrderController@list', 'user.orders');
    $router->get('/orders/{id}', 'UserOrderController@detail', 'user.order-detail');
    $router->post('/orders/{id}/refill', 'UserOrderController@refill');
    $router->post('/orders/{id}/cancel', 'UserOrderController@cancel');

    // Wallet
    $router->get('/wallet', 'UserWalletController@index', 'user.wallet');
    $router->post('/wallet/deposit', 'UserWalletController@deposit');

    // Tickets
    $router->get('/tickets', 'UserTicketController@index', 'user.tickets');
    $router->post('/tickets', 'UserTicketController@create');
    $router->get('/tickets/{id}', 'UserTicketController@show', 'user.ticket-detail');
    $router->post('/tickets/{id}/reply', 'UserTicketController@reply');
    $router->post('/tickets/{id}/close', 'UserTicketController@close');

    // API
    $router->get('/api', 'UserApiController@index', 'user.api');
    $router->post('/api/regenerate', 'UserApiController@regenerateKey');

    // Profile
    $router->get('/profile', 'UserProfileController@index', 'user.profile');
    $router->post('/profile', 'UserProfileController@update');
    $router->post('/profile/password', 'UserProfileController@changePassword');

    // Notifications
    $router->get('/notifications', 'NotificationController@getUnread');
    $router->post('/notifications/{id}/read', 'NotificationController@markRead');
    $router->post('/notifications/read-all', 'NotificationController@markAllRead');

    // Service info AJAX
    $router->get('/service-info', 'UserOrderController@getServiceInfo');
});

// Admin routes
$router->group(['prefix' => 'admin', 'middleware' => ['AdminMiddleware', 'CsrfMiddleware']], function ($router) {
    $router->get('/', 'AdminDashboardController@index', 'admin.dashboard');

    // Orders
    $router->get('/orders', 'AdminOrderController@index', 'admin.orders');
    $router->post('/orders/{id}/status', 'AdminOrderController@updateStatus');

    // Services
    $router->get('/services', 'AdminServiceController@index', 'admin.services');
    $router->post('/services', 'AdminServiceController@createService');
    $router->post('/services/{id}', 'AdminServiceController@updateService');
    $router->post('/services/{id}/delete', 'AdminServiceController@deleteService');
    $router->post('/categories', 'AdminServiceController@createCategory');
    $router->post('/categories/{id}/delete', 'AdminServiceController@deleteCategory');

    // Users
    $router->get('/users', 'AdminUserController@index', 'admin.users');
    $router->get('/users/{id}', 'AdminUserController@show', 'admin.user-detail');
    $router->post('/users/{id}/status', 'AdminUserController@updateStatus');
    $router->post('/users/{id}/funds', 'AdminUserController@addFunds');

    // Providers
    $router->get('/providers', 'AdminProviderController@index', 'admin.providers');
    $router->post('/providers', 'AdminProviderController@create');
    $router->post('/providers/{id}', 'AdminProviderController@update');
    $router->post('/providers/{id}/delete', 'AdminProviderController@delete');
    $router->get('/providers/{id}/balance', 'AdminProviderController@checkBalance');
    $router->get('/providers/{id}/services', 'AdminProviderController@getServices');

    // Payments
    $router->get('/payments', 'AdminPaymentController@index', 'admin.payments');
    $router->post('/payments/{id}/approve', 'AdminPaymentController@approve');
    $router->post('/payments/{id}/reject', 'AdminPaymentController@reject');

    // Tickets
    $router->get('/tickets', 'AdminTicketController@index', 'admin.tickets');
    $router->get('/tickets/{id}', 'AdminTicketController@show', 'admin.ticket-detail');
    $router->post('/tickets/{id}/reply', 'AdminTicketController@reply');
    $router->post('/tickets/{id}/close', 'AdminTicketController@close');

    // Settings
    $router->get('/settings', 'AdminSettingsController@index', 'admin.settings');
    $router->post('/settings', 'AdminSettingsController@update');
});
