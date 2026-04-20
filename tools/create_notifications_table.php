<?php
$pdo = new PDO('mysql:host=localhost;dbname=southdev;charset=utf8mb4', 'root', '');
$pdo->exec(file_get_contents(__DIR__ . '/../database/create_notifications_table.sql'));
echo "Notifications table created locally.\n";
