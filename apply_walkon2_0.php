<?php
/**
 * Walkon2.0 Database Setup Script
 * Executes the SQL schema to create the database and tables.
 */

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // 1. Connect to MySQL (no DB selected)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Starting Walkon2.0 Database Setup</h2>";

    // 2. Read the SQL file
    $sqlFile = __DIR__ . '/walkon2_0_setup.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    
    // 3. Execute the SQL (multiple queries)
    // PDO::exec() handles one query at a time usually, but for schema creation 
    // we can use a simpler approach or execute it via a loop if needed.
    // However, some configurations allow multi-query.
    
    // To be safe and see progress, let's split by semicolon and execute individually
    // This is basic and might fail on complex triggers, but for simple schema it works.
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
            // Optionally identify the query type for better logging
            if (stripos($query, 'CREATE DATABASE') === 0) {
                echo "✅ Database created/checked.<br>";
            } elseif (stripos($query, 'CREATE TABLE') === 0) {
                // Extract table name for logging
                preg_match('/CREATE TABLE IF NOT EXISTS\s+`?([\w\.]+)`?/i', $query, $matches);
                $tableName = isset($matches[1]) ? $matches[1] : "A table";
                echo "✅ Table created: <strong>$tableName</strong><br>";
            }
        }
    }

    echo "<h3>Setup Complete! walkon2.0 is ready.</h3>";
    echo "<p><a href='Index.php'>Return to Home</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color:red;'>Setup Failed!</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
