<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Transaction;

class AdminDashboardController extends Controller
{
    public function index(): void
    {
        $orderModel = new Order();
        $userModel = new User();

        $stats = [
            'orders' => $orderModel->getStats(),
            'users' => $userModel->getStats(),
        ];

        $chartData = $orderModel->getChartData(30);
        $recentOrders = $this->db->fetchAll(
            "SELECT o.*, s.name as service_name, u.username
             FROM orders o
             JOIN services s ON o.service_id = s.id
             JOIN users u ON o.user_id = u.id
             ORDER BY o.id DESC LIMIT 15"
        );

        $topUsers = $userModel->getTopUsers(5);

        $this->view('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'chartData' => $chartData,
            'recentOrders' => $recentOrders,
            'topUsers' => $topUsers,
        ]);
    }
}
