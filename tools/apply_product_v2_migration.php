<?php
/**
 * Apply Product Details v2 schema changes safely (idempotent).
 * Usage: php tools/apply_product_v2_migration.php
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
    if (!tableExists($pdo, 'product_images')) {
        $pdo->exec(
            "CREATE TABLE `product_images` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `filename` VARCHAR(255) NOT NULL,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_product_images_product` (`product_id`),
                CONSTRAINT `fk_product_images_product`
                    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        echo "Created product_images\n";
    } else {
        echo "product_images already exists\n";
    }

    $cols = [
        'specifications' => "ALTER TABLE `products` ADD COLUMN `specifications` TEXT NULL AFTER `description`",
        'size_label'     => "ALTER TABLE `products` ADD COLUMN `size_label` VARCHAR(50) NULL AFTER `sku`",
        'size_group'     => "ALTER TABLE `products` ADD COLUMN `size_group` VARCHAR(100) NULL AFTER `size_label`",
    ];
    foreach ($cols as $col => $sql) {
        if (!columnExists($pdo, 'products', $col)) {
            $pdo->exec($sql);
            echo "Added products.{$col}\n";
        } else {
            echo "products.{$col} already exists\n";
        }
    }

    $inserted = $pdo->exec(
        "INSERT INTO `product_images` (`product_id`, `filename`, `sort_order`)
         SELECT p.`id`, p.`image`, 0
         FROM `products` p
         WHERE p.`image` IS NOT NULL
           AND p.`image` <> ''
           AND NOT EXISTS (
               SELECT 1 FROM `product_images` pi WHERE pi.`product_id` = p.`id`
           )"
    );
    echo "Backfilled gallery rows: " . (int) $inserted . "\n";
    echo "DONE\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
