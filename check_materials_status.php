<?php
include 'config.php';

try {
    echo "<h1>Database Diagnostic</h1>";
    
    // 1. Check Connected DB Name
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    echo "<p><strong>Connected Database:</strong> " . htmlspecialchars($dbName) . "</p>";

    // 2. List Tables
    echo "<h2>Tables in Database:</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    $materials_found = false;
    foreach ($tables as $table) {
        $color = ($table === 'materials') ? 'green' : 'black';
        $weight = ($table === 'materials') ? 'bold' : 'normal';
        if ($table === 'materials') $materials_found = true;
        
        echo "<li style='color:$color; font-weight:$weight'>$table</li>";
    }
    echo "</ul>";

    if ($materials_found) {
        echo "<h3 style='color:green'>SUCCESS: 'materials' table EXISTS.</h3>";
        
        // Count rows
        $count = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
        echo "<p>Rows in 'materials': $count</p>";
    } else {
        echo "<h3 style='color:red'>FAILURE: 'materials' table DOES NOT EXIST.</h3>";
        
        // Attempt forced creation again with verbose error reporting
        try {
            $sql = "CREATE TABLE materials (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE
            )";
            $pdo->exec($sql);
            echo "<p>Attempted to create table... Success?</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>Creation Error: " . $e->getMessage() . "</p>";
        }
    }

} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage();
}
?>
