<?php
/**
 * SouthDev Home Depot – System Settings Model
 * Key-value store for SuperAdmin-configurable options.
 */

class Setting {
    private $pdo;
    private static $cache = [];
    private static $booted = false;
    private static $tableReady = false;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Ensure table exists (safe for local + production without manual migrate).
     * Only runs DDL when missing — CREATE/ALTER causes MySQL implicit COMMIT
     * and would break any open PDO transaction.
     */
    public function ensureTable(): void {
        if (self::$tableReady) {
            return;
        }
        try {
            $this->pdo->query('SELECT 1 FROM system_settings LIMIT 1');
            self::$tableReady = true;
            return;
        } catch (PDOException $e) {
            // Table missing — create it
        }
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS `system_settings` (
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text NULL,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        self::$tableReady = true;
    }

    public function boot(): void {
        if (self::$booted) {
            return;
        }
        $this->ensureTable();
        try {
            $stmt = $this->pdo->query('SELECT setting_key, setting_value FROM system_settings');
            self::$cache = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            self::$cache = [];
        }
        self::$booted = true;
    }

    public function get(string $key, $default = null) {
        $this->boot();
        return array_key_exists($key, self::$cache) ? self::$cache[$key] : $default;
    }

    public function set(string $key, $value): bool {
        $this->ensureTable();
        $stmt = $this->pdo->prepare(
            'INSERT INTO system_settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $ok = $stmt->execute([$key, (string) $value]);
        if ($ok) {
            self::$cache[$key] = (string) $value;
            self::$booted = true;
        }
        return $ok;
    }

    public function setMany(array $pairs): void {
        foreach ($pairs as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getAll(): array {
        $this->boot();
        return self::$cache;
    }

    /** Default payment toggles (all on). */
    public static function defaultPaymentFlags(): array {
        return [
            'cod'   => true,
            'gcash' => true,
            'card'  => true,
            'qrph'  => true,
        ];
    }

    public function isPaymentEnabled(string $method): bool {
        $method = strtolower(trim($method));
        $defaults = self::defaultPaymentFlags();
        if (!array_key_exists($method, $defaults)) {
            return false;
        }
        $raw = $this->get('payment_' . $method . '_enabled', $defaults[$method] ? '1' : '0');
        return $raw === '1' || $raw === 1 || $raw === true || $raw === 'true';
    }

    public function getEnabledPayments(): array {
        $enabled = [];
        foreach (array_keys(self::defaultPaymentFlags()) as $method) {
            if ($this->isPaymentEnabled($method)) {
                $enabled[] = $method;
            }
        }
        return $enabled;
    }

    public function getGeneral(): array {
        return [
            'items_per_page' => $this->itemsPerPage(),
        ];
    }

    public function itemsPerPage(): int {
        $n = (int) $this->get('items_per_page', defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 12);
        return max(5, min(100, $n ?: 12));
    }
}
