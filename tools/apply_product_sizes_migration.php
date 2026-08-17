<?php
/**
 * Apply product size options + cart size_option_id (idempotent).
 * Usage: php tools/apply_product_sizes_migration.php
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?"
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

try {
    if (!tableExists($pdo, 'product_size_options')) {
        $pdo->exec(
            "CREATE TABLE `product_size_options` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `size_label` VARCHAR(50) NOT NULL,
                `price` DECIMAL(10,2) NOT NULL,
                `stock` INT NOT NULL DEFAULT 0,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_size_options_product` (`product_id`),
                CONSTRAINT `fk_size_options_product`
                    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        echo "Created product_size_options\n";
    } else {
        echo "product_size_options already exists\n";
    }

    if (!columnExists($pdo, 'cart', 'size_option_id')) {
        $pdo->exec("ALTER TABLE `cart` ADD COLUMN `size_option_id` INT NULL DEFAULT NULL AFTER `product_id`");
        echo "Added cart.size_option_id\n";
    } else {
        echo "cart.size_option_id already exists\n";
    }

    if (!columnExists($pdo, 'order_items', 'size_label')) {
        $pdo->exec("ALTER TABLE `order_items` ADD COLUMN `size_label` VARCHAR(50) NULL AFTER `product_id`");
        echo "Added order_items.size_label\n";
    } else {
        echo "order_items.size_label already exists\n";
    }

    $pid = 33;
    $exists = $pdo->prepare('SELECT COUNT(*) FROM product_size_options WHERE product_id = ?');
    $exists->execute([$pid]);
    if ((int) $exists->fetchColumn() === 0) {
        $p = $pdo->prepare('SELECT price FROM products WHERE id = ?');
        $p->execute([$pid]);
        $base = $p->fetchColumn();
        if ($base !== false) {
            $base = (float) $base;
            $inv = $pdo->prepare('SELECT quantity FROM inventory WHERE product_id = ?');
            $inv->execute([$pid]);
            $stock = (int) ($inv->fetchColumn() ?: 0);
            $sizes = [
                ['60 x 60 cm', $base, max($stock, 100), 0],
                ['30 x 30 cm', round($base * 0.55, 2), 180, 1],
                ['40 x 40 cm', round($base * 0.7, 2), 150, 2],
                ['30 x 60 cm', round($base * 0.85, 2), 120, 3],
                ['60 x 120 cm', round($base * 1.6, 2), 80, 4],
            ];
            $ins = $pdo->prepare(
                'INSERT INTO product_size_options (product_id, size_label, price, stock, sort_order) VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($sizes as $s) {
                $ins->execute([$pid, $s[0], $s[1], $s[2], $s[3]]);
            }
            echo "Seeded demo sizes for product #{$pid}\n";
        }
    }

    echo "DONE\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
