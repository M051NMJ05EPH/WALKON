<?php
include 'config.php';

try {
    echo "<h2>Google Schema Fixer</h2>";
    echo "<p>Checking 'users' table for 'google_id'...</p>";

    // Get existing columns
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Check google_id
    if (!in_array('google_id', $columns)) {
        try {
            $sql = "ALTER TABLE users ADD COLUMN google_id VARCHAR(255) AFTER password";
            $pdo->exec($sql);
            echo "<div style='color:green'>Success: Added 'google_id' column to users table.</div>";
        } catch (Exception $e) {
            echo "<div style='color:red'>Error adding column: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div style='color:blue'>'google_id' column already exists.</div>";
    }

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Database Error: " . $e->getMessage() . "</h3>";
}
?>
