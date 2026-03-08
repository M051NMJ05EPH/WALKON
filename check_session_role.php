<?php
session_start();

echo "=== Session Role Check ===\n\n";

if (isset($_SESSION['user_id'])) {
    echo "Logged in as: {$_SESSION['first_name']} {$_SESSION['last_name']}\n";
    echo "Email: {$_SESSION['email']}\n";
    echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
} else {
    echo "Not logged in. Please login at login.php\n";
}
?>
