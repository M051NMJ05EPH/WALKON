<?php
include 'config.php';
try {
    $ids = [126, 129];
    foreach ($ids as $pid) {
        $stmt = $pdo->prepare("SELECT product_id, url FROM product_media WHERE product_id = ? AND is_primary = 1");
        $stmt->execute([$pid]);
        $res = $stmt->fetch();
        echo "Product $pid URL: " . ($res ? $res['url'] : "None") . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
