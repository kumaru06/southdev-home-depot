<?php
require_once __DIR__ . '/../config/database.php';
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'birthdate'");
    $row = $stmt->fetch();
    if ($row) {
        echo "birthdate column exists:\n";
        print_r($row);
    } else {
        echo "birthdate column not found.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
