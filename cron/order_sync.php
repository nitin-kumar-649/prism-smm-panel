<?php
/**
 * Order Sync Cron Job
 * Syncs order statuses with provider APIs
 * Run every 1-5 minutes: * * * * * php /path/to/cron/order_sync.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/helpers/functions.php';

// Load environment
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

// Autoload
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
use App\Models\Order;
use App\Services\ProviderApiService;

echo "[" . date('Y-m-d H:i:s') . "] Order sync started\n";

$db = Database::getInstance();
$orderModel = new Order();

$orders = $db->fetchAll(
    "SELECT o.*, s.provider_id, s.provider_service_id, p.api_url, p.api_key
     FROM orders o
     JOIN services s ON o.service_id = s.id
     JOIN providers p ON s.provider_id = p.id
     WHERE o.status IN ('processing', 'in_progress')
     AND o.provider_order_id IS NOT NULL
     ORDER BY o.id ASC LIMIT 100"
);

echo "Found " . count($orders) . " orders to sync\n";

$providerGroups = [];
foreach ($orders as $order) {
    $key = $order['provider_id'];
    $providerGroups[$key][] = $order;
}

foreach ($providerGroups as $providerId => $groupOrders) {
    $first = $groupOrders[0];
    $api = new ProviderApiService($first['api_url'], $first['api_key']);

    $orderIds = array_map(fn($o) => $o['provider_order_id'], $groupOrders);
    $statuses = $api->getMultiOrderStatus($orderIds);

    if (!$statuses) {
        echo "  Failed to fetch statuses for provider #{$providerId}\n";
        continue;
    }

    foreach ($groupOrders as $order) {
        $providerOrderId = $order['provider_order_id'];
        if (!isset($statuses[$providerOrderId])) continue;

        $remote = $statuses[$providerOrderId];
        $remoteStatus = strtolower($remote['status'] ?? '');
        $remains = (int) ($remote['remains'] ?? 0);
        $startCount = (int) ($remote['start_count'] ?? 0);

        $statusMap = [
            'pending' => 'pending',
            'processing' => 'processing',
            'in progress' => 'in_progress',
            'completed' => 'completed',
            'partial' => 'partial',
            'canceled' => 'cancelled',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
        ];

        $newStatus = $statusMap[$remoteStatus] ?? null;
        if (!$newStatus || $newStatus === $order['status']) continue;

        $updateData = [
            'status' => $newStatus,
            'remains' => $remains,
            'start_count' => $startCount,
        ];

        // Handle refund for partial/cancelled orders
        if (in_array($newStatus, ['partial', 'cancelled', 'refunded']) && in_array($order['status'], ['processing', 'in_progress'])) {
            $refundAmount = 0;
            if ($newStatus === 'partial' && $remains > 0) {
                $refundAmount = ($remains / $order['quantity']) * $order['charge'];
            } elseif (in_array($newStatus, ['cancelled', 'refunded'])) {
                $refundAmount = $order['charge'];
            }

            if ($refundAmount > 0) {
                $db->query("UPDATE users SET balance = balance + ? WHERE id = ?", [$refundAmount, $order['user_id']]);
                $db->insert('transactions', [
                    'transaction_id' => generate_transaction_id(),
                    'user_id' => $order['user_id'],
                    'type' => 'refund',
                    'amount' => $refundAmount,
                    'balance_before' => 0,
                    'balance_after' => 0,
                    'description' => "Auto-refund for order #{$order['order_id']}",
                    'status' => 'completed',
                ]);
                echo "  Refunded {$refundAmount} for order #{$order['order_id']}\n";
            }
        }

        $orderModel->update($order['id'], $updateData);
        echo "  Order #{$order['order_id']}: {$order['status']} -> {$newStatus}\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Order sync completed\n";
