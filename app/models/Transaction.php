<?php

namespace App\Models;

use App\Core\Model;

class Transaction extends Model
{
    protected string $table = 'transactions';

    public function getUserTransactions(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count('user_id = ?', [$userId]);
        $data = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}",
            [$userId]
        );
        return [
            'data' => $data,
            'current_page' => $page,
            'total_pages' => (int) ceil($total / $perPage),
            'total' => (int) $total
        ];
    }

    public function createTransaction(int $userId, string $type, float $amount, string $description, string $method = '', string $paymentId = ''): int
    {
        $user = (new User())->find($userId);
        $balanceBefore = (float) $user['balance'];
        $balanceAfter = $balanceBefore + $amount;

        return $this->create([
            'transaction_id' => generate_transaction_id(),
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
            'payment_method' => $method,
            'payment_id' => $paymentId,
            'status' => 'completed'
        ]);
    }

    public function getRevenueChart(int $days = 30): array
    {
        return $this->db->fetchAll(
            "SELECT DATE(created_at) as date, SUM(amount) as total
             FROM {$this->table}
             WHERE type = 'deposit' AND status = 'completed'
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$days]
        );
    }
}
