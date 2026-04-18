<?php
/**
 * CampusLink - Database Connection (Singleton PDO)
 * Uses PDO with prepared statements only.
 * Never expose this file publicly.
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    // ============================================================
    // Private constructor — Singleton pattern
    // ============================================================
    private function __construct()
    {
        $this->connect();
    }

    // ============================================================
    // Get singleton instance
    // ============================================================
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ============================================================
    // Establish PDO connection
    // ============================================================
    private function connect(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the real error, show generic message
            $this->logError($e->getMessage());

            if (APP_ENV === 'development') {
                die('<b>Database Connection Error:</b> ' . $e->getMessage());
            } else {
                die('A system error occurred. Please try again later.');
            }
        }
    }

    // ============================================================
    // Get PDO connection object
    // ============================================================
    public function getConnection(): PDO
    {
        // Reconnect if connection dropped
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    // ============================================================
    // Execute a query and return PDOStatement
    // ============================================================
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Query Error: " . $e->getMessage() . " | SQL: " . $sql);

            if (APP_ENV === 'development') {
                throw $e;
            } else {
                throw new RuntimeException('A database error occurred.');
            }
        }
    }

    // ============================================================
    // Fetch single row
    // ============================================================
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    // ============================================================
    // Fetch all rows
    // ============================================================
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    // ============================================================
    // Fetch single column value
    // ============================================================
    public function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    // ============================================================
    // Execute INSERT / UPDATE / DELETE — return affected rows
    // ============================================================
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    // ============================================================
    // Insert row and return last insert ID
    // ============================================================
    public function insert(string $sql, array $params = []): string
    {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }

    // ============================================================
    // Begin transaction
    // ============================================================
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    // ============================================================
    // Commit transaction
    // ============================================================
    public function commit(): void
    {
        $this->connection->commit();
    }

    // ============================================================
    // Rollback transaction
    // ============================================================
    public function rollback(): void
    {
        $this->connection->rollBack();
    }

    // ============================================================
    // Check if table exists
    // ============================================================
    public function tableExists(string $table): bool
    {
        try {
            $result = $this->connection->query("SELECT 1 FROM `{$table}` LIMIT 1");
            return $result !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // ============================================================
    // Get last insert ID
    // ============================================================
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    // ============================================================
    // Log database errors to file
    // ============================================================
    private function logError(string $message): void
    {
        if (!defined('LOG_ERRORS')) return;

        $logDir = dirname(LOG_ERRORS);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = sprintf(
            "[%s] [DB_ERROR] IP: %s | %s\n",
            date('Y-m-d H:i:s'),
            $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            $message
        );

        file_put_contents(LOG_ERRORS, $entry, FILE_APPEND | LOCK_EX);
    }

    // ============================================================
    // Prevent cloning and unserialization
    // ============================================================
    private function __clone() {}
    public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize Database singleton.');
    }
}