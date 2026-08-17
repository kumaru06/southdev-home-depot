<?php
/**
 * Cart Model
 */

class Cart {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT c.*, p.name as product_name, p.image,
                    COALESCE(s.price, p.price) as price,
                    s.size_label,
                    s.id as size_option_id,
                    COALESCE(s.stock, i.quantity) as stock
             FROM cart c
             JOIN products p ON c.product_id = p.id
             LEFT JOIN product_size_options s ON c.size_option_id = s.id
             LEFT JOIN inventory i ON p.id = i.product_id
             WHERE c.user_id = ?
             ORDER BY c.created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function addItem($userId, $productId, $quantity = 1, $sizeOptionId = null) {
        $sizeOptionId = $sizeOptionId ? (int) $sizeOptionId : null;

        if ($sizeOptionId) {
            $stmt = $this->pdo->prepare(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size_option_id = ?"
            );
            $stmt->execute([$userId, $productId, $sizeOptionId]);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size_option_id IS NULL"
            );
            $stmt->execute([$userId, $productId]);
        }
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $this->pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
            return $stmt->execute([$quantity, $existing['id']]);
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO cart (user_id, product_id, size_option_id, quantity) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$userId, $productId, $sizeOptionId, $quantity]);
    }

    public function getItemByUserAndProduct($userId, $productId, $sizeOptionId = null) {
        if ($sizeOptionId) {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size_option_id = ? LIMIT 1"
            );
            $stmt->execute([$userId, $productId, (int) $sizeOptionId]);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size_option_id IS NULL LIMIT 1"
            );
            $stmt->execute([$userId, $productId]);
        }
        return $stmt->fetch();
    }

    public function updateQuantity($cartId, $quantity, $userId = null) {
        if ($userId) {
            $stmt = $this->pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            return $stmt->execute([$quantity, $cartId, $userId]);
        }
        $stmt = $this->pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        return $stmt->execute([$quantity, $cartId]);
    }

    public function removeItem($cartId, $userId = null) {
        if ($userId) {
            $stmt = $this->pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            return $stmt->execute([$cartId, $userId]);
        }
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE id = ?");
        return $stmt->execute([$cartId]);
    }

    public function getItemById($cartId, $userId = null) {
        $sql = "SELECT c.*, p.name as product_name, p.image,
                       COALESCE(s.price, p.price) as price,
                       s.size_label,
                       COALESCE(s.stock, i.quantity) as stock
                FROM cart c
                JOIN products p ON c.product_id = p.id
                LEFT JOIN product_size_options s ON c.size_option_id = s.id
                LEFT JOIN inventory i ON i.product_id = p.id
                WHERE c.id = ?";
        $params = [$cartId];

        if ($userId !== null) {
            $sql .= " AND c.user_id = ?";
            $params[] = $userId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function clearCart($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function getCartTotal($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT SUM(c.quantity * COALESCE(s.price, p.price)) as total
             FROM cart c
             JOIN products p ON c.product_id = p.id
             LEFT JOIN product_size_options s ON c.size_option_id = s.id
             WHERE c.user_id = ?"
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getCartCount($userId) {
        $stmt = $this->pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?? 0;
    }
}
