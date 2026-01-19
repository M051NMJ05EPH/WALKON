<?php
include 'config.php';
echo "--- BRANDS ---\n";
try {
    $brands = $pdo->query("SELECT id, name FROM brands")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($brands as $b) { echo "{$b['id']}: {$b['name']}\n"; }
} catch (Exception $e) { echo "Error brands: " . $e->getMessage() . "\n"; }

echo "\n--- CATEGORIES ---\n";
try {
    $cats = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cats as $c) { echo "{$c['id']}: {$c['name']}\n"; }
} catch (Exception $e) { echo "Error cats: " . $e->getMessage() . "\n"; }
?>
