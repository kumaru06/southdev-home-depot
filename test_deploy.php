<?php
// Simple test to diagnose 500 error on InfinityFree
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h3>PHP Version: " . phpversion() . "</h3>";
echo "<h3>Session: OK</h3>";

echo "<h3>Files/Folders in htdocs:</h3>";
echo "<pre>";
$items = scandir(__DIR__);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    $type = is_dir(__DIR__ . '/' . $item) ? '[DIR]' : '[FILE]';
    echo "$type $item\n";
}
echo "</pre>";

echo "<h3>Config folder exists? " . (is_dir(__DIR__ . '/config') ? 'YES' : 'NO') . "</h3>";
echo "<h3>Controllers folder exists? " . (is_dir(__DIR__ . '/controllers') ? 'YES' : 'NO') . "</h3>";
echo "<h3>Views folder exists? " . (is_dir(__DIR__ . '/views') ? 'YES' : 'NO') . "</h3>";
