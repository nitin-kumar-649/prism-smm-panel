<?php

namespace App\Models;

use App\Core\Model;

class Ticket extends Model
{
    protected string $table = 'tickets';

    public function getUserTickets(int $userId, int $page = 1, int $perPage = 20): array
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

    public function getWithUser(int $page = 1, int $perPage = 20, string $status = ''): array
    {
        $where = '1=1';
        $params = [];
        if ($status) {
            $where .= ' AND t.status = ?';
            $params[] = $status;
        }
        $offset = ($page - 1) * $perPage;
        $total = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} t WHERE {$where}",
            $params
        )['cnt'];

        $data = $this->db->fetchAll(
            "SELECT t.*, u.username, u.email
             FROM {$this->table} t
             JOIN users u ON t.user_id = u.id
             WHERE {$where}
             ORDER BY
               CASE t.status WHEN 'open' THEN 0 WHEN 'answered' THEN 1 ELSE 2 END,
               t.updated_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data' => $data,
            'current_page' => $page,
            'total_pages' => (int) ceil($total / $perPage),
            'total' => (int) $total
        ];
    }

    public function getMessages(int $ticketId): array
    {
        return $this->db->fetchAll(
            "SELECT tm.*, u.username, u.avatar
             FROM ticket_messages tm
             JOIN users u ON tm.user_id = u.id
             WHERE tm.ticket_id = ?
             ORDER BY tm.id ASC",
            [$ticketId]
        );
    }

    public function addMessage(int $ticketId, int $userId, string $message, bool $isAdmin = false, string $attachment = ''): int
    {
        $msgId = $this->db->insert('ticket_messages', [
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'message' => $message,
            'is_admin' => $isAdmin ? 1 : 0,
            'attachment' => $attachment ?: null
        ]);

        $this->update($ticketId, [
            'status' => $isAdmin ? 'answered' : 'open',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $msgId;
    }
}
