<?php
include 'config.php';

try {
    // Read the SQL file
    $sql = file_get_contents('create_marketplace_table.sql');
    
    // Execute the SQL
    $pdo->exec($sql);
    
    echo "✅ Marketplace table created and populated successfully!<br>";
    
    // Verify the data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM marketplaces");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Total marketplaces: " . $result['count'] . "<br><br>";
    
    // Display all marketplaces
    $stmt = $pdo->query("SELECT * FROM marketplaces ORDER BY display_order");
    $marketplaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Marketplaces:</h3>";
    echo "<ul>";
    foreach ($marketplaces as $m) {
        echo "<li><strong>{$m['name']}</strong> - {$m['description']}</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
