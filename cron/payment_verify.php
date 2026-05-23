<?php
/**
 * Payment Verification Cron Job
 * Checks pending payment statuses with payment gateways
 * Run every 5 minutes: */5 * * * * php /path/to/cron/payment_verify.php
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

echo "[" . date('Y-m-d H:i:s') . "] Payment verification started\n";

$db = Database::getInstance();

// Auto-cancel old pending payments (> 24 hours)
$expired = $db->query(
    "UPDATE payment_requests SET status = 'cancelled', admin_note = 'Auto-cancelled: expired after 24 hours'
     WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)"
);

$cancelledCount = $expired->rowCount();
if ($cancelledCount > 0) {
    echo "  Auto-cancelled {$cancelledCount} expired payment requests\n";
}

// Check pending PayPal/Stripe payments (placeholder for real gateway verification)
$pendingPayments = $db->fetchAll(
    "SELECT * FROM payment_requests
     WHERE status = 'pending'
     AND gateway IN ('paypal', 'stripe')
     AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
     LIMIT 50"
);

echo "Found " . count($pendingPayments) . " pending gateway payments to verify\n";

foreach ($pendingPayments as $payment) {
    // In production, verify with actual gateway API
    // PayPal: Use Orders API to check payment status
    // Stripe: Use PaymentIntents API to check status
    echo "  Payment #{$payment['id']} ({$payment['gateway']}): verification pending - implement gateway API check\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Payment verification completed\n";
