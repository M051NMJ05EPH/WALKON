<?php
include __DIR__ . '/../../config.php';

echo "Seeding users for all roles...\n\n";

// Define users for each role
$users = [
    [
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'admin@walkon.com',
        'password' => 'Admin@123',
        'role' => 'admin'
    ],
    [
        'first_name' => 'Store',
        'last_name' => 'Owner',
        'email' => 'owner@walkon.com',
        'password' => 'Owner@123',
        'role' => 'store_owner'
    ],
    [
        'first_name' => 'John',
        'last_name' => 'Entrepreneur',
        'email' => 'entrepreneur@walkon.com',
        'password' => 'Entre@123',
        'role' => 'entrepreneur'
    ],
    [
        'first_name' => 'Support',
        'last_name' => 'Staff',
        'email' => 'staff@walkon.com',
        'password' => 'Staff@123',
        'role' => 'staff'
    ],
    [
        'first_name' => 'Jane',
        'last_name' => 'Customer',
        'email' => 'customer@walkon.com',
        'password' => 'Customer@123',
        'role' => 'customer'
    ],
    // Additional customers for variety
    [
        'first_name' => 'Michael',
        'last_name' => 'Smith',
        'email' => 'michael.smith@example.com',
        'password' => 'Customer@123',
        'role' => 'customer'
    ],
    [
        'first_name' => 'Sarah',
        'last_name' => 'Johnson',
        'email' => 'sarah.johnson@example.com',
        'password' => 'Customer@123',
        'role' => 'customer'
    ]
];

try {
    $pdo->beginTransaction();
    
    $inserted = 0;
    $updated = 0;
    $skipped = 0;
    
    foreach ($users as $user) {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id, role FROM users WHERE email = ?");
        $stmt->execute([$user['email']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing user's role if different
            if ($existing['role'] !== $user['role']) {
                $stmt = $pdo->prepare("UPDATE users SET role = ?, first_name = ?, last_name = ? WHERE email = ?");
                $stmt->execute([$user['role'], $user['first_name'], $user['last_name'], $user['email']]);
                echo "✓ Updated: {$user['email']} (Role: {$user['role']})\n";
                $updated++;
            } else {
                echo "- Skipped: {$user['email']} (Already exists)\n";
                $skipped++;
            }
        } else {
            // Insert new user
            $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (first_name, last_name, email, password, role, is_verified, created_at) 
                 VALUES (?, ?, ?, ?, ?, 1, NOW())"
            );
            $stmt->execute([
                $user['first_name'],
                $user['last_name'],
                $user['email'],
                $hashed_password,
                $user['role']
            ]);
            echo "✓ Created: {$user['email']} (Role: {$user['role']}, Password: {$user['password']})\n";
            $inserted++;
        }
    }
    
    $pdo->commit();
    
    echo "\n=== Summary ===\n";
    echo "Inserted: $inserted users\n";
    echo "Updated: $updated users\n";
    echo "Skipped: $skipped users\n";
    echo "Total: " . count($users) . " users\n\n";
    
    // Display all users by role
    echo "=== All Users by Role ===\n";
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, role FROM users ORDER BY role, id");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $currentRole = '';
    foreach ($allUsers as $u) {
        if ($currentRole !== $u['role']) {
            $currentRole = $u['role'];
            echo "\n" . strtoupper($currentRole) . ":\n";
        }
        echo "  - {$u['first_name']} {$u['last_name']} ({$u['email']}) [ID: {$u['id']}]\n";
    }
    
} catch (PDOException $e) {
    $pdo->rollBack();
    die("\nError: " . $e->getMessage() . "\n");
}
?>
