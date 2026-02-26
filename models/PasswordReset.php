<?php
/**
 * PasswordReset Model
 * Simple table to store password reset tokens for email addresses.
 */

class PasswordReset {
    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
        } else {
            global $pdo;
            $this->pdo = $pdo;
        }
        // Ensure the password_resets table exists to avoid runtime errors
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(128) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_unique` (`token`),
  KEY `email_idx` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->pdo->exec($sql);
        } catch (\Throwable $e) {
            // If table creation fails, log and continue — the caller will get a clear exception.
            if (class_exists('Log')) {
                try {
                    $log = new Log($this->pdo);
                    $log->create(LOG_SYSTEM_ERROR, 'Failed to ensure password_resets table: ' . $e->getMessage());
                } catch (\Throwable $inner) {
                    // ignore logging failures
                }
            }
        }
    }

    public function create($email, $token, $expiresAt) {
        $stmt = $this->pdo->prepare('INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (:email, :token, :expires_at, NOW())');
        return $stmt->execute([
            ':email' => $email,
            ':token' => $token,
            ':expires_at' => $expiresAt,
        ]);
    }

    public function getByToken($token) {
        $stmt = $this->pdo->prepare('SELECT * FROM password_resets WHERE token = :token LIMIT 1');
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteByEmail($email) {
        $stmt = $this->pdo->prepare('DELETE FROM password_resets WHERE email = :email');
        return $stmt->execute([':email' => $email]);
    }

    public function deleteByToken($token) {
        $stmt = $this->pdo->prepare('DELETE FROM password_resets WHERE token = :token');
        return $stmt->execute([':token' => $token]);
    }
}
