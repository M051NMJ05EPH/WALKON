<?php
require 'config.php';

try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count, SUM(total_price) as revenue FROM orders GROUP BY status");
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Order Stats:\n";
    foreach ($stats as $s) {
        echo "- {$s['status']}: {$s['count']} orders (Total: ₹{$s['revenue']})\n";
    }
    
    $total = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    echo "\nTotal Orders: $total\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
