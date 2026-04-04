<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;
    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';
            $db     = $config['db'];
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $db['name']);
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone='+01:00'",
            ];
            try {
                self::$instance = new PDO($dsn, $db['user'], $db['pass'], $options);
            } catch (PDOException $e) {
                error_log('[Campuslink DB] Connection failed: ' . $e->getMessage());
                http_response_code(503);
                die(json_encode(['error' => 'Service temporarily unavailable.']));
            }
        }
        return self::$instance;
    }
}