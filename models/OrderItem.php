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
        $stmt = $this->pdo->prepare(
            "SELECT oi.id, oi.order_id, oi.product_id, oi.size_option_id, oi.quantity, oi.price, oi.subtotal,
                    p.name as product_name, p.image,
                    COALESCE(NULLIF(TRIM(oi.size_label), ''), s.size_label) as size_label
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             LEFT JOIN product_size_options s ON oi.size_option_id = s.id
             WHERE oi.order_id = ?"
        );
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

    public function getSoldCountsByProductIds(array $productIds) {
        if (empty($productIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT oi.product_id, SUM(oi.quantity) AS total_sold
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE oi.product_id IN ($placeholders)
               AND o.status IN ('processing', 'shipped', 'delivered')
             GROUP BY oi.product_id"
        );
        $stmt->execute(array_values($productIds));
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int) $row['product_id']] = (int) $row['total_sold'];
        }
        return $result;
    }
}
