<?php
/**
 * Refill Checker Cron Job
 * Processes refill requests via provider APIs
 * Run every 5 minutes: */5 * * * * php /path/to/cron/refill_checker.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/helpers/functions.php';

$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        putenv(trim($key) . '=' . trim($value, " \t\n\r\0\x0B\"'"));
    }
}

spl_autoload_register(function (string $class) {
    $map = ['App\\Core\\' => 'app/core/', 'App\\Models\\' => 'app/models/', 'App\\Services\\' => 'app/services/'];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = BASE_PATH . '/' . $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) { require_once $file; return; }
        }
    }
});

use App\Core\Database;
use App\Core\Logger;
use App\Services\ProviderApiService;

echo "[" . date('Y-m-d H:i:s') . "] Refill checker started\n";

$db = Database::getInstance();

$refillOrders = $db->fetchAll(
    "SELECT o.*, s.provider_service_id, p.api_url, p.api_key
     FROM orders o
     JOIN services s ON o.service_id = s.id
     JOIN providers p ON s.provider_id = p.id
     WHERE o.refill_requested = 1
     AND o.status = 'completed'
     AND s.refill = 1
     AND o.provider_order_id IS NOT NULL
     LIMIT 50"
);

echo "Found " . count($refillOrders) . " refill requests\n";

foreach ($refillOrders as $order) {
    $api = new ProviderApiService($order['api_url'], $order['api_key']);
    $response = $api->requestRefill($order['provider_order_id']);

    if ($response && isset($response['refill'])) {
        $db->update('orders', [
            'refill_id' => $response['refill'],
            'refill_requested' => 0,
        ], 'id = ?', [$order['id']]);
        echo "  Refill submitted for order #{$order['order_id']}: refill_id={$response['refill']}\n";
    } else {
        echo "  Refill failed for order #{$order['order_id']}: " . ($response['error'] ?? 'Unknown error') . "\n";
        Logger::warning("Refill failed", ['order_id' => $order['order_id'], 'error' => $response['error'] ?? '']);
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Refill checker completed\n";
