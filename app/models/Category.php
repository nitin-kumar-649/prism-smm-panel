<?php

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected string $table = 'categories';

    public function getActive(): array
    {
        return $this->where("status = 'active'", [], 'sort_order ASC');
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    public function getWithServiceCount(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, COUNT(s.id) as service_count
             FROM {$this->table} c
             LEFT JOIN services s ON c.id = s.category_id AND s.status = 'active'
             GROUP BY c.id
             ORDER BY c.sort_order ASC"
        );
    }
}
