<?php
/**
 * Database Configuration
 * Auto-detects local (XAMPP) vs production (InfinityFree)
 */

// Detect environment: if running on localhost/XAMPP, use local DB
if (in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) 
    || php_sapi_name() === 'cli' 
    || strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false) {
    // LOCAL (XAMPP)
    $db_host = 'localhost';
    $db_name = 'southdev';
    $db_user = 'root';
    $db_pass = '';
} else {
    // PRODUCTION (InfinityFree)
    $db_host = 'sql107.infinityfree.com';
    $db_name = 'if0_41705046_southdev';
    $db_user = 'if0_41705046';
    $db_pass = 'markperez201';
}

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
    die("Database connection failed: " . $e->getMessage());
}
