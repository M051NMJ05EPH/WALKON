<?php
include 'config.php';

echo "=== Seeding Sample Users for All Roles ===\n\n";

$sample_users = [
    [
        'email' => 'owner@walkon.com',
        'password' => 'Owner@123',
        'first_name' => 'Store',
        'last_name' => 'Owner',
        'role' => 'store_owner'
    ],
    [
        'email' => 'staff@walkon.com',
        'password' => 'Staff@123',
        'first_name' => 'Store',
        'last_name' => 'Staff',
        'role' => 'staff'
    ]
];

foreach ($sample_users as $user) {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$user['email']]);
    
    if ($stmt->fetch()) {
        echo "✓ User {$user['email']} already exists\n";
    } else {
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, is_verified, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([
            $user['first_name'],
            $user['last_name'],
            $user['email'],
            $hashed_password,
            $user['role']
        ]);
        echo "✓ Created {$user['role']}: {$user['email']} (Password: {$user['password']})\n";
    }
}

echo "\n=== Sample Users Creation Complete ===\n\n";

// Display all users
echo "Current Users:\n";
$stmt = $pdo->query("SELECT id, email, first_name, last_name, role FROM users ORDER BY role, id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    echo "  [{$user['role']}] {$user['first_name']} {$user['last_name']} - {$user['email']}\n";
}
?>
