<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

echo "<h2>WALKON Database Debugger</h2>";

try {
    // 1. Database Connection Info
    $current_db = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "<b>Active Database:</b> $current_db <br><hr>";

    // 2. List all tables
    echo "<h3>Tables in Database:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul><hr>";

    // 3. Inspect Orders Table
    if (in_array('orders', $tables)) {
        echo "<h3>Inspect 'orders' Table:</h3>";
        $stmt = $pdo->query("DESCRIBE orders");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $val) echo "<td>" . ($val === null ? 'NULL' : $val) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'><b>ERROR:</b> 'orders' table does not exist!</p>";
    }

    // 4. Inspect Product Base (Dependency)
    if (in_array('product_base', $tables)) {
        echo "<h3>Inspect 'product_base' Table:</h3>";
        $stmt = $pdo->query("DESCRIBE product_base");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $val) echo "<td>" . ($val === null ? 'NULL' : $val) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<hr><p>End of Debug.</p>";

} catch (Exception $e) {
    echo "<p style='color:red;'><b>FATAL ERROR:</b> " . $e->getMessage() . "</p>";
}
?>
