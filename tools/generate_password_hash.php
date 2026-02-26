<?php
if ($argc < 2) {
    echo "Usage: php generate_password_hash.php 'YourPlainPassword'\n";
    exit(1);
}
$password = $argv[1];
echo password_hash($password, PASSWORD_DEFAULT) . PHP_EOL;
