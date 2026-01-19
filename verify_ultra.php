<?php
include 'config.php';

$tables = ['product_base', 'product_skus', 'product_prices', 'product_stock', 'product_sizes', 'product_colors', 'product_descriptions', 'product_media', 'product_channels'];

foreach ($tables as $table) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
    echo "$table count: " . $stmt->fetchColumn() . "\n";
}

// Sample Verify One Product
$pid = 1; // Assuming 1 exists, or pick one
echo "\n--- Data for Product ID $pid ---\n";
$base = $pdo->query("SELECT * FROM product_base WHERE id=$pid")->fetch(PDO::FETCH_ASSOC);
print_r($base);

$price = $pdo->query("SELECT * FROM product_prices WHERE product_id=$pid")->fetch(PDO::FETCH_ASSOC);
echo "Price: " . ($price['price'] ?? 'N/A') . "\n";

$sizes = $pdo->query("SELECT * FROM product_sizes WHERE product_id=$pid")->fetchAll(PDO::FETCH_COLUMN, 1);
echo "Sizes: " . implode(', ', $sizes) . "\n";

$colors = $pdo->query("SELECT * FROM product_colors WHERE product_id=$pid")->fetchAll(PDO::FETCH_COLUMN, 1);
echo "Colors: " . implode(', ', $colors) . "\n";
?>
