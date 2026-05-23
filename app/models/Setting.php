<?php

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';

    public function get(string $key, string $default = ''): string
    {
        $row = $this->db->fetch(
            "SELECT value FROM {$this->table} WHERE key_name = ?",
            [$key]
        );
        return $row['value'] ?? $default;
    }

    public function set(string $key, string $value, string $group = 'general'): void
    {
        $existing = $this->db->fetch(
            "SELECT id FROM {$this->table} WHERE key_name = ?",
            [$key]
        );

        if ($existing) {
            $this->db->update($this->table, ['value' => $value], 'key_name = ?', [$key]);
        } else {
            $this->create(['key_name' => $key, 'value' => $value, 'group_name' => $group]);
        }
    }

    public function getGroup(string $group): array
    {
        $rows = $this->db->fetchAll(
            "SELECT key_name, value FROM {$this->table} WHERE group_name = ?",
            [$group]
        );
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key_name']] = $row['value'];
        }
        return $settings;
    }

    public function getAll(): array
    {
        $rows = $this->db->fetchAll("SELECT key_name, value FROM {$this->table}");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key_name']] = $row['value'];
        }
        return $settings;
    }
}
