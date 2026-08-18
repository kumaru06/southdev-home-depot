<?php
/**
 * Inventory Model
 */

class Inventory {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Expensive items naturally carry lower unit counts — cap reorder level by price tier.
     */
    public static function effectiveReorderLevel(float $price, ?int $storedReorder = null): int {
        $stored = max(0, (int) ($storedReorder ?? 10));
        if ($price >= 20000) {
            return min($stored, 3);
        }
        if ($price >= 10000) {
            return min($stored, 5);
        }
        if ($price >= 5000) {
            return min($stored, 7);
        }
        return $stored;
    }

    public static function effectiveReorderLevelSqlExpr(
        string $priceColumn = 'p.price',
        string $reorderColumn = 'i.reorder_level'
    ): string {
        return "CASE
            WHEN {$priceColumn} >= 20000 THEN LEAST(COALESCE({$reorderColumn}, 10), 3)
            WHEN {$priceColumn} >= 10000 THEN LEAST(COALESCE({$reorderColumn}, 10), 5)
            WHEN {$priceColumn} >= 5000 THEN LEAST(COALESCE({$reorderColumn}, 10), 7)
            ELSE COALESCE({$reorderColumn}, 10)
        END";
    }

    private function effectiveReorderLevelSql(): string {
        return self::effectiveReorderLevelSqlExpr();
    }

    public function getByProductId($productId) {
        $stmt = $this->pdo->prepare(
            "SELECT i.*, p.name as product_name, p.sku,
                    COALESCE(sz.option_count, 0) AS size_option_count,
                    COALESCE(sz.total_stock, 0) AS size_option_stock
             FROM inventory i
             JOIN products p ON i.product_id = p.id
             LEFT JOIN (
                 SELECT product_id, COUNT(*) AS option_count, COALESCE(SUM(stock), 0) AS total_stock
                 FROM product_size_options
                 GROUP BY product_id
             ) sz ON sz.product_id = p.id
             WHERE i.product_id = ?"
        );
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    public function getAll() {
        // Include product cost if present (nullable column). Cost may be NULL if not populated.
        return $this->pdo->query(
            "SELECT i.*, p.name as product_name, p.sku, p.price, p.cost, p.image, p.category_id, c.name as category_name,
                    COALESCE(sz.option_count, 0) AS size_option_count,
                    COALESCE(sz.total_stock, 0) AS size_option_stock
             FROM inventory i
             JOIN products p ON i.product_id = p.id
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN (
                 SELECT product_id, COUNT(*) AS option_count, COALESCE(SUM(stock), 0) AS total_stock
                 FROM product_size_options
                 GROUP BY product_id
             ) sz ON sz.product_id = p.id
             WHERE p.is_active = 1
             ORDER BY p.name"
        )->fetchAll();
    }

    public function updateQuantity($productId, $quantity) {
        $stmt = $this->pdo->prepare("INSERT INTO inventory (product_id, quantity) VALUES (?, ?) ON DUPLICATE KEY UPDATE quantity = ?");
        return $stmt->execute([$productId, $quantity, $quantity]);
    }

    public function adjustQuantity($productId, $adjustment) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO inventory (product_id, quantity) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
        );
        return $stmt->execute([$productId, $adjustment]);
    }

    public function reserveQuantity($productId, $quantity) {
        $stmt = $this->pdo->prepare(
            "UPDATE inventory
             SET quantity = quantity - ?
             WHERE product_id = ? AND quantity >= ?"
        );
        $stmt->execute([$quantity, $productId, $quantity]);
        return $stmt->rowCount() > 0;
    }

    public function getLowStock($threshold = null) {
        if ($threshold !== null) {
            $sql = "SELECT i.*, p.name as product_name, p.sku, p.price
                    FROM inventory i
                    JOIN products p ON i.product_id = p.id
                    WHERE p.is_active = 1
                      AND NOT EXISTS (
                          SELECT 1 FROM product_size_options s WHERE s.product_id = p.id
                      )
                      AND i.quantity <= ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$threshold]);
            return $stmt->fetchAll();
        }

        $effective = $this->effectiveReorderLevelSql();
        $sql = "SELECT i.*, p.name as product_name, p.sku, p.price,
                       ({$effective}) AS effective_reorder_level
                FROM inventory i
                JOIN products p ON i.product_id = p.id
                WHERE p.is_active = 1
                  AND NOT EXISTS (
                      SELECT 1 FROM product_size_options s WHERE s.product_id = p.id
                  )
                  AND i.quantity <= ({$effective})";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function countLowStock($threshold = null) {
        if ($threshold !== null) {
            $sql = "SELECT COUNT(*) FROM inventory i
                    JOIN products p ON i.product_id = p.id
                    WHERE p.is_active = 1
                      AND NOT EXISTS (
                          SELECT 1 FROM product_size_options s WHERE s.product_id = p.id
                      )
                      AND i.quantity <= ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$threshold]);
            return (int) $stmt->fetchColumn();
        }

        $effective = $this->effectiveReorderLevelSql();
        $sql = "SELECT COUNT(*) FROM inventory i
                JOIN products p ON i.product_id = p.id
                WHERE p.is_active = 1
                  AND NOT EXISTS (
                      SELECT 1 FROM product_size_options s WHERE s.product_id = p.id
                  )
                  AND i.quantity <= ({$effective})";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }
}
