<?php
/**
 * External API Controller
 * Provides SMM panel API for third-party integrations
 * Supports: services, add order, order status, multi-status, refill, cancel, balance
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Service;
use App\Models\Order;
use App\Services\OrderService;

class ExternalApiController extends Controller
{
    public function handle(): void
    {
        header('Content-Type: application/json');

        $key = $_POST['key'] ?? $_GET['key'] ?? '';
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        if (empty($key)) {
            $this->json(['error' => 'API key is required.'], 401);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByApiKey($key);

        if (!$user || $user['status'] !== 'active') {
            $this->json(['error' => 'Invalid API key.'], 401);
            return;
        }

        match ($action) {
            'services' => $this->apiServices(),
            'add' => $this->apiAddOrder($user),
            'status' => $this->apiStatus($user),
            'refill' => $this->apiRefill($user),
            'cancel' => $this->apiCancel($user),
            'balance' => $this->json(['balance' => $user['balance'], 'currency' => env('CURRENCY', 'USD')]),
            default => $this->json(['error' => 'Invalid action.'], 400),
        };
    }

    private function apiServices(): void
    {
        $serviceModel = new Service();
        $services = $serviceModel->getActiveServices();

        $result = [];
        foreach ($services as $s) {
            $result[] = [
                'service' => $s['id'],
                'name' => $s['name'],
                'type' => $s['type'],
                'category' => $s['category_name'],
                'rate' => $s['price_per_1000'],
                'min' => $s['min_quantity'],
                'max' => $s['max_quantity'],
                'dripfeed' => (bool) $s['drip_feed'],
                'refill' => (bool) $s['refill'],
                'cancel' => (bool) $s['cancel'],
            ];
        }

        $this->json($result);
    }

    private function apiAddOrder(array $user): void
    {
        $serviceId = (int) ($_POST['service'] ?? 0);
        $link = $_POST['link'] ?? '';
        $quantity = (int) ($_POST['quantity'] ?? 0);

        $dripFeed = [];
        if (!empty($_POST['runs'])) {
            $dripFeed = [
                'runs' => (int) $_POST['runs'],
                'interval' => (int) ($_POST['interval'] ?? 0),
                'quantity' => $quantity,
            ];
        }

        $orderService = new OrderService();
        $result = $orderService->createOrder($user['id'], $serviceId, $link, $quantity, $dripFeed);

        if ($result['success']) {
            $orderModel = new Order();
            $order = $orderModel->findByOrderId($result['order_id']);
            $this->json(['order' => $order['id']]);
        } else {
            $this->json(['error' => $result['error']], 400);
        }
    }

    private function apiStatus(array $user): void
    {
        $orderModel = new Order();

        if (isset($_POST['orders'])) {
            $ids = array_map('intval', explode(',', $_POST['orders']));
            $result = [];
            foreach ($ids as $id) {
                $order = $orderModel->find($id);
                if ($order && $order['user_id'] === $user['id']) {
                    $result[$id] = [
                        'charge' => $order['charge'],
                        'start_count' => $order['start_count'],
                        'status' => ucfirst($order['status']),
                        'remains' => $order['remains'],
                        'currency' => env('CURRENCY', 'USD'),
                    ];
                }
            }
            $this->json($result);
        } else {
            $orderId = (int) ($_POST['order'] ?? 0);
            $order = $orderModel->find($orderId);

            if (!$order || $order['user_id'] !== $user['id']) {
                $this->json(['error' => 'Order not found.'], 404);
                return;
            }

            $this->json([
                'charge' => $order['charge'],
                'start_count' => $order['start_count'],
                'status' => ucfirst($order['status']),
                'remains' => $order['remains'],
                'currency' => env('CURRENCY', 'USD'),
            ]);
        }
    }

    private function apiRefill(array $user): void
    {
        $orderId = (int) ($_POST['order'] ?? 0);
        $orderService = new OrderService();
        $result = $orderService->requestRefill($orderId, $user['id']);

        if ($result['success']) {
            $this->json(['refill' => $orderId]);
        } else {
            $this->json(['error' => $result['error']], 400);
        }
    }

    private function apiCancel(array $user): void
    {
        $orderId = (int) ($_POST['order'] ?? 0);
        $orderService = new OrderService();
        $result = $orderService->requestCancel($orderId, $user['id']);

        if ($result['success']) {
            $this->json(['cancel' => $orderId]);
        } else {
            $this->json(['error' => $result['error']], 400);
        }
    }
}
