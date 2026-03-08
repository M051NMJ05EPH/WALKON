<?php
/**
 * Create Wishlist Table
 */
include 'config.php';

try {
    // Create wishlist table
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, product_id),
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Wishlist Table Created - WALKON</title>
        <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap' rel='stylesheet'>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit',sans-serif; }
            body { background:#030712; color:#fff; min-height:100vh; display:flex; align-items:center; justify-content:center; }
            .card { background:#111827; padding:60px; border-radius:32px; text-align:center; max-width:500px; border:1px solid rgba(255,255,255,0.08); }
            .icon { width:100px; height:100px; background:rgba(16,185,129,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px; font-size:3rem; color:#10b981; }
            h1 { font-size:2rem; margin-bottom:15px; }
            .btn { display:inline-block; padding:15px 35px; background:#10b981; color:white; text-decoration:none; border-radius:50px; font-weight:600; margin:10px; transition:0.3s; }
            .btn:hover { background:#059669; transform:translateY(-3px); }
        </style>
    </head>
    <body>
        <div class='card'>
            <div class='icon'>❤️</div>
            <h1>Wishlist Table Created!</h1>
            <p style='color:#9ca3af; margin-bottom:30px;'>The wishlist feature is now ready to use.</p>
            <a href='shop.php' class='btn'>Browse Shop</a>
            <a href='index.php' class='btn' style='background:#374151;'>Home</a>
        </div>
    </body>
    </html>";

} catch (PDOException $e) {
    echo "<div style='padding:40px; text-align:center; font-family:sans-serif; background:#030712; color:white; min-height:100vh;'>
            <h2 style='color:#ef4444;'>Error</h2>
            <p style='color:#9ca3af;'>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}
?>
