<?php
/**
 * ReturnRequest Model
 */

class ReturnRequest {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT rr.*, o.order_number, u.first_name, u.last_name FROM return_requests rr JOIN orders o ON rr.order_id = o.id JOIN users u ON rr.user_id = u.id WHERE rr.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT rr.*, o.order_number FROM return_requests rr JOIN orders o ON rr.order_id = o.id WHERE rr.user_id = ? ORDER BY rr.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAll($status = null) {
        $sql = "SELECT rr.*, o.order_number, u.first_name, u.last_name FROM return_requests rr JOIN orders o ON rr.order_id = o.id JOIN users u ON rr.user_id = u.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE rr.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY rr.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO return_requests (order_id, user_id, reason) VALUES (?, ?, ?)");
        return $stmt->execute([$data['order_id'], $data['user_id'], $data['reason']]);
    }

    public function updateStatus($id, $status, $adminNotes = null) {
        $stmt = $this->pdo->prepare("UPDATE return_requests SET status = ?, admin_notes = ? WHERE id = ?");
        return $stmt->execute([$status, $adminNotes, $id]);
    }

    public function hasExistingRequest($orderId) {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM return_requests rr
             JOIN orders o ON rr.order_id = o.id
             WHERE rr.order_id = ?
               AND rr.status IN ('pending','approved','completed')
               AND rr.created_at >= o.created_at"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get the latest return request for a specific order.
     * Validates that the return was created after the order (prevents orphaned records).
     */
    public function getByOrderId($orderId) {
        $stmt = $this->pdo->prepare(
            "SELECT rr.* FROM return_requests rr
             JOIN orders o ON rr.order_id = o.id AND rr.user_id = o.user_id
             WHERE rr.order_id = ?
               AND rr.status IN ('pending','approved','completed')
               AND rr.created_at >= o.created_at
             ORDER BY rr.created_at DESC LIMIT 1"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    /**
     * Get return requests indexed by order_id for a list of order IDs.
     * Joins with orders to ensure the return request belongs to the same user
     * and was created after the order itself (prevents orphaned records).
     */
    public function getByOrderIds(array $orderIds, $userId = null) {
        if (empty($orderIds)) return [];
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $sql = "SELECT rr.* FROM return_requests rr
                JOIN orders o ON rr.order_id = o.id AND rr.user_id = o.user_id
                WHERE rr.order_id IN ($placeholders)
                  AND rr.status IN ('pending','approved','completed')
                  AND rr.created_at >= o.created_at";
        $params = $orderIds;
        if ($userId) {
            $sql .= " AND rr.user_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY rr.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = [];
        while ($row = $stmt->fetch()) {
            if (!isset($results[$row['order_id']])) {
                $results[$row['order_id']] = $row;
            }
        }
        return $results;
    }
}
