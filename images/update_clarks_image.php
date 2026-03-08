<?php
include 'config.php';
try {
    $pid = 121;
    $new_url = 'uploads/clarks_craftmaster.png';
    $stmt = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ? AND is_primary = 1");
    $stmt->execute([$new_url, $pid]);
    echo "Successfully updated Clarks CraftMaster II image.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
