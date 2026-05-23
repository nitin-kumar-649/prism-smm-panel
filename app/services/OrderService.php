<?php
/**
 * Order Service
 * Handles order creation, processing, refill, and cancellation
 */

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Notification;

class OrderService
{
    private Database $db;
    private Order $orderModel;
    private Service $serviceModel;
    private User $userModel;
    private Transaction $transactionModel;
    private Notification $notification;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->orderModel = new Order();
        $this->serviceModel = new Service();
        $this->userModel = new User();
        $this->transactionModel = new Transaction();
        $this->notification = new Notification();
    }

    public function createOrder(int $userId, int $serviceId, string $link, int $quantity, array $dripFeed = []): array
    {
        $service = $this->serviceModel->getWithProvider($serviceId);
        if (!$service || $service['status'] !== 'active') {
            return ['success' => false, 'error' => 'Service not available.'];
        }

        if ($quantity < $service['min_quantity'] || $quantity > $service['max_quantity']) {
            return ['success' => false, 'error' => "Quantity must be between {$service['min_quantity']} and {$service['max_quantity']}."];
        }

        if (!is_valid_url($link)) {
            return ['success' => false, 'error' => 'Please enter a valid URL.'];
        }

        $charge = ($quantity / 1000) * $service['price_per_1000'];
        $charge = round($charge, 4);

        $user = $this->userModel->find($userId);
        if ($user['balance'] < $charge) {
            return ['success' => false, 'error' => 'Insufficient balance. Please add funds.'];
        }

        $this->db->beginTransaction();
        try {
            $this->userModel->updateBalance($userId, -$charge);
            $this->userModel->addSpent($userId, $charge);

            $this->transactionModel->createTransaction(
                $userId,
                'purchase',
                -$charge,
                "Order: {$service['name']} x{$quantity}"
            );

            $orderId = generate_order_id();
            $orderData = [
                'order_id' => $orderId,
                'user_id' => $userId,
                'service_id' => $serviceId,
                'link' => $link,
                'quantity' => $quantity,
                'charge' => $charge,
                'status' => 'pending',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ];

            if (!empty($dripFeed) && $service['drip_feed']) {
                $orderData['drip_feed'] = 1;
                $orderData['drip_feed_interval'] = (int) ($dripFeed['interval'] ?? 0);
                $orderData['drip_feed_quantity'] = (int) ($dripFeed['quantity'] ?? $quantity);
                $orderData['runs'] = (int) ($dripFeed['runs'] ?? 1);
            }

            $id = $this->orderModel->create($orderData);

            // Send to provider API if configured
            if ($service['provider_id'] && $service['provider_service_id']) {
                $this->sendToProvider($id, $service);
            }

            $this->db->commit();
            Logger::info("Order created", ['order_id' => $orderId, 'user_id' => $userId]);

            return [
                'success' => true,
                'order_id' => $orderId,
                'charge' => $charge,
                'message' => 'Order placed successfully!'
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error("Order creation failed: " . $e->getMessage());
            return ['success' => false, 'error' => 'Order creation failed. Please try again.'];
        }
    }

    public function createBulkOrders(int $userId, array $orders): array
    {
        $results = [];
        foreach ($orders as $order) {
            $results[] = $this->createOrder(
                $userId,
                (int) $order['service_id'],
                $order['link'],
                (int) $order['quantity'],
                $order['drip_feed'] ?? []
            );
        }
        return $results;
    }

    private function sendToProvider(int $orderId, array $service): void
    {
        $order = $this->orderModel->find($orderId);
        if (!$order || !$service['api_url'] || !$service['provider_api_key']) {
            return;
        }

        $api = new ProviderApiService($service['api_url'], $service['provider_api_key']);

        if ($order['drip_feed']) {
            $response = $api->placeDripFeedOrder(
                $service['provider_service_id'],
                $order['link'],
                $order['quantity'],
                $order['runs'],
                $order['drip_feed_interval']
            );
        } else {
            $response = $api->placeOrder(
                $service['provider_service_id'],
                $order['link'],
                $order['quantity']
            );
        }

        if ($response && isset($response['order'])) {
            $this->orderModel->update($orderId, [
                'provider_order_id' => $response['order'],
                'status' => 'processing'
            ]);
        } elseif ($response && isset($response['error'])) {
            Logger::warning("Provider order failed: " . $response['error'], ['order_id' => $orderId]);
        }
    }

    public function requestRefill(int $orderId, int $userId): array
    {
        $order = $this->orderModel->find($orderId);
        if (!$order || $order['user_id'] !== $userId) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if ($order['status'] !== 'completed') {
            return ['success' => false, 'error' => 'Only completed orders can be refilled.'];
        }

        $service = $this->serviceModel->find($order['service_id']);
        if (!$service || !$service['refill']) {
            return ['success' => false, 'error' => 'Refill not available for this service.'];
        }

        $this->orderModel->update($orderId, ['refill_requested' => 1]);
        return ['success' => true, 'message' => 'Refill request submitted.'];
    }

    public function requestCancel(int $orderId, int $userId): array
    {
        $order = $this->orderModel->find($orderId);
        if (!$order || $order['user_id'] !== $userId) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if (!in_array($order['status'], ['pending', 'processing'])) {
            return ['success' => false, 'error' => 'Only pending/processing orders can be cancelled.'];
        }

        $service = $this->serviceModel->find($order['service_id']);
        if (!$service || !$service['cancel']) {
            return ['success' => false, 'error' => 'Cancellation not available for this service.'];
        }

        $this->orderModel->update($orderId, ['cancel_requested' => 1]);
        return ['success' => true, 'message' => 'Cancellation request submitted.'];
    }
}
