<?php
/**
 * ReturnRequest Model
 */

class ReturnRequest {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureSelectedItemsColumn();
    }

    private function ensureSelectedItemsColumn() {
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM return_requests LIKE 'selected_items'");
            if (!$stmt->fetch()) {
                $this->pdo->exec("ALTER TABLE return_requests ADD COLUMN selected_items TEXT NULL AFTER reason");
            }
        } catch (Throwable $e) {
            // Keep existing return flow working if schema check fails.
        }
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
        $selectedItems = $data['selected_items'] ?? null;
        if (is_array($selectedItems)) {
            $selectedItems = json_encode(array_values($selectedItems));
        }

        $stmt = $this->pdo->prepare("INSERT INTO return_requests (order_id, user_id, reason, selected_items) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['order_id'], $data['user_id'], $data['reason'], $selectedItems]);
    }

    public function getSelectedItemIds($returnRequest) {
        if (empty($returnRequest['selected_items'])) {
            return [];
        }

        $decoded = json_decode($returnRequest['selected_items'], true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $decoded)));
    }

    public function getSelectedItemsSummary($returnRequest) {
        $itemIds = $this->getSelectedItemIds($returnRequest);
        if (empty($itemIds)) {
            return 'All order items';
        }

        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $params = array_merge([(int) $returnRequest['order_id']], $itemIds);
        $stmt = $this->pdo->prepare(
            "SELECT p.name, oi.quantity
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ? AND oi.id IN ($placeholders)
             ORDER BY oi.id ASC"
        );
        $stmt->execute($params);

        $items = $stmt->fetchAll();
        if (!$items) {
            return 'Selected items unavailable';
        }

        return implode(', ', array_map(function ($item) {
            return $item['name'] . ' (qty: ' . (int) $item['quantity'] . ')';
        }, $items));
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
