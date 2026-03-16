<?php
require_once __DIR__ . '/../config/database.php';
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) echo $t . PHP_EOL;
