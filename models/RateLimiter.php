<?php
/**
 * Rate Limiter – tracks and enforces login attempt limits
 * 
 * Limits: 5 failed attempts per IP within 15 minutes = 15-minute lockout
 */
class RateLimiter {
    private $pdo;

    // Max failed attempts before lockout
    const MAX_ATTEMPTS = 5;
    // Window in minutes to count attempts
    const WINDOW_MINUTES = 15;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTable();
    }

    /**
     * Create the login_attempts table if it doesn't exist
     */
    private function ensureTable() {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    email VARCHAR(255) DEFAULT NULL,
                    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ip_time (ip_address, attempted_at),
                    INDEX idx_email_time (email, attempted_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (PDOException $e) {
            // Silently fail – table may already exist or user lacks CREATE permission
            error_log('RateLimiter: Could not ensure table: ' . $e->getMessage());
        }
    }

    /**
     * Get the client IP address
     */
    public static function getClientIp() {
        // Only trust REMOTE_ADDR (server-set), not X-Forwarded-For (client-set, spoofable)
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt($email = null) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO login_attempts (ip_address, email, attempted_at) VALUES (?, ?, NOW())"
            );
            $stmt->execute([self::getClientIp(), $email]);
        } catch (PDOException $e) {
            error_log('RateLimiter: Could not record attempt: ' . $e->getMessage());
        }
    }

    /**
     * Check if the current IP is rate-limited
     * @return bool True if too many attempts (should block)
     */
    public function isLimited() {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts 
                 WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
            );
            $stmt->execute([self::getClientIp(), self::WINDOW_MINUTES]);
            return $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
        } catch (PDOException $e) {
            error_log('RateLimiter: Could not check limit: ' . $e->getMessage());
            return false; // fail open rather than locking out everyone
        }
    }

    /**
     * Get remaining attempts for the current IP
     */
    public function remainingAttempts() {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts 
                 WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
            );
            $stmt->execute([self::getClientIp(), self::WINDOW_MINUTES]);
            $used = $stmt->fetchColumn();
            return max(0, self::MAX_ATTEMPTS - $used);
        } catch (PDOException $e) {
            return self::MAX_ATTEMPTS;
        }
    }

    /**
     * Clear attempts for a specific IP (call after successful login)
     */
    public function clearAttempts() {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
            $stmt->execute([self::getClientIp()]);
        } catch (PDOException $e) {
            error_log('RateLimiter: Could not clear attempts: ' . $e->getMessage());
        }
    }

    /**
     * Purge old records (call periodically or via cron)
     */
    public function purgeOld() {
        try {
            $this->pdo->exec(
                "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)"
            );
        } catch (PDOException $e) {
            error_log('RateLimiter: Could not purge: ' . $e->getMessage());
        }
    }

    /**
     * Get minutes until lockout expires
     */
    public function getLockoutMinutesRemaining() {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT attempted_at FROM login_attempts 
                 WHERE ip_address = ? 
                 ORDER BY attempted_at ASC 
                 LIMIT 1 OFFSET " . (self::MAX_ATTEMPTS - 1)
            );
            $stmt->execute([self::getClientIp()]);
            $oldest = $stmt->fetchColumn();
            if ($oldest) {
                $expiry = strtotime($oldest) + (self::WINDOW_MINUTES * 60);
                $remaining = ceil(($expiry - time()) / 60);
                return max(1, $remaining);
            }
        } catch (PDOException $e) {
            // ignore
        }
        return self::WINDOW_MINUTES;
    }
}
