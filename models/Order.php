<?php
/**
 * SouthDev Home Depot – Order Model
 * Includes order cancellation with stock restoration
 */

class Order {
    private $pdo;
    private $supportsCancelReason = null;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function detectCancelReasonSupport() {
        if ($this->supportsCancelReason !== null) {
            return $this->supportsCancelReason;
        }
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM orders LIKE 'cancel_reason'");
            $this->supportsCancelReason = (bool) $stmt->fetch();
        } catch (Exception $e) {
            $this->supportsCancelReason = false;
        }
        return $this->supportsCancelReason;
    }

    private function ensureCancelReasonColumn() {
        if ($this->detectCancelReasonSupport()) return true;
        try {
            $this->pdo->exec("ALTER TABLE orders ADD COLUMN cancel_reason TEXT NULL");
            $this->supportsCancelReason = true;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT o.*, u.first_name, u.last_name, u.email, u.phone
             FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAll($status = null) {
        $sql = "SELECT o.*, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id = u.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $orderNumber = 'SHD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        $stmt = $this->pdo->prepare(
            "INSERT INTO orders (user_id, order_number, total_amount, shipping_address, shipping_city, shipping_state, shipping_zip, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['user_id'], $orderNumber, $data['total_amount'],
            $data['shipping_address'], $data['shipping_city'] ?? null,
            $data['shipping_state'] ?? null, $data['shipping_zip'] ?? null,
            $data['notes'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function addItem($orderId, $productId, $quantity, $price) {
        $subtotal = $quantity * $price;
        $stmt = $this->pdo->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$orderId, $productId, $quantity, $price, $subtotal]);
    }

    public function getItems($orderId) {
        $stmt = $this->pdo->prepare(
            "SELECT oi.*, p.name as product_name, p.sku, p.image
             FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Cancel order and restore stock for each item.
     * Uses a transaction to ensure atomicity.
     */
    public function cancelOrder($id, $userId = null, $reason = null) {
        $reason = is_string($reason) ? trim($reason) : null;
        if ($reason === '') $reason = null;

        // Best-effort: ensure schema supports saving cancel reasons.
        if ($reason !== null && !$this->detectCancelReasonSupport()) {
            if (!$this->ensureCancelReasonColumn()) {
                $reason = null;
            }
        }

        $this->pdo->beginTransaction();
        try {
            /* Verify order is cancellable */
            $sql = "SELECT id, status FROM orders WHERE id = ? AND status IN ('pending', 'processing')";
            $params = [$id];
            if ($userId) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $order = $stmt->fetch();

            if (!$order) {
                $this->pdo->rollBack();
                return false;
            }

            /* Restore stock for each order item */
            $items = $this->getItems($id);
            foreach ($items as $item) {
                $restore = $this->pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE product_id = ?");
                $restore->execute([$item['quantity'], $item['product_id']]);
            }

            /* Mark order as cancelled */
            if ($reason !== null) {
                $upd = $this->pdo->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = ? WHERE id = ?");
                $upd->execute([$reason, $id]);
            } else {
                $upd = $this->pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
                $upd->execute([$id]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function updateAddress($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE orders SET shipping_address = ?, shipping_city = ?, shipping_state = ?, shipping_zip = ?
             WHERE id = ? AND status = 'pending'"
        );
        return $stmt->execute([
            $data['shipping_address'], $data['shipping_city'],
            $data['shipping_state'], $data['shipping_zip'], $id
        ]);
    }

    public function getByOrderNumber($orderNumber) {
        $stmt = $this->pdo->prepare(
            "SELECT o.*, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.order_number = ?"
        );
        $stmt->execute([$orderNumber]);
        return $stmt->fetch();
    }

    public function getTotalSales($startDate = null, $endDate = null) {
        $sql = "SELECT SUM(total_amount) FROM orders WHERE status NOT IN ('cancelled')";
        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND created_at BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() ?? 0;
    }

    public function countByStatus($status = null) {
        $sql = "SELECT COUNT(*) FROM orders";
        $params = [];
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getRecentOrders($limit = 10) {
        $stmt = $this->pdo->prepare(
            "SELECT o.*, u.first_name, u.last_name FROM orders o
             JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
