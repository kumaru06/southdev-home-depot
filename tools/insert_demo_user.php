<?php
require_once __DIR__ . '/../config/database.php';
$email = 'inventory@demo.local';
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'exists']);
        exit;
    }

    $hash = '$2y$10$u5u9RGJq.87xvwl7vhzskeuV6vDhZhn1aLxsxbAQdy17I6zB.ut7W'; // Demo@1234
    $insert = $pdo->prepare('INSERT INTO users (role_id, first_name, last_name, email, password, phone, is_active, email_verified_at, created_at) VALUES (:role_id,:first,:last,:email,:pass,:phone,1,NOW(),NOW())');
    $insert->execute([
        'role_id' => 4,
        'first' => 'Inventory',
        'last' => 'Demo',
        'email' => $email,
        'pass' => $hash,
        'phone' => '09170000000'
    ]);
    echo json_encode(['status' => 'inserted', 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
