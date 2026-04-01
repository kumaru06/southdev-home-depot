<?php
require_once __DIR__ . '/../config/database.php';

echo "=== getByUserId raw result for user 31 ===\n";
$stmt = $pdo->prepare('SELECT o.*, p.payment_method FROM orders o LEFT JOIN payments p ON p.order_id = o.id WHERE o.user_id = ? ORDER BY o.created_at DESC');
$stmt->execute([31]);
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Order #{$r['id']} ({$r['order_number']}) - payment_method key exists: " . (array_key_exists('payment_method', $r) ? 'YES' : 'NO') . " - Value: '" . ($r['payment_method'] ?? 'NULL') . "'\n";
}

echo "\n=== Check for duplicate payment records ===\n";
$stmt = $pdo->query('SELECT order_id, COUNT(*) as cnt FROM payments GROUP BY order_id HAVING cnt > 1');
$dupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($dupes)) {
    echo "No duplicates found.\n";
} else {
    print_r($dupes);
}
