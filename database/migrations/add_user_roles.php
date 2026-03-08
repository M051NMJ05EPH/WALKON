<?php
include __DIR__ . '/../../config.php';

echo "Adding 'role' column to users table...\n";

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "Column 'role' already exists.\n";
    } else {
        // Add the column
        $sql = "ALTER TABLE users ADD COLUMN role ENUM('admin', 'store_owner', 'inventory_manager', 'staff') NOT NULL DEFAULT 'store_owner' AFTER email";
        $pdo->exec($sql);
        echo "Successfully added 'role' column.\n";
    }

    // Verify
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($col);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
