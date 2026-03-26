<?php
/**
 * PriceHistory Model
 * Tracks every product price change for audit & reporting.
 */

class PriceHistory {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTable();
    }

    private function ensureTable() {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `price_history` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `old_price` DECIMAL(10,2) NOT NULL,
                `new_price` DECIMAL(10,2) NOT NULL,
                `changed_by` INT NULL,
                `reason` VARCHAR(500) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
                INDEX `idx_ph_product` (`product_id`),
                INDEX `idx_ph_date` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            // table may already exist
        }
    }

    public function record($productId, $oldPrice, $newPrice, $changedBy = null, $reason = null) {
        if (abs($oldPrice - $newPrice) < 0.01) return false; // no change
        $stmt = $this->pdo->prepare(
            "INSERT INTO price_history (product_id, old_price, new_price, changed_by, reason) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$productId, $oldPrice, $newPrice, $changedBy, $reason]);
    }

    public function getByProduct($productId, $limit = 20, $offset = 0) {
        $stmt = $this->pdo->prepare(
            "SELECT ph.*, u.first_name, u.last_name
             FROM price_history ph
             LEFT JOIN users u ON ph.changed_by = u.id
             WHERE ph.product_id = ?
             ORDER BY ph.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$productId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function countByProduct($productId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM price_history WHERE product_id = ?");
        $stmt->execute([$productId]);
        return (int) $stmt->fetchColumn();
    }

    public function getAll($limit = 50, $offset = 0) {
        $stmt = $this->pdo->prepare(
            "SELECT ph.*, p.name as product_name, p.sku, u.first_name, u.last_name
             FROM price_history ph
             JOIN products p ON ph.product_id = p.id
             LEFT JOIN users u ON ph.changed_by = u.id
             ORDER BY ph.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function count() {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM price_history")->fetchColumn();
    }
}
