<?php
include 'config.php';

try {
    echo "<h1>📊 Product Distribution Verification</h1>";
    
    // Check total products
    $total = $pdo->query("SELECT COUNT(*) FROM product_base")->fetchColumn();
    echo "<p>Total Products in Database: <strong>$total</strong></p>";

    // Breakdown by date
    $stmt = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM product_base GROUP BY DATE(created_at) ORDER BY date ASC");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; max-width: 600px; font-family: sans-serif;'>";
    echo "<tr style='background: #f3f4f6;'><th>Date</th><th>Product Count</th></tr>";
    
    foreach ($results as $row) {
        $date = $row['date'];
        $count = $row['count'];
        $highlight = (strtotime($date) >= strtotime('2026-01-25') && strtotime($date) <= strtotime('2026-01-28')) ? "style='background: #ecfdf5;'" : "";
        
        echo "<tr $highlight><td>$date</td><td>$count</td></tr>";
    }
    echo "</table>";

    echo "<p style='margin-top: 20px;'><a href='seed_products_daily.php' style='color: #10b981;'>Run Seeder Again</a> | <a href='index.php'>Go Home</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
