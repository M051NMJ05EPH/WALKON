<?php
include 'config.php';
try {
    $pid = 130;
    $new_url = 'uploads/under_armour_curry_10.png';
    $stmt = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ? AND is_primary = 1");
    $stmt->execute([$new_url, $pid]);
    echo "Successfully updated Under Armour Curry 10 image.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
