<?php
/**
 * Base Model - Active Record style
 * Provides CRUD operations for database tables
 */

namespace App\Core;

class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    public function findBy(string $column, $value): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }

    public function all(string $orderBy = 'id DESC', int $limit = 100): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} LIMIT {$limit}"
        );
    }

    public function where(string $condition, array $params = [], string $orderBy = 'id DESC'): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$condition} ORDER BY {$orderBy}",
            $params
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        return $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        return $this->db->count($this->table, $where, $params);
    }

    public function paginate(int $page = 1, int $perPage = 20, string $where = '1=1', array $params = []): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($where, $params);
        $totalPages = (int) ceil($total / $perPage);
        $data = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$this->primaryKey} DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
            'per_page' => $perPage
        ];
    }

    public function sum(string $column, string $where = '1=1', array $params = []): float
    {
        $result = $this->db->fetch(
            "SELECT COALESCE(SUM({$column}), 0) as total FROM {$this->table} WHERE {$where}",
            $params
        );
        return (float) ($result['total'] ?? 0);
    }
}
