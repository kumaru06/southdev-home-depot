<?php
/**
 * Add orders.shipping_fee column (idempotent).
 * Usage: php tools/apply_shipping_fee_migration.php
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

try {
    if (!columnExists($pdo, 'orders', 'shipping_fee')) {
        $pdo->exec(
            "ALTER TABLE `orders`
             ADD COLUMN `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_amount`"
        );
        echo "Added orders.shipping_fee\n";
    } else {
        echo "orders.shipping_fee already exists\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Migration failed: ' . $e->getMessage() . "\n");
    exit(1);
}
