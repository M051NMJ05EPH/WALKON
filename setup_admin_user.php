<?php
include 'config.php';

// Check if running from CLI
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

$email = 'admin@walkon.com';
$password = 'Admin@123'; // Default password
$first_name = 'Super';
$last_name = 'Admin';

echo "Setting up Admin User ($email)...\n";

// Check if user exists
$stmt = $pdo->prepare("SELECT id, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Update existing user to admin
    $stmt = $pdo->prepare("UPDATE users SET role = 'admin', first_name = ?, last_name = ? WHERE id = ?");
    $stmt->execute([$first_name, $last_name, $user['id']]);
    echo "Existing user promoted to Admin.\n";
} else {
    // Create new admin user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin';
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, is_verified, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
    $stmt->execute([$first_name, $last_name, $email, $hashed_password, $role]);
    echo "New Admin user created.\n";
    echo "Password: $password\n";
}

// Verify
$stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$verified_user = $stmt->fetch(PDO::FETCH_ASSOC);

print_r($verified_user);
?>
