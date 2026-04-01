<?php
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query("
    SELECT p.name, p.price, p.cost, COALESCE(i.quantity, 0) as qty,
           COALESCE(p.cost, p.price) as unit_val,
           COALESCE(i.quantity, 0) * COALESCE(p.cost, p.price) as line_value
    FROM products p
    LEFT JOIN inventory i ON p.id = i.product_id
    WHERE p.is_active = 1
    ORDER BY COALESCE(i.quantity, 0) * COALESCE(p.cost, p.price) DESC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
echo str_pad("Product", 40) . str_pad("Price", 12) . str_pad("Cost", 12) . str_pad("Stock", 8) . str_pad("Value Used", 14) . "Line Value\n";
echo str_repeat("-", 100) . "\n";

foreach ($rows as $r) {
    $usedVal = ($r['cost'] !== null && $r['cost'] !== '') ? floatval($r['cost']) : floatval($r['price']);
    $lineVal = $usedVal * intval($r['qty']);
    $total += $lineVal;
    $costLabel = ($r['cost'] !== null && $r['cost'] !== '') ? number_format(floatval($r['cost']), 2) : 'N/A (use price)';

    echo str_pad(substr($r['name'], 0, 38), 40)
       . str_pad(number_format(floatval($r['price']), 2), 12)
       . str_pad($costLabel, 12)
       . str_pad($r['qty'], 8)
       . str_pad(number_format($usedVal, 2), 14)
       . number_format($lineVal, 2) . "\n";
}
echo str_repeat("-", 100) . "\n";
echo str_pad("TOTAL INVENTORY VALUE:", 72) . number_format($total, 2) . "\n";
echo "\nProducts: " . count($rows) . "\n";
