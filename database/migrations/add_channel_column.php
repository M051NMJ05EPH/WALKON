<?php
/**
 * Migration: Add 'channel' column to orders table
 * This allows tracking which marketplace orders come from
 */

include 'config.php';

try {
    // Check if column already exists
    $check = $pdo->query("SHOW COLUMNS FROM orders LIKE 'channel'");
    
    if ($check->rowCount() == 0) {
        // Add channel column to orders table
        $pdo->exec("ALTER TABLE orders ADD COLUMN channel VARCHAR(50) DEFAULT 'Direct' AFTER shipping_address");
        
        echo "<div style='padding:40px; text-align:center; font-family:Outfit,sans-serif; background:#030712; color:white; min-height:100vh;'>
                <div style='max-width:500px; margin:0 auto; background:#111827; padding:50px; border-radius:24px; border:1px solid rgba(255,255,255,0.08);'>
                    <div style='width:80px; height:80px; background:rgba(16,185,129,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;'>
                        <span style='font-size:2.5rem; color:#10b981;'>✓</span>
                    </div>
                    <h2 style='color:#10b981; margin-bottom:15px;'>Migration Successful!</h2>
                    <p style='color:#9CA3AF; margin-bottom:30px;'>The 'channel' column has been added to the orders table. You can now track orders by marketplace.</p>
                    <a href='my_orders.php' style='display:inline-block; padding:14px 28px; background:#10b981; color:white; text-decoration:none; border-radius:50px; font-weight:600;'>View Orders</a>
                </div>
              </div>";
    } else {
        echo "<div style='padding:40px; text-align:center; font-family:Outfit,sans-serif; background:#030712; color:white; min-height:100vh;'>
                <div style='max-width:500px; margin:0 auto; background:#111827; padding:50px; border-radius:24px; border:1px solid rgba(255,255,255,0.08);'>
                    <div style='width:80px; height:80px; background:rgba(59,130,246,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;'>
                        <span style='font-size:2.5rem; color:#3b82f6;'>ℹ</span>
                    </div>
                    <h2 style='color:#3b82f6; margin-bottom:15px;'>Already Up to Date</h2>
                    <p style='color:#9CA3AF; margin-bottom:30px;'>The 'channel' column already exists in the orders table.</p>
                    <a href='my_orders.php' style='display:inline-block; padding:14px 28px; background:#10b981; color:white; text-decoration:none; border-radius:50px; font-weight:600;'>View Orders</a>
                </div>
              </div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='padding:40px; text-align:center; font-family:Outfit,sans-serif; background:#030712; color:white; min-height:100vh;'>
            <div style='max-width:500px; margin:0 auto; background:#111827; padding:50px; border-radius:24px; border:1px solid rgba(255,255,255,0.08);'>
                <div style='width:80px; height:80px; background:rgba(239,68,68,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;'>
                    <span style='font-size:2.5rem; color:#ef4444;'>✕</span>
                </div>
                <h2 style='color:#ef4444; margin-bottom:15px;'>Migration Failed</h2>
                <p style='color:#9CA3AF; margin-bottom:30px;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
                <a href='dashboard.php' style='display:inline-block; padding:14px 28px; background:#374151; color:white; text-decoration:none; border-radius:50px; font-weight:600;'>Back to Dashboard</a>
            </div>
          </div>";
}
?>
