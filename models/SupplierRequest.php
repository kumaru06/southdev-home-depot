<?php
/**
 * SupplierRequest Model
 * Tracks restock requests from inventory → ordered → received/cancelled
 */

class SupplierRequest {
    private $pdo;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_ORDERED   = 'ordered';
    public const STATUS_RECEIVED  = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT sr.*, p.name AS product_name, p.sku, p.image,
                    i.quantity AS current_stock,
                    u.first_name, u.last_name
             FROM supplier_requests sr
             JOIN products p ON sr.product_id = p.id
             LEFT JOIN inventory i ON i.product_id = p.id
             LEFT JOIN users u ON sr.requested_by = u.id
             WHERE sr.id = ?"
        );
        $stmt->execute([(int) $id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll($status = null) {
        $sql = "SELECT sr.*, p.name AS product_name, p.sku, p.image,
                       i.quantity AS current_stock,
                       u.first_name, u.last_name
                FROM supplier_requests sr
                JOIN products p ON sr.product_id = p.id
                LEFT JOIN inventory i ON i.product_id = p.id
                LEFT JOIN users u ON sr.requested_by = u.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE sr.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY
                    FIELD(sr.status, 'pending', 'ordered', 'received', 'cancelled'),
                    sr.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create($productId, $quantity, $notes, $requestedBy) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO supplier_requests (product_id, requested_quantity, status, notes, requested_by, created_at)
             VALUES (?, ?, 'pending', ?, ?, NOW())"
        );
        $ok = $stmt->execute([
            (int) $productId,
            (int) $quantity,
            $notes !== '' ? $notes : null,
            $requestedBy ? (int) $requestedBy : null,
        ]);
        return $ok ? (int) $this->pdo->lastInsertId() : false;
    }

    public function hasOpenRequest($productId) {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM supplier_requests
             WHERE product_id = ? AND status IN ('pending', 'ordered')
             LIMIT 1"
        );
        $stmt->execute([(int) $productId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getOpenRequestId($productId) {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM supplier_requests
             WHERE product_id = ? AND status IN ('pending', 'ordered')
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute([(int) $productId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function updateStatus($id, $status) {
        $allowed = ['pending', 'ordered', 'received', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            "UPDATE supplier_requests SET status = ? WHERE id = ?"
        );
        return $stmt->execute([$status, (int) $id]);
    }

    public function countByStatus($status = null) {
        if ($status) {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM supplier_requests sr
                 JOIN products p ON sr.product_id = p.id
                 WHERE sr.status = ?"
            );
            $stmt->execute([$status]);
            return (int) $stmt->fetchColumn();
        }
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM supplier_requests sr JOIN products p ON sr.product_id = p.id"
        )->fetchColumn();
    }

    public function countPending() {
        return $this->countByStatus(self::STATUS_PENDING);
    }

    public function getSummary() {
        $rows = $this->pdo->query(
            "SELECT sr.status, COUNT(*) AS c
             FROM supplier_requests sr
             JOIN products p ON sr.product_id = p.id
             GROUP BY sr.status"
        )->fetchAll();
        $summary = [
            'total'     => 0,
            'pending'   => 0,
            'ordered'   => 0,
            'received'  => 0,
            'cancelled' => 0,
        ];
        foreach ($rows as $row) {
            $key = $row['status'];
            $count = (int) $row['c'];
            if (isset($summary[$key])) {
                $summary[$key] = $count;
            }
            $summary['total'] += $count;
        }
        return $summary;
    }
}
