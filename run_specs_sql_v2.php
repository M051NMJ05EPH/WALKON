<?php
include 'config.php';

$sql = file_get_contents('add_specs_tables.sql');

try {
    // Split by semicolon so we can execute statement by statement if needed, 
    // but PDO::exec usually handles multiple statements if driver supports it.
    // Let's try executing the whole block.
    $pdo->exec($sql);
    echo "Successfully executed add_specs_tables.sql\n";
    
    // Check materials
    $stmt = $pdo->query("SELECT * FROM materials");
    $mats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Materials count: " . count($mats) . "\n";
    
} catch (PDOException $e) {
    echo "Error executing SQL: " . $e->getMessage();
}
?>
