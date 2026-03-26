<?php
/**
 * Reset history script
 * - Deletes orders, order items, payments, logs
 * - Deletes all users except demo accounts listed below
 * - Runs in dry-run mode by default; pass --yes to execute
 *
 * Usage:
 *   php tools/reset_history.php        # dry-run, shows counts
 *   php tools/reset_history.php --yes  # perform deletions
 */

require_once __DIR__ . '/../config/database.php';

$keepEmails = [
    'customer@southdev.com',
    'staff@southdev.com',
    'admin@southdev.com',
    'inventory@demo.local'
];

$confirm = in_array('--yes', $argv);

function fetchCount($pdo, $sql, $params = []){
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

echo "Southdev — Reset History Utility\n";
echo "This will remove orders, payments, logs and non-demo users.\n\n";

$counts = [];
$counts['orders'] = fetchCount($pdo, "SELECT COUNT(*) FROM orders");
$counts['order_items'] = fetchCount($pdo, "SELECT COUNT(*) FROM order_items");
$counts['payments'] = fetchCount($pdo, "SELECT COUNT(*) FROM payments");
$counts['logs'] = fetchCount($pdo, "SELECT COUNT(*) FROM logs");

$placeholders = implode(',', array_fill(0, count($keepEmails), '?'));
$counts['users_total'] = fetchCount($pdo, "SELECT COUNT(*) FROM users");
$counts['users_keep'] = fetchCount($pdo, "SELECT COUNT(*) FROM users WHERE email IN ($placeholders)", $keepEmails);
$counts['users_delete'] = $counts['users_total'] - $counts['users_keep'];

echo "Summary (current):\n";
foreach ($counts as $k => $v) {
    echo " - $k: $v\n";
}

if (!$confirm) {
    echo "\nDRY RUN — no changes will be made.\n";
    echo "To perform the reset, run: php tools/reset_history.php --yes\n";
    exit(0);
}

// Perform deletion
echo "\nPerforming reset...\n";
try {
    // Disable foreign key checks for safe truncation
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    // Truncate tables that represent history
    $tablesToTruncate = ['order_items', 'payments', 'orders', 'logs'];
    foreach ($tablesToTruncate as $t) {
        $pdo->exec("TRUNCATE TABLE `$t`");
        echo "Truncated $t\n";
    }

    // Delete non-demo users
    $sql = "DELETE FROM users WHERE email NOT IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($keepEmails);
    $deletedUsers = $stmt->rowCount();
    echo "Deleted $deletedUsers non-demo user(s)\n";

    // Reset AUTO_INCREMENT values to 1 (or next available)
    $resetTables = array_merge($tablesToTruncate, ['users']);
    foreach ($resetTables as $t) {
        $pdo->exec("ALTER TABLE `$t` AUTO_INCREMENT = 1");
    }

    // Re-enable FK checks
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    echo "\nReset completed successfully.\n";
    echo "Kept demo accounts:\n" . implode("\n - ", $keepEmails) . "\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error during reset: " . $e->getMessage() . "\n";
    exit(1);
}

// Print final counts for verification
$final = [];
$final['orders'] = fetchCount($pdo, "SELECT COUNT(*) FROM orders");
$final['order_items'] = fetchCount($pdo, "SELECT COUNT(*) FROM order_items");
$final['payments'] = fetchCount($pdo, "SELECT COUNT(*) FROM payments");
$final['logs'] = fetchCount($pdo, "SELECT COUNT(*) FROM logs");
$final['users_total'] = fetchCount($pdo, "SELECT COUNT(*) FROM users");
$final['users_keep'] = fetchCount($pdo, "SELECT COUNT(*) FROM users WHERE email IN ($placeholders)", $keepEmails);

echo "\nFinal counts:\n";
foreach ($final as $k => $v) {
    echo " - $k: $v\n";
}

return 0;
