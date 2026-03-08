<?php
// Simulate the exact login process from login.php
session_start();
include 'config.php';

echo "=== Simulating Complete Login Flow ===\n\n";

$test_accounts = [
    ['email' => 'owner@walkon.com', 'password' => 'Owner@123', 'name' => 'Store Owner'],
    ['email' => 'staff@walkon.com', 'password' => 'Staff@123', 'name' => 'Staff']
];

foreach ($test_accounts as $account) {
    echo "Testing: {$account['name']} ({$account['email']})\n";
    
    $email = trim($account['email']);
    $password = $account['password'];
    
    // This is the EXACT query from login.php line 20
    $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "  ✓ User found\n";
        echo "  → Name: {$user['first_name']} {$user['last_name']}\n";
        echo "  → Role: {$user['role']}\n";
        
        if (password_verify($password, $user['password'])) {
            echo "  ✓ Password verified\n";
            
            // Simulate setting session (these would be set in login.php)
            echo "  → Would set session variables:\n";
            echo "     - \$_SESSION['user_id'] = {$user['id']}\n";
            echo "     - \$_SESSION['email'] = {$user['email']}\n";
            echo "     - \$_SESSION['first_name'] = {$user['first_name']}\n";
            echo "     - \$_SESSION['last_name'] = {$user['last_name']}\n";
            echo "     - \$_SESSION['role'] = {$user['role']}\n";
            echo "  → Would redirect to: dashboard.php\n";
            echo "  ✓ LOGIN SHOULD WORK\n";
        } else {
            echo "  ✗ Password verification FAILED\n";
        }
    } else {
        echo "  ✗ User NOT found in database\n";
    }
    echo "\n";
}

echo "=== Can you describe what error you're seeing when you try to login? ===\n";
echo "- Are you getting an error message?\n";
echo "- Does login.php show 'Invalid password!' or 'No account found'?\n";
echo "- Or does something else happen?\n";
?>
