<?php
require_once __DIR__ . '/../config/database.php';
try {
    $stmt = $pdo->prepare('SELECT id, role_id, email, password, is_active, email_verified_at, created_at FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => 'inventory@demo.local']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo json_encode($row, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['error' => 'not_found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'exception', 'message' => $e->getMessage()]);
}
