<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "=== Return Requests ===\n";
$stmt = $pdo->query('SELECT rr.*, o.order_number FROM return_requests rr JOIN orders o ON rr.order_id = o.id ORDER BY rr.id');
$rows = $stmt->fetchAll();
foreach ($rows as $r) {
    echo "Return #{$r['id']}: Order {$r['order_number']} - Status: {$r['status']}\n";
}

echo "\n=== Payments ===\n";
$stmt = $pdo->query('SELECT p.*, o.order_number FROM payments p JOIN orders o ON p.order_id = o.id ORDER BY p.id');
$rows = $stmt->fetchAll();
foreach ($rows as $r) {
    echo "Payment #{$r['id']}: Order {$r['order_number']} - Method: {$r['payment_method']} - Status: {$r['status']}\n";
}

// Fix: mark payments as refunded for orders with approved/completed return requests
echo "\n=== Fixing approved/completed returns ===\n";
$stmt = $pdo->query("
    SELECT rr.id as return_id, rr.order_id, rr.status as return_status, o.order_number, p.id as payment_id, p.status as payment_status
    FROM return_requests rr
    JOIN orders o ON rr.order_id = o.id
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE rr.status IN ('approved', 'completed')
    AND (p.status IS NULL OR p.status != 'refunded')
");
$fixes = $stmt->fetchAll();

if (empty($fixes)) {
    echo "No payments need fixing.\n";
} else {
    $update = $pdo->prepare("UPDATE payments SET status = 'refunded' WHERE id = ?");
    foreach ($fixes as $fix) {
        if ($fix['payment_id']) {
            $update->execute([$fix['payment_id']]);
            echo "Marked payment #{$fix['payment_id']} for Order {$fix['order_number']} as REFUNDED (return status: {$fix['return_status']})\n";
        }
    }
}

echo "\nDone.\n";
