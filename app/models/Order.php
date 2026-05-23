<?php

namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    protected string $table = 'orders';

    public function findByOrderId(string $orderId): ?array
    {
        return $this->findBy('order_id', $orderId);
    }

    public function getUserOrders(int $userId, int $page = 1, int $perPage = 20, string $status = ''): array
    {
        $where = 'o.user_id = ?';
        $params = [$userId];

        if ($status) {
            $where .= ' AND o.status = ?';
            $params[] = $status;
        }

        $offset = ($page - 1) * $perPage;
        $total = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} o WHERE {$where}",
            $params
        )['cnt'];

        $data = $this->db->fetchAll(
            "SELECT o.*, s.name as service_name, c.name as category_name
             FROM {$this->table} o
             JOIN services s ON o.service_id = s.id
             JOIN categories c ON s.category_id = c.id
             WHERE {$where}
             ORDER BY o.id DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data' => $data,
            'current_page' => $page,
            'total_pages' => (int) ceil($total / $perPage),
            'total' => (int) $total,
            'per_page' => $perPage
        ];
    }

    public function getOrdersWithDetails(int $page = 1, int $perPage = 20, string $status = '', string $search = ''): array
    {
        $where = '1=1';
        $params = [];

        if ($status) {
            $where .= ' AND o.status = ?';
            $params[] = $status;
        }
        if ($search) {
            $where .= ' AND (o.order_id LIKE ? OR o.link LIKE ? OR u.username LIKE ?)';
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        $offset = ($page - 1) * $perPage;
        $total = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} o
             JOIN users u ON o.user_id = u.id
             WHERE {$where}",
            $params
        )['cnt'];

        $data = $this->db->fetchAll(
            "SELECT o.*, s.name as service_name, u.username, c.name as category_name
             FROM {$this->table} o
             JOIN services s ON o.service_id = s.id
             JOIN categories c ON s.category_id = c.id
             JOIN users u ON o.user_id = u.id
             WHERE {$where}
             ORDER BY o.id DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data' => $data,
            'current_page' => $page,
            'total_pages' => (int) ceil($total / $perPage),
            'total' => (int) $total
        ];
    }

    public function getPendingOrders(): array
    {
        return $this->db->fetchAll(
            "SELECT o.*, s.provider_id, s.provider_service_id
             FROM {$this->table} o
             JOIN services s ON o.service_id = s.id
             WHERE o.status IN ('pending', 'processing')
             ORDER BY o.id ASC LIMIT 100"
        );
    }

    public function getRefillRequests(): array
    {
        return $this->db->fetchAll(
            "SELECT o.*, s.provider_id, s.provider_service_id
             FROM {$this->table} o
             JOIN services s ON o.service_id = s.id
             WHERE o.refill_requested = 1 AND o.status = 'completed'
             ORDER BY o.id ASC LIMIT 50"
        );
    }

    public function getStats(): array
    {
        return [
            'total' => $this->count(),
            'pending' => $this->count("status = 'pending'"),
            'processing' => $this->count("status IN ('processing', 'in_progress')"),
            'completed' => $this->count("status = 'completed'"),
            'cancelled' => $this->count("status = 'cancelled'"),
            'today' => $this->count("DATE(created_at) = CURDATE()"),
            'revenue_today' => $this->sum('charge', "DATE(created_at) = CURDATE()"),
            'revenue_month' => $this->sum('charge', "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"),
            'revenue_total' => $this->sum('charge'),
        ];
    }

    public function getChartData(int $days = 30): array
    {
        return $this->db->fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(charge) as revenue
             FROM {$this->table}
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$days]
        );
    }
}
