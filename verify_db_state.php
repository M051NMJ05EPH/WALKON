<?php
include 'config.php';

echo "<h1>WALKON Database State Verification</h1>";

function checkTable($pdo, $tableName) {
    echo "<h2>Table: $tableName</h2>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tableName");
        $count = $stmt->fetchColumn();
        echo "<p>Total Records: <strong>$count</strong></p>";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT * FROM $tableName LIMIT 15");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr>";
            foreach (array_keys($rows[0]) as $key) echo "<th>$key</th>";
            echo "</tr>";
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $val) echo "<td>" . htmlspecialchars($val) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }
}

checkTable($pdo, 'categories');
checkTable($pdo, 'sub_categories');
checkTable($pdo, 'brands');
checkTable($pdo, 'marketplaces');
checkTable($pdo, 'sellers');

echo "<hr><p>Verification Complete.</p>";
?>
