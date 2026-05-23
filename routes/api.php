<?php
/**
 * API Routes
 * External API endpoint for third-party integrations
 */

/** @var \App\Core\Router $router */

$router->post('/api/v2', 'ExternalApiController@handle', 'api.v2');
$router->get('/api/v2', 'ExternalApiController@handle');
