<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Service;
use App\Models\Category;
use App\Services\OrderService;

class UserOrderController extends Controller
{
    private Order $orderModel;
    private Service $serviceModel;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->serviceModel = new Service();
        $this->orderService = new OrderService();
    }

    public function newOrder(): void
    {
        $categoryModel = new Category();
        $services = $this->serviceModel->getGroupedByCategory();
        $categories = $categoryModel->getActive();

        $this->view('user.new-order', [
            'title' => 'New Order',
            'services' => $services,
            'categories' => $categories,
        ]);
    }

    public function create(): void
    {
        if (!$this->validateCsrf()) return;

        $result = $this->orderService->createOrder(
            current_user_id(),
            (int) $this->input('service_id'),
            $this->input('link'),
            (int) $this->input('quantity'),
            [
                'interval' => (int) $this->input('drip_feed_interval'),
                'quantity' => (int) $this->input('drip_feed_quantity'),
                'runs' => (int) $this->input('drip_feed_runs'),
            ]
        );

        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function bulkOrder(): void
    {
        $this->view('user.bulk-order', ['title' => 'Bulk Orders']);
    }

    public function bulkCreate(): void
    {
        if (!$this->validateCsrf()) return;

        $rawOrders = $_POST['orders'] ?? '';
        $lines = array_filter(explode("\n", $rawOrders));

        $orders = [];
        foreach ($lines as $line) {
            $parts = array_map('trim', explode('|', $line));
            if (count($parts) >= 3) {
                $orders[] = [
                    'service_id' => (int) $parts[0],
                    'link' => $parts[1],
                    'quantity' => (int) $parts[2],
                    'drip_feed' => [],
                ];
            }
        }

        if (empty($orders)) {
            $this->json(['error' => 'No valid orders found. Format: service_id|link|quantity'], 400);
            return;
        }

        $results = $this->orderService->createBulkOrders(current_user_id(), $orders);
        $success = count(array_filter($results, fn($r) => $r['success']));
        $failed = count($results) - $success;

        $this->json([
            'success' => true,
            'message' => "{$success} orders placed, {$failed} failed.",
            'results' => $results
        ]);
    }

    public function list(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $status = sanitize_input($_GET['status'] ?? '');
        $orders = $this->orderModel->getUserOrders(current_user_id(), $page, 20, $status);

        $this->view('user.orders', [
            'title' => 'My Orders',
            'orders' => $orders,
            'status_filter' => $status,
        ]);
    }

    public function detail(string $id): void
    {
        $order = $this->orderModel->find((int) $id);
        if (!$order || $order['user_id'] !== current_user_id()) {
            $this->redirect('/user/orders');
            return;
        }

        $service = $this->serviceModel->find($order['service_id']);

        $this->view('user.order-detail', [
            'title' => "Order #{$order['order_id']}",
            'order' => $order,
            'service' => $service,
        ]);
    }

    public function refill(string $id): void
    {
        $result = $this->orderService->requestRefill((int) $id, current_user_id());
        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function cancel(string $id): void
    {
        $result = $this->orderService->requestCancel((int) $id, current_user_id());
        $this->json($result, $result['success'] ? 200 : 400);
    }

    public function getServiceInfo(): void
    {
        $serviceId = (int) ($_GET['id'] ?? 0);
        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            $this->json(['error' => 'Service not found'], 404);
            return;
        }
        $this->json([
            'id' => $service['id'],
            'name' => $service['name'],
            'description' => $service['description'],
            'price_per_1000' => $service['price_per_1000'],
            'min_quantity' => $service['min_quantity'],
            'max_quantity' => $service['max_quantity'],
            'drip_feed' => (bool) $service['drip_feed'],
            'refill' => (bool) $service['refill'],
            'cancel' => (bool) $service['cancel'],
            'type' => $service['type'],
        ]);
    }
}
