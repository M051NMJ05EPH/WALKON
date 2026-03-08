<?php
include __DIR__ . '/../../config.php';

echo "=== Creating User Management & Platform Control Tables ===\n\n";

try {
    // 1. User Activity Logs Table
    echo "Creating user_activity_logs table...\n";
    $sql_activity = "CREATE TABLE IF NOT EXISTS user_activity_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql_activity);
    echo "✓ user_activity_logs table created successfully\n\n";

    // 2. Store Settings Table
    echo "Creating store_settings table...\n";
    $sql_settings = "CREATE TABLE IF NOT EXISTS store_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        category VARCHAR(50),
        updated_by INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_setting_key (setting_key),
        FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql_settings);
    echo "✓ store_settings table created successfully\n\n";

    // 3. Staff Permissions Table
    echo "Creating staff_permissions table...\n";
    $sql_permissions = "CREATE TABLE IF NOT EXISTS staff_permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        permission_key VARCHAR(100) NOT NULL,
        is_granted BOOLEAN DEFAULT TRUE,
        granted_by INT,
        granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_permission_key (permission_key),
        UNIQUE KEY unique_user_permission (user_id, permission_key),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql_permissions);
    echo "✓ staff_permissions table created successfully\n\n";

    // 4. Add is_active column to users table if not exists
    echo "Updating users table...\n";
    $check_col = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($check_col->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER is_verified");
        echo "✓ Added is_active column to users table\n";
    } else {
        echo "✓ is_active column already exists\n";
    }

    // 5. Add last_login column to users table if not exists
    $check_login = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_login'");
    if ($check_login->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL AFTER is_active");
        echo "✓ Added last_login column to users table\n";
    } else {
        echo "✓ last_login column already exists\n";
    }
    echo "\n";

    // Seed default store settings
    echo "Seeding default store settings...\n";
    $default_settings = [
        ['setting_key' => 'store_name', 'setting_value' => 'WALKON Footwear Store', 'category' => 'business'],
        ['setting_key' => 'store_email', 'setting_value' => 'contact@walkon.com', 'category' => 'business'],
        ['setting_key' => 'store_phone', 'setting_value' => '+91 1234567890', 'category' => 'business'],
        ['setting_key' => 'tax_rate', 'setting_value' => '18', 'category' => 'financial'],
        ['setting_key' => 'currency', 'setting_value' => 'INR', 'category' => 'financial'],
        ['setting_key' => 'currency_symbol', 'setting_value' => '₹', 'category' => 'financial'],
        ['setting_key' => 'return_window_days', 'setting_value' => '30', 'category' => 'policy'],
        ['setting_key' => 'brand_color_primary', 'setting_value' => '#10b981', 'category' => 'branding'],
        ['setting_key' => 'brand_color_secondary', 'setting_value' => '#059669', 'category' => 'branding'],
    ];

    $stmt = $pdo->prepare("INSERT INTO store_settings (setting_key, setting_value, category) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($default_settings as $setting) {
        $stmt->execute([$setting['setting_key'], $setting['setting_value'], $setting['category']]);
    }
    echo "✓ Seeded " . count($default_settings) . " default settings\n\n";

    // Verify tables
    echo "=== Verification ===\n";
    $tables = ['user_activity_logs', 'store_settings', 'staff_permissions'];
    foreach ($tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() > 0) {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "✓ $table exists ($count rows)\n";
        } else {
            echo "✗ $table NOT FOUND\n";
        }
    }

    echo "\n=== Migration Complete ===\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
