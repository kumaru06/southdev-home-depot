<?php
/**
 * DamagedProduct Model
 * Tracks products returned as damaged / broken from customer returns.
 */

class DamagedProduct {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTable();
    }

    private function ensureTable() {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `damaged_products` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `order_id` INT NOT NULL,
                `return_request_id` INT NOT NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `reason` TEXT NOT NULL,
                `status` ENUM('received','inspected','written_off','repaired') NOT NULL DEFAULT 'received',
                `admin_notes` TEXT NULL,
                `reported_by` INT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_dp_product` (`product_id`),
                INDEX `idx_dp_status` (`status`),
                INDEX `idx_dp_return` (`return_request_id`),
                INDEX `idx_dp_date` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            // table may already exist with different constraints
        }

        // Migrate old 'pending' status to 'received' if table existed before
        try {
            $this->pdo->exec("ALTER TABLE `damaged_products` MODIFY COLUMN `status` ENUM('received','inspected','written_off','repaired') NOT NULL DEFAULT 'received'");
            $this->pdo->exec("UPDATE `damaged_products` SET `status` = 'received' WHERE `status` = '' OR `status` IS NULL");
        } catch (\Throwable $e) {
            // ignore if already migrated
        }
    }

    /**
     * Create a damaged product record.
     */
    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO damaged_products (product_id, order_id, return_request_id, quantity, reason, status, reported_by)
             VALUES (?, ?, ?, ?, ?, 'received', ?)"
        );
        return $stmt->execute([
            $data['product_id'],
            $data['order_id'],
            $data['return_request_id'],
            $data['quantity'] ?? 1,
            $data['reason'],
            $data['reported_by'] ?? ($_SESSION['user_id'] ?? null)
        ]);
    }

    /**
     * Find a single record by ID.
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT dp.*, p.name AS product_name, p.sku, p.image, p.price,
                    o.order_number, rr.reason AS return_reason,
                    u.first_name, u.last_name
             FROM damaged_products dp
             JOIN products p ON dp.product_id = p.id
             JOIN orders o ON dp.order_id = o.id
             JOIN return_requests rr ON dp.return_request_id = rr.id
             LEFT JOIN users u ON dp.reported_by = u.id
             WHERE dp.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get all damaged product records with optional status filter.
     */
    public function getAll($status = null) {
        $sql = "SELECT dp.*, p.name AS product_name, p.sku, p.image, p.price,
                       o.order_number, rr.reason AS return_reason,
                       u.first_name, u.last_name
                FROM damaged_products dp
                JOIN products p ON dp.product_id = p.id
                JOIN orders o ON dp.order_id = o.id
                JOIN return_requests rr ON dp.return_request_id = rr.id
                LEFT JOIN users u ON dp.reported_by = u.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE dp.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY dp.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Update status and admin notes.
     */
    public function updateStatus($id, $status, $adminNotes = null) {
        $stmt = $this->pdo->prepare("UPDATE damaged_products SET status = ?, admin_notes = ? WHERE id = ?");
        return $stmt->execute([$status, $adminNotes, $id]);
    }

    /**
     * Count damaged products, optionally by status.
     */
    public function count($status = null) {
        $sql = "SELECT COUNT(*) FROM damaged_products";
        $params = [];
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get summary stats for dashboard.
     */
    public function getSummary() {
        $stmt = $this->pdo->query(
            "SELECT dp.status, COUNT(*) AS cnt, SUM(dp.quantity) AS total_qty
             FROM damaged_products dp
             GROUP BY dp.status"
        );
        $rows = $stmt->fetchAll();
        $summary = ['total' => 0, 'received' => 0, 'inspected' => 0, 'written_off' => 0, 'repaired' => 0, 'total_qty' => 0];
        foreach ($rows as $r) {
            $summary[$r['status']] = (int) $r['cnt'];
            $summary['total'] += (int) $r['cnt'];
            $summary['total_qty'] += (int) $r['total_qty'];
        }
        return $summary;
    }

    /**
     * Check if a return request already has a damaged product record.
     */
    public function existsForReturn($returnRequestId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM damaged_products WHERE return_request_id = ?");
        $stmt->execute([$returnRequestId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get recent damaged products for dashboard widget.
     */
    public function getRecent($limit = 5) {
        $stmt = $this->pdo->prepare(
            "SELECT dp.*, p.name AS product_name, p.sku, o.order_number
             FROM damaged_products dp
             JOIN products p ON dp.product_id = p.id
             JOIN orders o ON dp.order_id = o.id
             ORDER BY dp.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
