<?php
// Test file to check what's working
session_start();
include 'config.php';

echo "<h1>WALKON Debug Test</h1>";

// 1. Check database connection
try {
    $result = $pdo->query("SELECT 1")->fetch();
    echo "<p>✅ Database connection: OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// 2. Check session
if (isset($_SESSION['user_id'])) {
    echo "<p>✅ User logged in: " . $_SESSION['email'] . "</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
} else {
    echo "<p>❌ No user logged in</p>";
}

// 3. Check tables exist
$tables = ['marketplaces', 'seller_marketplaces', 'product_base', 'users', 'cart', 'wishlist'];
foreach ($tables as $table) {
    try {
        $pdo->query("SELECT 1 FROM $table LIMIT 1");
        echo "<p>✅ Table '$table': exists</p>";
    } catch (Exception $e) {
        echo "<p>❌ Table '$table': missing or error</p>";
    }
}

// 4. Check product count
try {
    $count = $pdo->query("SELECT COUNT(*) FROM product_base")->fetchColumn();
    echo "<p>Products in database: $count</p>";
} catch (Exception $e) {
    echo "<p>❌ Cannot count products: " . $e->getMessage() . "</p>";
}

// 5. Test links
echo "<hr><h2>Test Links</h2>";
echo '<p><a href="customer_dashboard.php">Customer Dashboard</a></p>';
echo '<p><a href="marketplaces.php">Marketplaces Hub</a></p>';
echo '<p><a href="profile.php">Profile Settings</a></p>';
echo '<p><a href="channel_settings.php?id=1">Channel Settings</a></p>';
?>
