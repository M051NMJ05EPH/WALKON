<?php
include 'config.php';

echo "=== Updating Users Table for Store Mapping ===\n\n";

try {
    // Add seller_id column to users table
    $check_col = $pdo->query("SHOW COLUMNS FROM users LIKE 'seller_id'");
    if ($check_col->rowCount() == 0) {
        echo "Adding seller_id column to users table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN seller_id INT AFTER role");
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT fk_user_seller FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE SET NULL");
        echo "✓ Added seller_id column and foreign key\n";
    } else {
        echo "✓ seller_id column already exists\n";
    }

    // Assign existing store_owners to their respective seller records if email matches
    echo "Syncing existing store owners with seller records...\n";
    $stmt = $pdo->query("SELECT id, email FROM users WHERE role = 'store_owner' AND seller_id IS NULL");
    $owners = $stmt->fetchAll();
    
    $update_stmt = $pdo->prepare("UPDATE users SET seller_id = ? WHERE id = ?");
    $seller_stmt = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    
    foreach ($owners as $owner) {
        $seller_stmt->execute([$owner['email']]);
        $seller = $seller_stmt->fetch();
        if ($seller) {
            $update_stmt->execute([$seller['id'], $owner['id']]);
            echo "✓ Linked owner {$owner['email']} to seller ID {$seller['id']}\n";
        }
    }

    echo "\n=== Migration Complete ===\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
