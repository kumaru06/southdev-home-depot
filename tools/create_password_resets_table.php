<?php
// Create password_resets table using the app's DB connection via config
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PasswordReset.php';

try {
    $pr = new PasswordReset($pdo);
    echo "password_resets table is present or was created successfully.\n";
} catch (Throwable $e) {
    echo "Failed to create password_resets table: " . $e->getMessage() . "\n";
    exit(1);
}
