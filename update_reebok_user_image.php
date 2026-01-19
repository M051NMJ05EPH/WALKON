<?php
include 'config.php';
try {
    $pid = 126; // Reebok Nano X3 ID
    $new_url = 'uploads/reebok_nano_x3_updated.png';
    $stmt = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ? AND is_primary = 1");
    $stmt->execute([$new_url, $pid]);
    echo "Successfully updated Reebok Nano X3 image to user-provided file.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
