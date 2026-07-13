<?php
declare(strict_types=1);

/**
 * BaseModel - Provides reusable PDO data access methods for all models.
 */
abstract class BaseModel
{
    protected PDO    $db;
    protected string $table       = '';
    protected string $primaryKey  = 'id';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Core query helpers ─────────────────────────────────────────────────

    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    protected function countAll(): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM {$this->table}");
        return (int) ($row['total'] ?? 0);
    }

    protected function countWhere(string $where, array $params = []): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM {$this->table} WHERE {$where}", $params);
        return (int) ($row['total'] ?? 0);
    }

    // ── CRUD ───────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function findAll(string $orderBy = 'created_at', string $dir = 'DESC'): array
    {
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        return $this->fetchAll("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$dir}");
    }

    public function insert(array $data): int
    {
        $columns     = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(static fn($k) => ":{$k}", array_keys($data)));
        $this->execute("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})", $data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $assignments = implode(', ', array_map(static fn($k) => "{$k} = :{$k}", array_keys($data)));
        $data[$this->primaryKey] = $id;
        return $this->execute(
            "UPDATE {$this->table} SET {$assignments} WHERE {$this->primaryKey} = :{$this->primaryKey}",
            $data
        );
    }

    public function delete(int $id): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id",
            ['id' => $id]
        );
    }

    public function bulkDelete(array $ids): bool
    {
        if (empty($ids)) return false;
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} IN ({$placeholders})");
        return $stmt->execute($ids);
    }

    public function toggleStatus(int $id, string $field = 'status', string $active = 'active', string $inactive = 'inactive'): bool
    {
        $row = $this->findById($id);
        if (!$row) return false;
        $newStatus = $row[$field] === $active ? $inactive : $active;
        return $this->update($id, [$field => $newStatus]);
    }

    // ── DataTables-compatible response builder ─────────────────────────────

    protected function datatableResponse(array $rows, int $filtered, int $total, int $draw): array
    {
        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }
}
