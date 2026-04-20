<?php
/**
 * Database Configuration
 * Reads from .env file via env() helper (loaded in config.php)
 * Falls back to auto-detection if env vars are not set
 */

// Detect local environment
$db_is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) 
    || php_sapi_name() === 'cli' 
    || strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false;

// Read from .env, with environment-based defaults
$db_host = env('DB_HOST', $db_is_local ? 'localhost' : '');
$db_name = env('DB_NAME', $db_is_local ? 'southdev' : '');
$db_user = env('DB_USER', $db_is_local ? 'root' : '');
$db_pass = env('DB_PASSWORD', $db_is_local ? '' : '');

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    // Align MySQL session timezone with PHP (Asia/Manila = UTC+8)
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    // Log actual error, show generic message to user
    error_log('Database connection failed: ' . $e->getMessage());
    if ($db_host === 'localhost') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("A system error occurred. Please try again later.");
    }
}
