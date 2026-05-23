<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    public function findByApiKey(string $apiKey): ?array
    {
        return $this->findBy('api_key', $apiKey);
    }

    public function updateBalance(int $userId, float $amount): bool
    {
        $this->db->query(
            "UPDATE {$this->table} SET balance = balance + ?, updated_at = NOW() WHERE id = ?",
            [$amount, $userId]
        );
        return true;
    }

    public function addSpent(int $userId, float $amount): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET spent = spent + ? WHERE id = ?",
            [$amount, $userId]
        );
    }

    public function setOtp(int $userId, string $code): void
    {
        $expiry = date('Y-m-d H:i:s', time() + (int) env('OTP_EXPIRY', 300));
        $this->update($userId, [
            'otp_code' => $code,
            'otp_expires' => $expiry
        ]);
    }

    public function verifyOtp(int $userId, string $code): bool
    {
        $user = $this->find($userId);
        if (!$user) return false;
        return $user['otp_code'] === $code
            && $user['otp_expires']
            && strtotime($user['otp_expires']) > time();
    }

    public function recordLogin(int $userId, string $ip): void
    {
        $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s'),
            'last_ip' => $ip,
            'login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    public function incrementLoginAttempts(int $userId): void
    {
        $user = $this->find($userId);
        if (!$user) return;

        $attempts = $user['login_attempts'] + 1;
        $data = ['login_attempts' => $attempts];

        if ($attempts >= 5) {
            $data['locked_until'] = date('Y-m-d H:i:s', time() + 900);
        }

        $this->update($userId, $data);
    }

    public function isLocked(int $userId): bool
    {
        $user = $this->find($userId);
        if (!$user || !$user['locked_until']) return false;
        return strtotime($user['locked_until']) > time();
    }

    public function getStats(): array
    {
        return [
            'total' => $this->count(),
            'active' => $this->count("status = 'active'"),
            'banned' => $this->count("status = 'banned'"),
            'today' => $this->count("DATE(created_at) = CURDATE()"),
        ];
    }

    public function getTopUsers(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT id, username, email, balance, spent, created_at FROM {$this->table} WHERE role = 'user' ORDER BY spent DESC LIMIT ?",
            [$limit]
        );
    }
}
