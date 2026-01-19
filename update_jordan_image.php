<?php
include 'config.php';
try {
    $pid = 118;
    $new_url = 'uploads/nike_air_jordan_1.png';
    $stmt = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ? AND is_primary = 1");
    $stmt->execute([$new_url, $pid]);
    echo "Successfully updated Nike Air Jordan 1 High image.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
