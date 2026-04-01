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

    /**
     * Get all reviews by a specific user.
     */
    public function getByUserId($userId, $limit = 100) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT r.*, p.name AS product_name, p.image AS product_image
                 FROM reviews r
                 LEFT JOIN products p ON r.product_id = p.id
                 WHERE r.user_id = ?
                 ORDER BY r.created_at DESC
                 LIMIT ?"
            );
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get average rating and review count for a list of product IDs.
     * Returns assoc array keyed by product_id => ['avg_rating' => float, 'review_count' => int]
     */
    public function getAvgRatingsByProductIds(array $productIds) {
        if (empty($productIds)) return [];
        try {
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $stmt = $this->pdo->prepare(
                "SELECT product_id, ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS review_count
                 FROM reviews
                 WHERE product_id IN ($placeholders)
                 GROUP BY product_id"
            );
            $stmt->execute(array_values($productIds));
            $rows = $stmt->fetchAll();
            $result = [];
            foreach ($rows as $row) {
                $result[(int)$row['product_id']] = [
                    'avg_rating'   => (float)$row['avg_rating'],
                    'review_count' => (int)$row['review_count']
                ];
            }
            return $result;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get IDs of order items that the user has already reviewed for a given order.
     */
    public function getReviewedOrderItemIds($userId, $orderId) {
        try {
            $stmt = $this->pdo->prepare("SELECT order_item_id FROM reviews WHERE user_id = ? AND order_id = ? AND order_item_id IS NOT NULL");
            $stmt->execute([$userId, $orderId]);
            return array_column($stmt->fetchAll(), 'order_item_id');
        } catch (Exception $e) {
            return [];
        }
    }
}
