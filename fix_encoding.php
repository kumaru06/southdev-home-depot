<?php
/**
 * Fix corrupted UTF-8 em-dash characters in the database.
 * The string ΓÇö (or its variants) should be — or just " - "
 * Run once, then delete this file.
 */
require_once __DIR__ . '/config/database.php';

$tables = [
    'damaged_products' => 'description',
    'return_requests'  => 'reason',
    'stock_movements'  => 'notes',
    'cancel_requests'  => 'reason',
    'system_logs'      => 'description',
];

$fixed = 0;
echo "<h3>Fixing corrupted em-dash characters...</h3><pre>\n";

foreach ($tables as $table => $column) {
    // Check if table exists
    $check = $pdo->query("SHOW TABLES LIKE '{$table}'");
    if ($check->rowCount() === 0) {
        echo "[SKIP] Table '{$table}' does not exist.\n";
        continue;
    }

    // Check if column exists
    $colCheck = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    if ($colCheck->rowCount() === 0) {
        echo "[SKIP] Column '{$table}.{$column}' does not exist.\n";
        continue;
    }

    // Count affected rows
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` LIKE :pattern");
    $countStmt->execute(['pattern' => '%ΓÇö%']);
    $count = $countStmt->fetchColumn();

    if ($count > 0) {
        $stmt = $pdo->prepare("UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, 'ΓÇö', '-') WHERE `{$column}` LIKE :pattern");
        $stmt->execute(['pattern' => '%ΓÇö%']);
        echo "[FIXED] {$table}.{$column}: {$count} rows updated\n";
        $fixed += $count;
    } else {
        echo "[OK] {$table}.{$column}: no corrupted characters\n";
    }
}

echo "\nTotal rows fixed: {$fixed}\n";
echo "</pre><p><strong>Done!</strong> Delete this file now.</p>";
