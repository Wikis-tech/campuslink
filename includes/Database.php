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
            $cfg = require __DIR__ . '/config.php';
            $db  = $cfg['db'];

            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND =>
                    "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone='+01:00'",
            ];

            try {
                self::$instance = new PDO($dsn, $db['user'], $db['pass'], $options);
            } catch (PDOException $e) {
                // Only show error details in debug mode
                $cfg = require __DIR__ . '/config.php';
                if ($cfg['app']['debug']) {
                    die('<pre>DB Connection Error: ' . $e->getMessage() . '</pre>');
                }
                error_log('[CL DB] Connection failed: ' . $e->getMessage());
                http_response_code(503);
                if (!headers_sent()) {
                    header('Content-Type: application/json');
                }
                die(json_encode([
                    'error' => 'Service temporarily unavailable. Please try again later.',
                ]));
            }
        }
        return self::$instance;
    }
}