<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Service;
use App\Models\Notification;

class UserDashboardController extends Controller
{
    public function index(): void
    {
        $userId = current_user_id();
        $orderModel = new Order();
        $transactionModel = new Transaction();

        $stats = [
            'total_orders' => $orderModel->count('user_id = ?', [$userId]),
            'pending_orders' => $orderModel->count("user_id = ? AND status = 'pending'", [$userId]),
            'completed_orders' => $orderModel->count("user_id = ? AND status = 'completed'", [$userId]),
            'total_spent' => $orderModel->sum('charge', 'user_id = ?', [$userId]),
        ];

        $recentOrders = $this->db->fetchAll(
            "SELECT o.*, s.name as service_name FROM orders o
             JOIN services s ON o.service_id = s.id
             WHERE o.user_id = ? ORDER BY o.id DESC LIMIT 10",
            [$userId]
        );

        $recentTransactions = $this->db->fetchAll(
            "SELECT * FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 10",
            [$userId]
        );

        $this->view('user.dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
