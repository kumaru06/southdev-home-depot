<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Test damaged inventory query
$sql = "SELECT dp.created_at, p.sku, p.name as product_name, dp.quantity, dp.status, dp.reason, o.order_number
        FROM damaged_products dp
        JOIN products p ON dp.product_id = p.id
        JOIN orders o ON dp.order_id = o.id
        ORDER BY dp.created_at DESC";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo count($rows) . " damaged records found\n";
foreach ($rows as $r) {
    echo "  {$r['order_number']} - {$r['product_name']} - {$r['status']}\n";
}

// Test stock movements
echo "\n";
$sql2 = "SELECT sm.created_at, p.name, sm.quantity, sm.type
         FROM stock_movements sm
         JOIN products p ON sm.product_id = p.id
         WHERE sm.quantity > 0
         ORDER BY sm.created_at DESC
         LIMIT 5";
$stmt2 = $pdo->query($sql2);
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo count($rows2) . " stock-in records found\n";
foreach ($rows2 as $r) {
    echo "  {$r['name']} +{$r['quantity']} ({$r['type']})\n";
}

// Test current inventory
echo "\n";
$sql3 = "SELECT COUNT(*) FROM products p JOIN inventory i ON p.id = i.product_id WHERE p.is_active = 1";
$cnt = $pdo->query($sql3)->fetchColumn();
echo "$cnt products in inventory\n";
