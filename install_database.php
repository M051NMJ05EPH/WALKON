<?php
/**
 * WalkOn Database Installation Script
 * This script will create and set up the complete database structure
 */

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'walkon_shoes';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>WalkOn Database Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .step.error {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .step.warning {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        .step h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .step p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .success {
            color: #28a745;
            font-weight: 600;
        }
        .error-text {
            color: #dc3545;
            font-weight: 600;
        }
        .info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .info h4 {
            color: #0066cc;
            margin-bottom: 10px;
        }
        .info ul {
            margin-left: 20px;
            color: #333;
        }
        .info li {
            margin-bottom: 5px;
        }
        .btn {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class='container'>
    <h1>🚀 WalkOn Database Setup</h1>
    <p class='subtitle'>Setting up your database structure...</p>
";

try {
    // Step 1: Connect to MySQL server (without database)
    echo "<div class='step'>";
    echo "<h3>Step 1: Connecting to MySQL Server</h3>";
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "<p class='success'>✓ Successfully connected to MySQL server</p>";
    echo "</div>";

    // Step 2: Create Database
    echo "<div class='step'>";
    echo "<h3>Step 2: Creating Database</h3>";
    $sql = "CREATE DATABASE IF NOT EXISTS $db_name";
    if ($conn->query($sql) === TRUE) {
        echo "<p class='success'>✓ Database '<code>$db_name</code>' created successfully</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    echo "</div>";

    // Step 3: Select Database
    $conn->select_db($db_name);
    
    // Step 4: Create Tables
    echo "<div class='step'>";
    echo "<h3>Step 3: Creating Tables</h3>";
    
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            verification_token VARCHAR(255),
            is_verified TINYINT(1) DEFAULT 0,
            reset_token VARCHAR(255),
            reset_expires DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'sellers' => "CREATE TABLE IF NOT EXISTS sellers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            business_name VARCHAR(150),
            phone VARCHAR(20),
            address TEXT,
            city VARCHAR(50),
            state VARCHAR(50),
            country VARCHAR(50),
            postal_code VARCHAR(20),
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'products' => "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seller_id INT NOT NULL,
            product_name VARCHAR(200) NOT NULL,
            sku VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            category VARCHAR(50),
            price DECIMAL(10, 2) NOT NULL,
            min_price DECIMAL(10, 2),
            max_price DECIMAL(10, 2),
            quantity INT DEFAULT 0,
            channels TEXT,
            images TEXT,
            status VARCHAR(20) DEFAULT 'published',
            smart_pricing_status TINYINT(1) DEFAULT 0,
            views INT DEFAULT 0,
            sales INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
            INDEX idx_seller (seller_id),
            INDEX idx_sku (sku)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'orders' => "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seller_id INT NOT NULL,
            product_id INT NOT NULL,
            customer_name VARCHAR(100),
            customer_email VARCHAR(100),
            quantity INT NOT NULL,
            unit_price DECIMAL(10, 2) NOT NULL,
            total_amount DECIMAL(10, 2) NOT NULL,
            channel VARCHAR(50),
            order_status VARCHAR(50) DEFAULT 'pending',
            order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_seller (seller_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'sync_logs' => "CREATE TABLE IF NOT EXISTS sync_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seller_id INT NOT NULL,
            product_id INT,
            channel VARCHAR(50) NOT NULL,
            sync_type VARCHAR(50) NOT NULL,
            status VARCHAR(20) NOT NULL,
            message TEXT,
            sync_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
            INDEX idx_seller (seller_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'notifications' => "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'info',
            is_read TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    $created_count = 0;
    foreach ($tables as $table_name => $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "<p class='success'>✓ Table '<code>$table_name</code>' created</p>";
            $created_count++;
        } else {
            echo "<p class='error-text'>✗ Error creating table '$table_name': " . $conn->error . "</p>";
        }
    }
    echo "</div>";

    // Step 5: Summary
    echo "<div class='step'>";
    echo "<h3>Step 4: Setup Complete!</h3>";
    echo "<p class='success'>✓ Database setup completed successfully</p>";
    echo "<p>Created $created_count tables in the database</p>";
    echo "</div>";

    // Information Box
    echo "<div class='info'>";
    echo "<h4>📋 Database Information</h4>";
    echo "<ul>";
    echo "<li><strong>Database Name:</strong> <code>$db_name</code></li>";
    echo "<li><strong>Host:</strong> <code>$host</code></li>";
    echo "<li><strong>Tables Created:</strong> $created_count</li>";
    echo "<li><strong>Status:</strong> <span class='success'>Ready to use</span></li>";
    echo "</ul>";
    echo "</div>";

    echo "<div class='info'>";
    echo "<h4>🎯 Next Steps</h4>";
    echo "<ul>";
    echo "<li>Your database is now ready to use</li>";
    echo "<li>You can start using the WalkOn application</li>";
    echo "<li>Create an account or login to get started</li>";
    echo "<li>For security, consider deleting this installation file after setup</li>";
    echo "</ul>";
    echo "</div>";

    echo "<a href='Index.php' class='btn'>Go to Application →</a>";

    $conn->close();

} catch (Exception $e) {
    echo "<div class='step error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p class='error-text'>" . $e->getMessage() . "</p>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h4>💡 Troubleshooting Tips</h4>";
    echo "<ul>";
    echo "<li>Make sure XAMPP/MySQL is running</li>";
    echo "<li>Check your database credentials in <code>config.php</code></li>";
    echo "<li>Ensure you have proper permissions</li>";
    echo "<li>Try restarting MySQL service</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div>
</body>
</html>";
?>
