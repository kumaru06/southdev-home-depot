<?php
/**
 * Inventory Model
 */

class Inventory {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getByProductId($productId) {
        $stmt = $this->pdo->prepare("SELECT i.*, p.name as product_name, p.sku FROM inventory i JOIN products p ON i.product_id = p.id WHERE i.product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    public function getAll() {
        // Include product cost if present (nullable column). Cost may be NULL if not populated.
        return $this->pdo->query("SELECT i.*, p.name as product_name, p.sku, p.price, p.cost FROM inventory i JOIN products p ON i.product_id = p.id WHERE p.is_active = 1 ORDER BY p.name")->fetchAll();
    }

    public function updateQuantity($productId, $quantity) {
        $stmt = $this->pdo->prepare("INSERT INTO inventory (product_id, quantity) VALUES (?, ?) ON DUPLICATE KEY UPDATE quantity = ?");
        return $stmt->execute([$productId, $quantity, $quantity]);
    }

    public function adjustQuantity($productId, $adjustment) {
        $stmt = $this->pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE product_id = ?");
        return $stmt->execute([$adjustment, $productId]);
    }

    public function getLowStock($threshold = null) {
        $sql = "SELECT i.*, p.name as product_name, p.sku FROM inventory i JOIN products p ON i.product_id = p.id WHERE p.is_active = 1 AND i.quantity <= " . ($threshold ? "?" : "i.reorder_level");
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($threshold ? [$threshold] : []);
        return $stmt->fetchAll();
    }
}
