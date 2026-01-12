<?php
include 'config.php';

try {
    $images = [
        'uploads/air_jordan_green.png',
        'uploads/air_jordan_top.png',
        'uploads/air_jordan_back.png',
        'uploads/air_jordan_sole.png',
        'uploads/air_jordan_lifestyle.png'
    ];
    $images_json = json_encode($images);
    
    // Update the first few products to have this image for demonstration
    $stmt = $pdo->prepare("UPDATE products SET images = ? LIMIT 2");
    $stmt->execute([$images_json]);
    
    echo "Sample images updated successfully! You can now see the multi-angle Air Jordan images in your product details gallery.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
