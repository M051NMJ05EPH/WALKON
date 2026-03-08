<?php
include 'config.php';

try {
    echo "<h1>🛠️ Updating Orders Schema</h1>";
    
    // Add payment_status if not exists
    $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'paid', 'failed', 'refunded') DEFAULT 'unpaid' AFTER total_price");
    echo "✅ Added: payment_status<br>";
    
    // Add payment_link if not exists
    $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_link VARCHAR(500) DEFAULT NULL AFTER payment_status");
    echo "✅ Added: payment_link<br>";
    
    // Update existing orders with dummy payment links for demo purposes
    $pdo->exec("UPDATE orders SET payment_link = 'https://rzp.io/l/walkon-demo' WHERE payment_link IS NULL");
    echo "✅ Updated existing orders with demo payment links.<br>";

    echo "<h3>Schema Update Complete!</h3>";
    echo "<p><a href='my_orders.php'>Go back to Orders</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
