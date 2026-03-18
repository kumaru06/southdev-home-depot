<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/StockMovement.php';

header('Content-Type: application/json');

try {
    // find a product to update
    $stmt = $pdo->query('SELECT id, name FROM products LIMIT 1');
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        echo json_encode(['error' => 'no_products']);
        exit(0);
    }
    $productId = (int)$product['id'];

    $sm = new StockMovement($pdo);
    $type = 'purchase';
    $quantity = 5; // add 5 units
    $notes = 'Simulated add stock test';
    $performedBy = null; // no session user

    $ok = $sm->record($productId, $type, $quantity, null, $notes, $performedBy);
    if (!$ok) {
        echo json_encode(['error' => 'insert_failed']);
        exit(0);
    }

    // fetch last inserted movement for that product
    $rows = $sm->getByProduct($productId, 5);
    echo json_encode(['inserted_for_product' => $product, 'recent_movements' => $rows], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo json_encode(['error' => 'exception', 'message' => $e->getMessage()]);
}
