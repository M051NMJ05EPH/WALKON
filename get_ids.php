<?php
require 'config.php';
$stmt = $pdo->query('SELECT id, name FROM sellers LIMIT 1');
$seller = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Default Seller: " . json_encode($seller) . "\n";

$stmt = $pdo->query('SELECT id, name FROM categories LIMIT 5');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Sample Categories: " . json_encode($categories) . "\n";
