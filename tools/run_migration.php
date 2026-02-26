<?php
// Run a SQL migration file using existing PDO connection
if ($argc < 2) {
    echo "Usage: php run_migration.php path/to/migration.sql\n";
    exit(1);
}

$file = $argv[1];
if (!file_exists($file)) {
    echo "File not found: {$file}\n";
    exit(2);
}

require_once __DIR__ . '/../config/database.php'; // provides $pdo
$sql = file_get_contents($file);
try {
    $pdo->beginTransaction();
    $pdo->exec($sql);
    $pdo->commit();
    echo "Migration applied: {$file}\n";
    exit(0);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(3);
}
