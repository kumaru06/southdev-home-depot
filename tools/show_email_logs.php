<?php
require_once __DIR__ . '/../config/database.php';

echo "--- Recent email-related log entries (logs table) ---\n";
try {
    $sql = "SELECT id, level, message, created_at FROM logs WHERE message LIKE '%email%' OR message LIKE '%Email%' OR message LIKE '%send failed%' OR message LIKE '%Email send failed%' ORDER BY id DESC LIMIT 20";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
    if ($rows) {
        foreach ($rows as $r) {
            echo "[{$r['id']}] {$r['created_at']} ({$r['level']}): {$r['message']}\n";
        }
    } else {
        echo "No recent email-related log entries found.\n";
    }
} catch (Exception $e) {
    echo "Error querying logs table: " . $e->getMessage() . "\n";
}

echo "\n--- Fallback mail files (storage/mails) ---\n";
$dir = __DIR__ . '/../storage/mails';
if (is_dir($dir)) {
    $files = array_slice(scandir($dir, SCANDIR_SORT_DESCENDING), 0, 20);
    if ($files) {
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $path = $dir . '/' . $f;
            $size = filesize($path);
            $mtime = date('Y-m-d H:i:s', filemtime($path));
            echo "{$f} — {$size} bytes — {$mtime}\n";
        }
    } else {
        echo "No files found in storage/mails.\n";
    }
} else {
    echo "storage/mails directory does not exist.\n";
}
