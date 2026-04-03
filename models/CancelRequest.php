<?php
/**
 * SouthDev Home Depot – Cancel Request Model
 * Manages customer-initiated order cancellation requests
 */

class CancelRequest {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT cr.*, o.order_number, o.total_amount, o.status as order_status,
                    u.first_name, u.last_name, u.email
             FROM cancel_requests cr
             JOIN orders o ON cr.order_id = o.id
             JOIN users u ON cr.user_id = u.id
             WHERE cr.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT cr.*, o.order_number, o.total_amount
             FROM cancel_requests cr
             JOIN orders o ON cr.order_id = o.id
             WHERE cr.user_id = ?
             ORDER BY cr.created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAll($status = null) {
        $sql = "SELECT cr.*, o.order_number, o.total_amount, o.status as order_status,
                       u.first_name, u.last_name
                FROM cancel_requests cr
                JOIN orders o ON cr.order_id = o.id
                JOIN users u ON cr.user_id = u.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE cr.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY cr.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO cancel_requests (order_id, user_id, reason) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$data['order_id'], $data['user_id'], $data['reason']]);
    }

    public function approve($id, $adminNotes = null) {
        $stmt = $this->pdo->prepare(
            "UPDATE cancel_requests SET status = 'approved', admin_notes = ? WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$adminNotes, $id]);
        return $stmt->rowCount() > 0;
    }

    public function reject($id, $adminNotes = null) {
        $stmt = $this->pdo->prepare(
            "UPDATE cancel_requests SET status = 'rejected', admin_notes = ? WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$adminNotes, $id]);
        return $stmt->rowCount() > 0;
    }

    public function getByOrderId($orderId) {
        $stmt = $this->pdo->prepare(
            "SELECT cr.*, u.first_name as staff_first_name, u.last_name as staff_last_name
             FROM cancel_requests cr
             LEFT JOIN users u ON u.id = (SELECT user_id FROM logs WHERE action LIKE '%cancel%' AND description LIKE CONCAT('%#', cr.id, '%') LIMIT 1)
             WHERE cr.order_id = ?
             ORDER BY cr.created_at DESC LIMIT 1"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function hasExistingRequest($orderId) {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM cancel_requests WHERE order_id = ? AND status = 'pending'"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchColumn() > 0;
    }

    public function countPending() {
        return $this->pdo->query("SELECT COUNT(*) FROM cancel_requests WHERE status = 'pending'")->fetchColumn();
    }
}
