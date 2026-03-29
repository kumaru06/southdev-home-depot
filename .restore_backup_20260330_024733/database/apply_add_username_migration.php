<?php
/**
 * Safe migration runner for adding `username` column to `users`.
 * Usage: php apply_add_username_migration.php
 *
 * This script checks the schema and only applies the ALTER TABLE if the
 * `username` column is not present. It prompts for confirmation before
 * altering the database. Make a backup before running.
 */

require __DIR__ . '/../config/database.php';

echo "WARNING: Make a database backup before running this script.\n";
echo "This script will add a 'username' column to the 'users' table if missing.\n";
$dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();

$check = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'username'");
$check->execute([$dbName]);
if (((int)$check->fetchColumn()) > 0) {
    echo "'username' column already exists in 'users'. No action taken.\n";
    exit(0);
}

echo "Proceed to add 'username' column to 'users'? Type 'yes' to continue: ";
$handle = fopen('php://stdin', 'r');
$line = trim(fgets($handle));
if ($line !== 'yes') {
    echo "Aborted by user.\n";
    exit(2);
}

try {
    // Add nullable username column first
    $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(100) NULL");
    echo "Added column 'username'.\n";

    // Check for existing duplicate values before adding UNIQUE index
    $dupCheck = $pdo->query("SELECT username, COUNT(*) c FROM users WHERE username IS NOT NULL GROUP BY username HAVING c > 1");
    $dups = $dupCheck->fetchAll();
    if (count($dups) > 0) {
        echo "Detected duplicate username values; UNIQUE index will not be created.\n";
        echo "Please resolve duplicates manually before creating a UNIQUE index.\n";
        exit(0);
    }

    // Create unique index (name chosen to avoid collision)
    $pdo->exec("ALTER TABLE users ADD UNIQUE INDEX ux_users_username (username)");
    echo "Created UNIQUE index on 'username'. Migration complete.\n";
    exit(0);
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
