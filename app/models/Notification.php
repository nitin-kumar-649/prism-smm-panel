<?php

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected string $table = 'notifications';

    public function getUnread(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE user_id = ? AND is_read = 0 ORDER BY id DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->count('user_id = ? AND is_read = 0', [$userId]);
    }

    public function markAsRead(int $id, int $userId): void
    {
        $this->db->update($this->table, ['is_read' => 1], 'id = ? AND user_id = ?', [$id, $userId]);
    }

    public function markAllAsRead(int $userId): void
    {
        $this->db->update($this->table, ['is_read' => 1], 'user_id = ? AND is_read = 0', [$userId]);
    }

    public function send(int $userId, string $title, string $message, string $type = 'info', string $link = ''): int
    {
        return $this->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link ?: null
        ]);
    }
}
