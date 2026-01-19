<?php
include 'config.php';
try {
    $updates = [
        126 => 'uploads/reebok_nano_x3_alt.jpg', // Reebok Nano X3
        129 => 'uploads/timberland_6inch_alt.jpg' // Timberland 6-Inch Premium
    ];
    
    foreach ($updates as $pid => $url) {
        $stmt = $pdo->prepare("UPDATE product_media SET url = ? WHERE product_id = ? AND is_primary = 1");
        $stmt->execute([$url, $pid]);
        echo "Updated Product ID $pid to $url\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
