<?php
/**
 * CampusLink - Audit & Application Logger
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Logger
{
    // ============================================================
    // General purpose log entry
    // ============================================================
    public static function log(string $event, string $message, string $logFile = ''): void
    {
        if (!LOG_ENABLED) return;

        $file = $logFile ?: LOG_AUDIT;
        self::writeToFile($file, $event, $message);
    }

    // ============================================================
    // Log authentication events
    // ============================================================
    public static function auth(
        string $event,
        string $identifier,
        bool   $success,
        string $extra = ''
    ): void {
        $status  = $success ? 'SUCCESS' : 'FAILED';
        $message = "Identifier: $identifier | Status: $status" . ($extra ? " | $extra" : '');
        self::writeToFile(LOG_AUDIT, "AUTH_{$event}", $message);

        // Also log to DB if login-related
        if (in_array($event, ['LOGIN', 'LOGOUT', 'FAILED_LOGIN'])) {
            self::logToDb($event, $identifier, $success);
        }
    }

    // ============================================================
    // Log payment events
    // ============================================================
    public static function payment(
        string $event,
        string $reference,
        int    $amount,
        int    $vendorId,
        string $status
    ): void {
        $message = "Ref: $reference | Amount: " . formatNaira($amount) . " | VendorID: $vendorId | Status: $status";
        self::writeToFile(LOG_PAYMENTS, "PAYMENT_{$event}", $message);
    }

    // ============================================================
    // Log security events
    // ============================================================
    public static function security(string $event, string $message): void
    {
        $fullMessage = $message . " | IP: " . getClientIP() . " | UA: " . substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100);
        self::writeToFile(LOG_AUDIT, "SECURITY_{$event}", $fullMessage);
    }

    // ============================================================
    // Log errors
    // ============================================================
    public static function error(string $message, string $context = ''): void
    {
        $full = $message . ($context ? " | Context: $context" : '');
        self::writeToFile(LOG_ERRORS, 'ERROR', $full);
    }

    // ============================================================
    // Log admin actions
    // ============================================================
    public static function admin(
        int    $adminId,
        string $action,
        string $target,
        string $details = ''
    ): void {
        $message = "AdminID: $adminId | Action: $action | Target: $target" . ($details ? " | $details" : '');
        self::writeToFile(LOG_AUDIT, 'ADMIN_ACTION', $message);
    }

    // ============================================================
    // Write to log file
    // ============================================================
    private static function writeToFile(string $filePath, string $event, string $message): void
    {
        $logDir = dirname($filePath);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = sprintf(
            "[%s] [%s] IP: %s | Session: %s | %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($event),
            getClientIP(),
            substr(session_id(), 0, 8) ?: 'NO_SESSION',
            $message
        );

        file_put_contents($filePath, $entry, FILE_APPEND | LOCK_EX);

        // Rotate log if > 10MB
        if (file_exists($filePath) && filesize($filePath) > 10485760) {
            self::rotateLog($filePath);
        }
    }

    // ============================================================
    // Log login to database (for admin monitoring)
    // ============================================================
    private static function logToDb(
        string $event,
        string $identifier,
        bool   $success
    ): void {
        try {
            $db = Database::getInstance();
            $db->execute(
                "INSERT INTO login_logs 
                 (event, identifier, success, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $event,
                    $identifier,
                    $success ? 1 : 0,
                    getClientIP(),
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                ]
            );
        } catch (Exception $e) {
            // Silently fail — don't break app if DB logging fails
        }
    }

    // ============================================================
    // Rotate log file when it gets too large
    // ============================================================
    private static function rotateLog(string $filePath): void
    {
        $rotated = $filePath . '.' . date('Y-m-d-His') . '.bak';
        rename($filePath, $rotated);
    }
}