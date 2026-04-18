<?php
/**
 * CampusLink - Base Model
 * All models extend this class.
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

abstract class Model
{
    protected Database $db;
    protected string   $table  = '';
    protected string   $primaryKey = 'id';

    // ============================================================
    // Constructor
    // ============================================================
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================================
    // Find a single record by primary key
    // ============================================================
    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
    }

    // ============================================================
    // Find a single record by column
    // ============================================================
    public function findBy(string $column, mixed $value): ?array
    {
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1",
            [$value]
        );
    }

    // ============================================================
    // Find all records
    // ============================================================
    public function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $orderBy   = preg_replace('/[^a-zA-Z0-9_]/', '', $orderBy);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` $direction"
        );
    }

    // ============================================================
    // Find all with conditions
    // ============================================================
    public function where(string $conditions, array $params = [], string $orderBy = 'id DESC'): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE $conditions ORDER BY $orderBy",
            $params
        );
    }

    // ============================================================
    // Create a new record
    // ============================================================
    public function create(array $data): string|false
    {
        if (empty($data)) return false;

        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})";

        try {
            return $this->db->insert($sql, array_values($data));
        } catch (Exception $e) {
            return false;
        }
    }

    // ============================================================
    // Update a record by primary key
    // ============================================================
    public function update(int $id, array $data): bool
    {
        if (empty($data)) return false;

        $setParts = array_map(fn($col) => "`$col` = ?", array_keys($data));
        $set = implode(', ', $setParts);

        $values = array_values($data);
        $values[] = $id;

        $sql = "UPDATE `{$this->table}` SET $set WHERE `{$this->primaryKey}` = ?";

        try {
            $affected = $this->db->execute($sql, $values);
            return $affected >= 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // ============================================================
    // Delete a record by primary key
    // ============================================================
    public function delete(int $id): bool
    {
        try {
            $affected = $this->db->execute(
                "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
                [$id]
            );
            return $affected > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // ============================================================
    // Count all records
    // ============================================================
    public function count(string $conditions = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if (!empty($conditions)) {
            $sql .= " WHERE $conditions";
        }
        return (int)$this->db->fetchColumn($sql, $params);
    }

    // ============================================================
    // Check if record exists
    // ============================================================
    public function exists(string $column, mixed $value, int $excludeId = 0): bool
    {
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);

        if ($excludeId > 0) {
            return (bool)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM `{$this->table}` WHERE `{$column}` = ? AND `{$this->primaryKey}` != ?",
                [$value, $excludeId]
            );
        }

        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE `{$column}` = ?",
            [$value]
        );
    }

    // ============================================================
    // Paginate records
    // ============================================================
    public function paginate(
        int    $page,
        int    $perPage,
        string $conditions = '',
        array  $params = [],
        string $orderBy = 'id DESC'
    ): array {
        $offset = ($page - 1) * $perPage;
        $total  = $this->count($conditions, $params);

        $sql = "SELECT * FROM `{$this->table}`";
        if (!empty($conditions)) {
            $sql .= " WHERE $conditions";
        }
        $sql .= " ORDER BY $orderBy LIMIT ? OFFSET ?";

        $queryParams = array_merge($params, [$perPage, $offset]);
        $records = $this->db->fetchAll($sql, $queryParams);

        return [
            'data'         => $records,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'total_pages'  => (int)ceil($total / $perPage),
            'has_next'     => ($page * $perPage) < $total,
            'has_prev'     => $page > 1,
        ];
    }

    // ============================================================
    // Get table name
    // ============================================================
    public function getTable(): string
    {
        return $this->table;
    }
}