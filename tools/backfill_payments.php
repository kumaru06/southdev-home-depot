<?php
/**
 * One-time tool to backfill payment records for orders that have no payment entry.
 * Run once: php tools/backfill_payments.php
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// $pdo is already available from database.php

// Find orders with no payment record
$stmt = $pdo->query("
    SELECT o.id, o.order_number, o.total_amount
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE p.id IS NULL
    ORDER BY o.id ASC
");
$orders = $stmt->fetchAll();

if (empty($orders)) {
    echo "All orders already have payment records. Nothing to do.\n";
    exit;
}

echo "Found " . count($orders) . " orders without payment records.\n";

$insert = $pdo->prepare("INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, ?)");

$count = 0;
foreach ($orders as $order) {
    $insert->execute([
        $order['id'],
        'cod',                  // Default to COD for legacy orders
        $order['total_amount'],
        'completed'             // Mark as completed since these are existing orders
    ]);
    $count++;
    echo "  Created payment for Order #{$order['order_number']} (₱" . number_format($order['total_amount'], 2) . ") → COD / completed\n";
}

echo "\nDone! Created {$count} payment record(s).\n";
