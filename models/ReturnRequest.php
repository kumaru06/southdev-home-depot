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
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM return_requests WHERE order_id = ? AND status NOT IN ('rejected')");
        $stmt->execute([$orderId]);
        return $stmt->fetchColumn() > 0;
    }
}
