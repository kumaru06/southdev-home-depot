<?php
/**
 * Review Model
 */
class Review {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    /**
     * Ensure the `reviews` table exists. Creates it if missing.
     */
    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                order_id INT NULL,
                order_item_id INT NULL,
                user_id INT NOT NULL,
                rating TINYINT NOT NULL,
                comment TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (product_id),
                INDEX (user_id),
                INDEX (order_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->pdo->exec($sql);
        } catch (Exception $e) {
            // Best-effort: if creation fails, we'll allow higher-level code to handle missing table.
        }
    }

    public function create($data) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO reviews (product_id, order_id, order_item_id, user_id, rating, comment)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            return (bool) $stmt->execute([
                $data['product_id'],
                $data['order_id'] ?? null,
                $data['order_item_id'] ?? null,
                $data['user_id'],
                $data['rating'],
                $data['comment'] ?? null
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getByProductId($productId, $limit = 50) {
        try {
            $stmt = $this->pdo->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC LIMIT ?");
            $stmt->execute([$productId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function hasExisting($userId, $productId, $orderItemId = null) {
        try {
            if ($orderItemId) {
                $stmt = $this->pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? AND order_item_id = ? LIMIT 1");
                $stmt->execute([$userId, $productId, $orderItemId]);
                return (bool) $stmt->fetch();
            }
            $stmt = $this->pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
            $stmt->execute([$userId, $productId]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAll($limit = 200) {
        try {
            $sql = "SELECT r.*, p.name AS product_name, u.first_name, u.last_name
                    FROM reviews r
                    LEFT JOIN products p ON r.product_id = p.id
                    LEFT JOIN users u ON r.user_id = u.id
                    ORDER BY r.created_at DESC";
            if ($limit) $sql .= " LIMIT " . intval($limit);
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function deleteById($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM reviews WHERE id = ?");
            return (bool) $stmt->execute([$id]);
        } catch (Exception $e) {
            return false;
        }
    }
}
