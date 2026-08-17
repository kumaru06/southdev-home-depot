<?php
/**
 * Verify inventory integrity after a concurrent checkout load test.
 *
 * Usage (CLI):
 *   php tools/load-tests/verify-stock-after-test.php [product_id]
 *
 * Checks:
 *   - inventory quantity is not negative
 *   - product_size_options stock is not negative
 *   - recent orders vs stock movements (basic sanity)
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';

$productId = isset($argv[1]) ? (int) $argv[1] : 0;

echo "=== Stock integrity check ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$issues = 0;

// Negative inventory
$negInv = $pdo->query(
    "SELECT i.product_id, p.name, i.quantity
     FROM inventory i
     JOIN products p ON p.id = i.product_id
     WHERE i.quantity < 0"
)->fetchAll();

if ($negInv) {
    $issues++;
    echo "[FAIL] Negative inventory rows:\n";
    foreach ($negInv as $row) {
        echo "  - Product #{$row['product_id']} ({$row['name']}): qty={$row['quantity']}\n";
    }
} else {
    echo "[OK] No negative inventory quantities.\n";
}

// Negative size option stock
$negSize = $pdo->query(
    "SELECT s.id, s.product_id, p.name, s.size_label, s.stock
     FROM product_size_options s
     JOIN products p ON p.id = s.product_id
     WHERE s.stock < 0"
)->fetchAll();

if ($negSize) {
    $issues++;
    echo "[FAIL] Negative size option stock:\n";
    foreach ($negSize as $row) {
        echo "  - Size #{$row['id']} product #{$row['product_id']} ({$row['size_label']}): stock={$row['stock']}\n";
    }
} else {
    echo "[OK] No negative size option stock.\n";
}

if ($productId > 0) {
    echo "\n--- Product #{$productId} detail ---\n";

    $stmt = $pdo->prepare(
        "SELECT p.name, COALESCE(i.quantity, 0) AS qty
         FROM products p
         LEFT JOIN inventory i ON i.product_id = p.id
         WHERE p.id = ?"
    );
    $stmt->execute([$productId]);
    $row = $stmt->fetch();
    if ($row) {
        echo "Inventory qty: {$row['qty']}\n";
    }

    $stmt = $pdo->prepare(
        "SELECT id, size_label, stock FROM product_size_options WHERE product_id = ? ORDER BY id"
    );
    $stmt->execute([$productId]);
    $sizes = $stmt->fetchAll();
    if ($sizes) {
        echo "Size options:\n";
        foreach ($sizes as $s) {
            echo "  - #{$s['id']} {$s['size_label']}: stock={$s['stock']}\n";
        }
    }

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE oi.product_id = ? AND o.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
    );
    $stmt->execute([$productId]);
    $recentOrders = (int) $stmt->fetchColumn();
    echo "Order line items (last hour): {$recentOrders}\n";
}

echo "\n";
if ($issues > 0) {
    echo "Result: FAILED ({$issues} issue type(s) found)\n";
    exit(1);
}

echo "Result: PASSED\n";
exit(0);
