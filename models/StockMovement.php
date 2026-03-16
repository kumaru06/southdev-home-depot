<?php
/**
 * StockMovement Model
 * Tracks every inventory change: purchase, sale, return, adjustment.
 */

class StockMovement {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTable();
    }

    private function ensureTable() {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `stock_movements` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `type` ENUM('purchase','sale','return','adjustment','initial') NOT NULL,
                `quantity` INT NOT NULL,
                `reference_id` INT NULL,
                `notes` VARCHAR(500) NULL,
                `performed_by` INT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
                INDEX `idx_sm_product` (`product_id`),
                INDEX `idx_sm_type` (`type`),
                INDEX `idx_sm_date` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            // table may already exist
        }
    }

    /**
     * Record a stock movement.
     * @param int    $productId
     * @param string $type       purchase|sale|return|adjustment|initial
     * @param int    $quantity   positive = in, negative = out
     * @param int|null $referenceId  order_id or return_request_id
     * @param string|null $notes
     * @param int|null $performedBy  user_id
     */
    public function record($productId, $type, $quantity, $referenceId = null, $notes = null, $performedBy = null) {
        $performedBy = $performedBy ?? ($_SESSION['user_id'] ?? null);
        $stmt = $this->pdo->prepare(
            "INSERT INTO stock_movements (product_id, type, quantity, reference_id, notes, performed_by)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$productId, $type, $quantity, $referenceId, $notes, $performedBy]);
    }

    public function getByProduct($productId, $limit = 50) {
        $stmt = $this->pdo->prepare(
            "SELECT sm.*, p.name as product_name, p.sku, u.first_name, u.last_name
             FROM stock_movements sm
             JOIN products p ON sm.product_id = p.id
             LEFT JOIN users u ON sm.performed_by = u.id
             WHERE sm.product_id = ?
             ORDER BY sm.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$productId, $limit]);
        return $stmt->fetchAll();
    }

    public function getAll($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT sm.*, p.name as product_name, p.sku, u.first_name, u.last_name
                FROM stock_movements sm
                JOIN products p ON sm.product_id = p.id
                LEFT JOIN users u ON sm.performed_by = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND sm.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['product_id'])) {
            $sql .= " AND sm.product_id = ?";
            $params[] = $filters['product_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND sm.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND sm.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= " ORDER BY sm.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count($filters = []) {
        $sql = "SELECT COUNT(*) FROM stock_movements sm WHERE 1=1";
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND sm.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['product_id'])) {
            $sql .= " AND sm.product_id = ?";
            $params[] = $filters['product_id'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Summary: total stock added by type for a date range
     */
    public function getSummary($dateFrom = null, $dateTo = null) {
        $sql = "SELECT sm.type,
                       SUM(CASE WHEN sm.quantity > 0 THEN sm.quantity ELSE 0 END) as total_in,
                       SUM(CASE WHEN sm.quantity < 0 THEN ABS(sm.quantity) ELSE 0 END) as total_out,
                       COUNT(*) as movement_count
                FROM stock_movements sm WHERE 1=1";
        $params = [];
        if ($dateFrom) { $sql .= " AND sm.created_at >= ?"; $params[] = $dateFrom . ' 00:00:00'; }
        if ($dateTo)   { $sql .= " AND sm.created_at <= ?"; $params[] = $dateTo . ' 23:59:59'; }
        $sql .= " GROUP BY sm.type ORDER BY sm.type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
