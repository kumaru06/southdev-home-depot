<?php
/**
 * Migrate damaged_products table: change 'pending' status to 'received'
 */
require_once __DIR__ . '/../config/database.php';

try {
    // Alter the ENUM to use 'received' instead of 'pending'
    $pdo->exec("ALTER TABLE `damaged_products` MODIFY COLUMN `status` ENUM('received','inspected','written_off','repaired') NOT NULL DEFAULT 'received'");
    echo "ENUM updated successfully.\n";

    // Any rows that had 'pending' will now have empty string due to ENUM change - fix them
    $updated = $pdo->exec("UPDATE `damaged_products` SET `status` = 'received' WHERE `status` = '' OR `status` IS NULL");
    echo "Rows updated to 'received': {$updated}\n";

    $count = $pdo->query("SELECT COUNT(*) FROM damaged_products")->fetchColumn();
    echo "Total damaged_products records: {$count}\n";
    echo "Migration complete!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
