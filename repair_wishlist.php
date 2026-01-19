<?php
include 'config.php';

try {
    $sql = file_get_contents('update_wishlist_schema.sql');
    $pdo->exec($sql);
    echo "Wishlist table created or verified successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
