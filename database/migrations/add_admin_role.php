<?php
session_start();
include 'config.php';

try {
    $pdo->exec("USE `walkon_shoes_v2` ");
    echo "<h1>🔑 Role System Upgrade</h1>";

    // 1. Add role column to users
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user' AFTER is_verified");
    echo "✅ Column 'role' added to users table.<br>";

    // 2. Set current user as Admin (if logged in)
    if (isset($_SESSION['email'])) {
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
        $stmt->execute([$_SESSION['email']]);
        echo "✅ User <strong>" . htmlspecialchars($_SESSION['email']) . "</strong> promoted to Admin.<br>";
    } else {
        echo "ℹ️ No user in session. Please log in first to be promoted, or run manually for your email.<br>";
    }

    // 3. Promote specific email (optional safety)
    if (isset($_GET['promote'])) {
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
        $stmt->execute([$_GET['promote']]);
        echo "✅ User <strong>" . htmlspecialchars($_GET['promote']) . "</strong> promoted to Admin.<br>";
    }

    echo "<h3>System Ready!</h3>";
    echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
