<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'username'");
    $stmt->execute();
    if ((int)$stmt->fetchColumn() > 0) {
        echo "Column 'username' already exists. Skipping.\n";
    } else {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `username` VARCHAR(100) NULL AFTER `last_name`");
        $pdo->exec("ALTER TABLE `users` ADD UNIQUE INDEX `idx_users_username` (`username`)");
        echo "Migration complete: username column added.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
