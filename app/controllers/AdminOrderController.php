<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Notification;

class AdminOrderController extends Controller
{
    private Order $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $status = sanitize_input($_GET['status'] ?? '');
        $search = sanitize_input($_GET['search'] ?? '');
        $orders = $this->orderModel->getOrdersWithDetails($page, 25, $status, $search);

        $this->view('admin.orders', [
            'title' => 'Orders',
            'orders' => $orders,
            'status_filter' => $status,
            'search' => $search,
        ]);
    }

    public function updateStatus(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $orderId = (int) $id;
        $status = $this->input('status');
        $validStatuses = ['pending', 'processing', 'in_progress', 'completed', 'partial', 'cancelled', 'refunded', 'failed'];

        if (!in_array($status, $validStatuses)) {
            $this->json(['error' => 'Invalid status.'], 400);
            return;
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            $this->json(['error' => 'Order not found.'], 404);
            return;
        }

        $updateData = ['status' => $status];

        if ($status === 'completed') {
            $updateData['remains'] = 0;
        }

        // Handle refund
        if (in_array($status, ['cancelled', 'refunded']) && in_array($order['status'], ['pending', 'processing', 'in_progress'])) {
            $refundAmount = $order['charge'];
            if ($status === 'partial') {
                $remains = (int) $this->input('remains', 0);
                $updateData['remains'] = $remains;
                $refundAmount = ($remains / $order['quantity']) * $order['charge'];
            }

            $userModel = new User();
            $userModel->updateBalance($order['user_id'], $refundAmount);

            $transactionModel = new Transaction();
            $transactionModel->createTransaction(
                $order['user_id'],
                'refund',
                $refundAmount,
                "Refund for order #{$order['order_id']}"
            );

            $notificationModel = new Notification();
            $notificationModel->send(
                $order['user_id'],
                'Order Refunded',
                format_money($refundAmount) . " refunded for order #{$order['order_id']}",
                'info'
            );
        }

        $this->orderModel->update($orderId, $updateData);
        $this->json(['success' => true, 'message' => 'Order status updated.']);
    }
}
