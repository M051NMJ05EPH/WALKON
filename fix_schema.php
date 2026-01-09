<?php
include 'config.php';

try {
    echo "<h2>Database Schema Fixer</h2>";
    echo "<p>Checking 'users' table structure...</p>";

    // 1. Check if 'users' table exists
    $checkTable = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($checkTable->rowCount() == 0) {
        // Create table if it doesn't exist
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            verification_token VARCHAR(255),
            is_verified TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "<div style='color:green'>Created 'users' table successfully.</div>";
    } else {
        echo "Table 'users' exists. Checking columns...<br>";
        
        // Get existing columns
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $alterConfig = [];

        // Check first_name
        if (!in_array('first_name', $columns)) {
            $alterConfig[] = "ADD COLUMN first_name VARCHAR(50) NOT NULL AFTER id";
            echo " - Missing: first_name<br>";
        } else {
            echo " - Found: first_name<br>";
        }

        // Check last_name
        if (!in_array('last_name', $columns)) {
            $alterConfig[] = "ADD COLUMN last_name VARCHAR(50) NOT NULL AFTER first_name";
            echo " - Missing: last_name<br>";
        } else {
            echo " - Found: last_name<br>";
        }

        // Check verification_token
        if (!in_array('verification_token', $columns)) {
            $alterConfig[] = "ADD COLUMN verification_token VARCHAR(255) AFTER password";
            echo " - Missing: verification_token<br>";
        }

        // Check is_verified
        if (!in_array('is_verified', $columns)) {
            $alterConfig[] = "ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER verification_token";
            echo " - Missing: is_verified<br>";
        }

        // Apply changes
        if (!empty($alterConfig)) {
            foreach ($alterConfig as $sql) {
                $fullSql = "ALTER TABLE users " . $sql;
                try {
                    $pdo->exec($fullSql);
                    echo "<div style='color:green'>Executed: $fullSql</div>";
                } catch (Exception $e) {
                    echo "<div style='color:red'>Error executing '$fullSql': " . $e->getMessage() . "</div>";
                }
            }
            echo "<h3 style='color:green'>Table updated successfully!</h3>";
        } else {
            echo "<h3 style='color:blue'>Table is already up to date.</h3>";
        }
    }

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Database Error: " . $e->getMessage() . "</h3>";
}
?>
