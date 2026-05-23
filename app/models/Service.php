<?php

namespace App\Models;

use App\Core\Model;

class Service extends Model
{
    protected string $table = 'services';

    public function getActiveServices(): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, c.name as category_name, c.slug as category_slug
             FROM {$this->table} s
             JOIN categories c ON s.category_id = c.id
             WHERE s.status = 'active' AND c.status = 'active'
             ORDER BY c.sort_order ASC, s.sort_order ASC"
        );
    }

    public function getGroupedByCategory(): array
    {
        $services = $this->getActiveServices();
        $grouped = [];
        foreach ($services as $service) {
            $cat = $service['category_name'];
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $service;
        }
        return $grouped;
    }

    public function getWithProvider(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT s.*, c.name as category_name, p.name as provider_name, p.api_url, p.api_key as provider_api_key
             FROM {$this->table} s
             JOIN categories c ON s.category_id = c.id
             LEFT JOIN providers p ON s.provider_id = p.id
             WHERE s.id = ?",
            [$id]
        );
    }

    public function getAllWithDetails(): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, c.name as category_name, p.name as provider_name
             FROM {$this->table} s
             JOIN categories c ON s.category_id = c.id
             LEFT JOIN providers p ON s.provider_id = p.id
             ORDER BY c.sort_order ASC, s.sort_order ASC"
        );
    }
}
