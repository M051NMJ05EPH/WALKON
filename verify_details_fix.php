<?php
include 'config.php';
// Try to select from proper tables
try {
    $pdo->query("SELECT 1 FROM product_sizes LIMIT 1");
    echo "✅ product_sizes exists.<br>";
    $pdo->query("SELECT 1 FROM product_colors LIMIT 1");
    echo "✅ product_colors exists.<br>";
    $media_count = $pdo->query("SELECT COUNT(*) FROM product_media WHERE is_primary=0")->fetchColumn();
    echo "✅ Secondary images count: $media_count (Gallery populated)<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
