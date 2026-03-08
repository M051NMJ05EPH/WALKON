<?php
include 'config.php';

$admin_email = 'admin@walkon.com';
$admin_pass = 'admin123';
$hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);

try {
    $pdo->exec("USE `walkon_shoes_v2` ");
    
    // Ensure role column exists (safety check)
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user'");

    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    $exists = $stmt->fetch();

    if (!$exists) {
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['System', 'Admin', $admin_email, $hashed_pass, 'admin', 1]);
        echo "<h1>✅ Admin Account Created</h1>";
        echo "<p>Email: <strong>$admin_email</strong></p>";
        echo "<p>Password: <strong>$admin_pass</strong></p>";
    } else {
        // Upgrade existing to admin just in case
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin', password = ? WHERE email = ?");
        $stmt->execute([$hashed_pass, $admin_email]);
        echo "<h1>✅ Admin Account Updated</h1>";
        echo "<p>Email: <strong>$admin_email</strong></p>";
        echo "<p>Password: <strong>$admin_pass</strong> (Reset to default)</p>";
    }

    echo "<hr><p><a href='login.php'>Go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
