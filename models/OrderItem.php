<?php
/**
 * OrderItem Model
 */

class OrderItem {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getByOrderId($orderId) {
        $stmt = $this->pdo->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['order_id'], $data['product_id'],
            $data['quantity'], $data['price'],
            $data['quantity'] * $data['price']
        ]);
    }

    public function getTopProducts($limit = 10) {
        $stmt = $this->pdo->prepare("SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id WHERE o.status != 'cancelled' GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
