<?php
/**
 * Payment Model
 */

class Payment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByOrderId($orderId) {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function getBySourceId($sourceId) {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE source_id = ?");
        $stmt->execute([$sourceId]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_id, amount, status) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['order_id'], $data['payment_method'],
            $data['transaction_id'] ?? null, $data['amount'],
            $data['status'] ?? PAYMENT_PENDING
        ]);
    }

    public function createWithSource($data) {
        $stmt = $this->pdo->prepare("INSERT INTO payments (order_id, payment_method, source_id, client_key, amount, status) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['order_id'], $data['payment_method'],
            $data['source_id'] ?? null,
            $data['client_key'] ?? null,
            $data['amount'],
            $data['status'] ?? PAYMENT_PENDING
        ]);
    }

    public function updateSourceId($id, $sourceId) {
        $stmt = $this->pdo->prepare("UPDATE payments SET source_id = ? WHERE id = ?");
        return $stmt->execute([$sourceId, $id]);
    }

    public function updateSourceAndClientKey($id, $sourceId, $clientKey) {
        $stmt = $this->pdo->prepare("UPDATE payments SET source_id = ?, client_key = ? WHERE id = ?");
        return $stmt->execute([$sourceId, $clientKey, $id]);
    }

    public function updateStatus($id, $status, $transactionId = null) {
        $sql = "UPDATE payments SET status = ?";
        $params = [$status];
        if ($transactionId) {
            $sql .= ", transaction_id = ?";
            $params[] = $transactionId;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
