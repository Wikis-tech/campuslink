<?php

/**

 * CampusLink — Database Class

 * Singleton PDO wrapper

 * Usage: $db = DB::getInstance();

 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class DB {

    private static ?DB  $instance = null;

    private PDO         $pdo;

    // ── Private constructor — use getInstance() ────────────────────

    private function __construct() {

        $dsn = sprintf(

            'mysql:host=%s;dbname=%s;charset=%s',

            DB_HOST,

            DB_NAME,

            DB_CHARSET

        );

        $options = [

            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES   => false,

            PDO::ATTR_PERSISTENT         => false,

        ];

        try {

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {

            throw new Exception('Database connection failed: ' . $e->getMessage());

        }

    }

    // ── Singleton getter ───────────────────────────────────────────

    public static function getInstance(): static {

        if (static::$instance === null) {

            static::$instance = new static();

        }

        return static::$instance;

    }

    // Prevent cloning and unserialization

    private function __clone() {}

    public function __wakeup() {

        throw new Exception('Cannot unserialize singleton.');

    }

    // ── Raw PDO access (use sparingly) ────────────────────────────

    public function pdo(): PDO {

        return $this->pdo;

    }

    // ─────────────────────────────────────────────────────────────

    // QUERY METHODS

    // ─────────────────────────────────────────────────────────────

    /**

     * Run any SQL — returns PDOStatement

     * Use for INSERT, UPDATE, DELETE

     *

     * $db->execute("INSERT INTO users (name) VALUES (?)", ['John']);

     */

    public function execute(string $sql, array $params = []): PDOStatement {

        try {

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute($params);

            return $stmt;

        } catch (PDOException $e) {

            throw new Exception('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);

        }

    }

    /**

     * Fetch a single row as associative array

     * Returns null if not found

     *

     * $user = $db->row("SELECT * FROM users WHERE id = ?", [1]);

     */

    public function row(string $sql, array $params = []): ?array {

        $result = $this->execute($sql, $params)->fetch();

        return $result ?: null;

    }

    /**

     * Fetch all rows as array of associative arrays

     *

     * $users = $db->rows("SELECT * FROM users WHERE status = ?", ['active']);

     */

    public function rows(string $sql, array $params = []): array {

        return $this->execute($sql, $params)->fetchAll();

    }

    /**

     * Fetch a single value from the first column of the first row

     *

     * $count = $db->value("SELECT COUNT(*) FROM users");

     */

    public function value(string $sql, array $params = []): mixed {

        $result = $this->execute($sql, $params)->fetchColumn();

        return $result !== false ? $result : null;

    }

    /**

     * Get the last inserted auto-increment ID

     *

     * $id = $db->lastInsertId();

     */

    public function lastInsertId(): int {

        return (int)$this->pdo->lastInsertId();

    }

    /**

     * Insert a row from an associative array

     * Returns the new row's ID

     *

     * $id = $db->insert('users', ['name'=>'John', 'email'=>'j@e.com']);

     */

    public function insert(string $table, array $data): int {

        $columns     = implode(', ', array_keys($data));

        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql         = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";

        $this->execute($sql, array_values($data));

        return $this->lastInsertId();

    }

    /**

     * Update rows matching a WHERE clause

     *

     * $db->update('users', ['name'=>'Jane'], 'id = ?', [1]);

     */

    public function update(string $table, array $data, string $where, array $whereParams = []): int {

        $setParts = implode(', ', array_map(fn($col) => "`{$col}` = ?", array_keys($data)));

        $sql      = "UPDATE `{$table}` SET {$setParts} WHERE {$where}";

        $params   = array_merge(array_values($data), $whereParams);

        return $this->execute($sql, $params)->rowCount();

    }

    /**

     * Delete rows matching a WHERE clause

     *

     * $db->delete('users', 'id = ?', [1]);

     */

    public function delete(string $table, string $where, array $params = []): int {

        $sql = "DELETE FROM `{$table}` WHERE {$where}";

        return $this->execute($sql, $params)->rowCount();

    }

    /**

     * Check if a row exists

     *

     * $exists = $db->exists('users', 'email = ?', ['test@test.com']);

     */

    public function exists(string $table, string $where, array $params = []): bool {

        $count = $this->value("SELECT COUNT(*) FROM `{$table}` WHERE {$where}", $params);

        return (int)$count > 0;

    }

    /**

     * Begin a transaction

     */

    public function beginTransaction(): void {

        $this->pdo->beginTransaction();

    }

    /**

     * Commit a transaction

     */

    public function commit(): void {

        $this->pdo->commit();

    }

    /**

     * Roll back a transaction

     */

    public function rollback(): void {

        $this->pdo->rollBack();

    }

    /**

     * Run code inside a transaction safely

     * Automatically rolls back on exception

     *

     * $db->transaction(function() use ($db) {

     *     $db->insert('users', [...]);

     *     $db->insert('profiles', [...]);

     * });

     */

    public function transaction(callable $callback): mixed {

        $this->beginTransaction();

        try {

            $result = $callback($this);

            $this->commit();

            return $result;

        } catch (Exception $e) {

            $this->rollback();

            throw $e;

        }

    }

}