<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// New query (excludes cancelled + refunded)
$stmt = $pdo->query("
    SELECT SUM(o.total_amount) as total
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE o.status NOT IN ('cancelled')
    AND (p.status IS NULL OR p.status != 'refunded')
");
$newTotal = $stmt->fetchColumn();

// Old query (only excluded cancelled)
$stmt2 = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status NOT IN ('cancelled')");
$oldTotal = $stmt2->fetchColumn();

echo "Old total sales (excl cancelled only): ₱" . number_format($oldTotal, 2) . "\n";
echo "New total sales (excl cancelled + refunded): ₱" . number_format($newTotal, 2) . "\n";
echo "Difference (refunded amount): ₱" . number_format($oldTotal - $newTotal, 2) . "\n";
