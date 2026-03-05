<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Log.php';

$logModel = new Log($pdo);

// Test getAll
echo "=== Testing getAll([], 25, 0) ===\n";
try {
    $result = $logModel->getAll([], 25, 0);
    echo "Returned: " . count($result) . " rows\n";
    if (count($result) > 0) {
        echo "Keys: " . implode(', ', array_keys($result[0])) . "\n";
        echo "First row:\n";
        print_r($result[0]);
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test getActionTypes
echo "\n=== Testing getActionTypes() ===\n";
try {
    $types = $logModel->getActionTypes();
    echo "Type: " . gettype($types) . "\n";
    echo "Values:\n";
    print_r($types);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test count
echo "\n=== Testing count() ===\n";
try {
    $cnt = $logModel->count([]);
    echo "Count: " . $cnt . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
