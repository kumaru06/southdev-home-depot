<?php
require_once __DIR__ . '/../config/database.php';
try {
    $stmt = $pdo->query('SHOW CREATE TABLE users');
    $row = $stmt->fetch();
    if ($row && isset($row['Create Table'])) {
        echo $row['Create Table'] . "\n";
    } else {
        echo "Cannot retrieve CREATE TABLE output.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
