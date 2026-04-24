<?php
/**
 * Rate Limiter – tracks and enforces login attempt limits.
 *
 * Lockout is per (IP address + email/username + login_type) so that:
 *  - Typing the wrong password for YOUR account only blocks YOUR account from that IP.
 *  - Other customers on the same network (same public IP) are completely unaffected.
 *
 * Limits: 5 failed attempts within 15 minutes → 15-minute lockout for that IP+email combo.
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
     * Create / migrate the login_attempts table if needed.
     */
    private function ensureTable() {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    email VARCHAR(255) DEFAULT NULL,
                    login_type VARCHAR(20) NOT NULL DEFAULT 'customer',
                    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ip_email_type_time (ip_address, email, login_type, attempted_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (PDOException $e) {
            error_log('RateLimiter: Could not ensure table: ' . $e->getMessage());
        }
        // Add login_type column to tables created before this version
        try {
            $cols = $this->pdo->query("SHOW COLUMNS FROM login_attempts LIKE 'login_type'")->fetchAll();
            if (empty($cols)) {
                $this->pdo->exec("ALTER TABLE login_attempts ADD COLUMN login_type VARCHAR(20) NOT NULL DEFAULT 'customer'");
            }
        } catch (PDOException $e) {
            error_log('RateLimiter: Could not migrate table: ' . $e->getMessage());
        }
    }

    /**
     * Get the client IP address.
     */
    public static function getClientIp() {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Record a failed login attempt for this IP + email + type.
     *
     * @param string $email     The email or username that was attempted (required).
     * @param string $loginType 'customer' or 'admin'
     */
    public function recordFailedAttempt($email, $loginType = 'customer') {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO login_attempts (ip_address, email, login_type, attempted_at) VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([self::getClientIp(), strtolower(trim($email)), $loginType]);
        } catch (PDOException $e) {
            error_log('RateLimiter: Could not record attempt: ' . $e->getMessage());
        }
    }

    /**
     * Check if this IP + email combo is currently locked out.
     * Other customers (different emails) on the same IP are NOT affected.
     *
     * @param string $email     The email or username being attempted.
     * @param string $loginType 'customer' or 'admin'
     * @return bool  True = blocked, False = allow through
     */
    public function isLimited($email, $loginType = 'customer') {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts
                 WHERE ip_address = ? AND email = ? AND login_type = ?
                   AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
            );
            $stmt->execute([self::getClientIp(), strtolower(trim($email)), $loginType, self::WINDOW_MINUTES]);
            return $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
        } catch (PDOException $e) {
            error_log('RateLimiter: Could not check limit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * How many attempts remain before lockout for this IP + email.
     *
     * @param string $email
     * @param string $loginType
     */
    public function remainingAttempts($email, $loginType = 'customer') {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts
                 WHERE ip_address = ? AND email = ? AND login_type = ?
                   AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
            );
            $stmt->execute([self::getClientIp(), strtolower(trim($email)), $loginType, self::WINDOW_MINUTES]);
            $used = $stmt->fetchColumn();
            return max(0, self::MAX_ATTEMPTS - $used);
        } catch (PDOException $e) {
            return self::MAX_ATTEMPTS;
        }
    }

    /**
     * Clear failed attempts for this IP + email on successful login.
     *
     * @param string $email
     * @param string $loginType
     */
    public function clearAttempts($email, $loginType = 'customer') {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM login_attempts WHERE ip_address = ? AND email = ? AND login_type = ?"
            );
            $stmt->execute([self::getClientIp(), strtolower(trim($email)), $loginType]);
        } catch (PDOException $e) {
            error_log('RateLimiter: Could not clear attempts: ' . $e->getMessage());
        }
    }

    /**
     * Purge records older than 1 day (call via cron or on login).
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
     * Minutes remaining until the lockout expires for this IP + email.
     *
     * @param string $email
     * @param string $loginType
     */
    public function getLockoutMinutesRemaining($email, $loginType = 'customer') {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT attempted_at FROM login_attempts
                 WHERE ip_address = ? AND email = ? AND login_type = ?
                 ORDER BY attempted_at ASC
                 LIMIT 1 OFFSET " . (self::MAX_ATTEMPTS - 1)
            );
            $stmt->execute([self::getClientIp(), strtolower(trim($email)), $loginType]);
            $oldest = $stmt->fetchColumn();
            if ($oldest) {
                $expiry    = strtotime($oldest) + (self::WINDOW_MINUTES * 60);
                $remaining = ceil(($expiry - time()) / 60);
                return max(1, $remaining);
            }
        } catch (PDOException $e) {
            // ignore
        }
        return self::WINDOW_MINUTES;
    }
}
