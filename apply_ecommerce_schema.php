<?php
require_once 'config.php';

try {
    $sql = file_get_contents('add_ecommerce_tables.sql');
    
    // Split SQL by semicolon to execute one by one if needed, 
    // but PDO exec can handle multiple statements if configured.
    $pdo->exec($sql);
    
    echo "Successfully applied e-commerce database schema!\n";
    echo "Tables created: cart, wishlist, user_addresses, product_reviews, coupons.\n";
} catch (Exception $e) {
    echo "Error applying schema: " . $e->getMessage() . "\n";
}
?>
