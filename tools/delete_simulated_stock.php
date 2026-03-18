<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
try {
    $note = 'Simulated add stock test';
    $stmt = $pdo->prepare("SELECT id, product_id, type, quantity, notes, created_at FROM stock_movements WHERE notes = ?");
    $stmt->execute([$note]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo json_encode(['deleted' => 0, 'deleted_ids' => [], 'rows' => []]);
        exit(0);
    }
    $ids = array_map(function($r){ return (int)$r['id']; }, $rows);
    $in = implode(',', $ids);
    $delSql = "DELETE FROM stock_movements WHERE id IN ($in)";
    $del = $pdo->prepare($delSql);
    $del->execute();
    echo json_encode(['deleted' => count($ids), 'deleted_ids' => $ids, 'rows' => $rows], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
