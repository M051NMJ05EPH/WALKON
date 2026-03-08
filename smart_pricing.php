<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Get the actual seller_id for this user
$stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
$stmt_seller->execute([$email]);
$seller = $stmt_seller->fetch();
$seller_id = $seller ? $seller['id'] : -1;

$message = "";

// ---------------------------------------------------------
// 1. DATABASE COLUMNS: Handled by add_smart_pricing.sql
// ---------------------------------------------------------

// ---------------------------------------------------------
// 2. HANDLE UPDATES
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_pricing'])) {
        $p_id = $_POST['product_id'];
        $min = !empty($_POST['min_price']) ? $_POST['min_price'] : NULL;
        $max = !empty($_POST['max_price']) ? $_POST['max_price'] : NULL;
        $status = isset($_POST['enabled']) ? 1 : 0;
        
        // Simple security check: make sure product belongs to user (via product_base join technically, but here we assume id is valid if fetched. 
        // For robustness, check if product_id is owned by seller first, but for now just update product_prices linked to this product)
        
        $stmt_check = $pdo->prepare("SELECT id FROM product_base WHERE id = ? AND seller_id = ?");
        $stmt_check->execute([$p_id, $seller_id]);
        if ($stmt_check->fetch()) {
             $stmt = $pdo->prepare("UPDATE product_prices SET min_price = ?, max_price = ?, smart_pricing_status = ? WHERE product_id = ?");
             if ($stmt->execute([$min, $max, $status, $p_id])) {
                 $message = "Pricing rules updated successfully!";
             } else {
                 $message = "Error updating pricing.";
             }
        } else {
            $message = "Unauthorized product access.";
        }
    }
}

// ---------------------------------------------------------
// 3. FETCH PRODUCTS (Normalized Schema)
// ---------------------------------------------------------
$stmt = $pdo->prepare("
    SELECT pb.id, pb.name as product_name, ps.sku, pp.price, 
           pp.min_price, pp.max_price, pp.smart_pricing_status,
           (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as image_url
    FROM product_base pb
    JOIN product_prices pp ON pb.id = pp.product_id
    JOIN product_skus ps ON pb.id = ps.product_id
    WHERE pb.seller_id = ?
    ORDER BY pb.created_at DESC
");
$stmt->execute([$seller_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Recent Logs
$log_stmt = $pdo->prepare("SELECT sl.*, pb.name 
                          FROM smart_pricing_log sl 
                          JOIN product_base pb ON sl.product_id = pb.id 
                          WHERE sl.seller_id = ? 
                          ORDER BY sl.created_at DESC LIMIT 10");
$log_stmt->execute([$seller_id]);
$logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Pricing Manager - WALKON Premium</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --dark-bg: #0B0F19;
            --dark-card: rgba(21, 27, 43, 0.7);
            --dark-border: rgba(42, 50, 65, 0.5);
            --text-main: #F1F5F9;
            --text-muted: #94A3B8;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        
        body { 
            font-family: 'Outfit', sans-serif;
            background: var(--dark-bg);
            color: var(--text-main);
            line-height: 1.6;
            background-image: radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.05) 0%, transparent 50%),
                              radial-gradient(circle at 100% 100%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: rgba(11, 15, 25, 0.8);
            backdrop-filter: blur(20px);
            position: fixed; width: 100%; top: 0; z-index: 1000;
            border-bottom: 1px solid var(--dark-border);
            height: 80px;
        }
        .nav-container {
            max-width: 1400px; margin: 0 auto; padding: 0 2rem; height: 100%;
            display: flex; justify-content: space-between; align-items: center;
        }
        .logo-box { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-box img { height: 32px; width: auto; }
        .brand-name { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: white; }
        .brand-name span { color: var(--primary); }
        .back-btn {
            color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 0.9rem;
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        .back-btn:hover { color: var(--primary); }

        .container { max-width: 1200px; margin: 120px auto 60px; padding: 0 2rem; }

        .header-content { margin-bottom: 40px; }
        .header-content h1 { 
            font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 10px;
        }
        .header-content p { color: var(--text-muted); font-size: 1.1rem; }

        .card { 
            background: var(--dark-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--dark-border);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }

        /* Pricing Table */
        .pricing-table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        th { text-align: left; padding: 15px 20px; color: var(--text-muted); font-weight: 500; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        tr.product-row { background: var(--glass); transition: 0.3s; }
        tr.product-row:hover { background: rgba(255,255,255,0.05); }
        td { padding: 20px; vertical-align: middle; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); }
        td:first-child { border-left: 1px solid var(--glass-border); border-radius: 12px 0 0 12px; }
        td:last-child { border-right: 1px solid var(--glass-border); border-radius: 0 12px 12px 0; }

        .product-info { display: flex; align-items: center; gap: 15px; }
        .product-img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; border: 1px solid var(--dark-border); }
        .name-sku { display: flex; flex-direction: column; }
        .p-name { font-weight: 600; color: white; font-size: 1rem; }
        .p-sku { font-size: 0.8rem; color: var(--text-muted); }

        .current-price { font-weight: 700; color: var(--primary); font-size: 1.1rem; }

        .input-group { position: relative; width: 120px; }
        .input-group span { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem; }
        .price-input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--dark-border);
            color: white; padding: 10px 10px 10px 25px; border-radius: 8px; font-size: 0.9rem; outline: none; transition: 0.3s;
        }
        .price-input:focus { border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary-glow); }

        /* Custom Toggle */
        .switch { position: relative; display: inline-block; width: 48px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #334155; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(22px); }

        .btn-save {
            background: var(--primary); color: #000; border: none; padding: 10px 20px; border-radius: 10px;
            font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 0.9rem;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 5px 15px var(--primary-glow); }
        
        .btn-run {
            background: transparent; border: 1px solid var(--primary); color: var(--primary);
            padding: 12px 24px; border-radius: 50px; font-weight: 600; cursor: pointer; font-size: 0.95rem;
            display: flex; align-items: center; gap: 10px; transition: 0.3s;
        }
        .btn-run:hover { background: var(--primary); color: #000; }

        .alert { 
            background: rgba(16, 185, 129, 0.1); border: 1px solid var(--primary); color: var(--primary);
            padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;
        }

        /* History Section */
        .history-item {
            display: grid; grid-template-columns: 100px 1fr 120px 120px 100px;
            padding: 15px 20px; border-bottom: 1px solid var(--glass-border); align-items: center;
        }
        .history-item:last-child { border-bottom: none; }
        .hist-time { color: var(--text-muted); font-size: 0.85rem; }
        .hist-name { font-weight: 500; color: white; }
        .hist-old { color: var(--text-muted); text-decoration: line-through; font-size: 0.9rem; }
        .hist-new { font-weight: 700; color: var(--primary); }
        .hist-change { text-align: right; font-weight: 600; }

        @media (max-width: 992px) {
            .history-item { grid-template-columns: 1fr; gap: 10px; padding: 20px; }
            .hist-change { text-align: left; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo-box">
                <img src="assets/shoe_logo_green.png" alt="WalkOn">
                <div class="brand-name">Walk<span>on</span></div>
            </a>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="header-content" style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1>Auto Pricing Engine</h1>
                <p>Optimize your revenue with real-time algorithmic price adjustments.</p>
            </div>
            <button class="btn-run" onclick="runSmartPricing()">
                <i class="fas fa-bolt"></i> Run Engine Now
            </button>
        </div>

        <?php if($message): ?>
            <div class="alert">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="pricing-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product Details</th>
                            <th>Current</th>
                            <th>Floor Price</th>
                            <th>Ceiling Price</th>
                            <th>Auto Mode</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $p): 
                                $img = $p['image_url'] ? $p['image_url'] : 'https://via.placeholder.com/60?text=No+Img';
                            ?>
                            <tr class="product-row">
                                <td>
                                    <div class="product-info">
                                        <img src="<?php echo htmlspecialchars($img); ?>" class="product-img" alt="Product">
                                        <div class="name-sku">
                                            <span class="p-name"><?php echo htmlspecialchars($p['product_name']); ?></span>
                                            <span class="p-sku"><?php echo htmlspecialchars($p['sku']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="current-price">₹<?php echo number_format((float)$p['price']); ?></span></td>
                                
                                <form method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="update_pricing" value="1">
                                    
                                    <td>
                                        <div class="input-group">
                                            <span>₹</span>
                                            <input type="number" step="0.01" name="min_price" class="price-input" value="<?php echo $p['min_price']; ?>" placeholder="Min">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span>₹</span>
                                            <input type="number" step="0.01" name="max_price" class="price-input" value="<?php echo $p['max_price']; ?>" placeholder="Max">
                                        </div>
                                    </td>
                                    <td>
                                        <label class="switch">
                                            <input type="checkbox" name="enabled" <?php echo $p['smart_pricing_status'] ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: right;">
                                        <button type="submit" class="btn-save">Update</button>
                                    </td>
                                </form>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 60px; color: var(--text-muted);">
                                    No products found. Start by <a href="add_listing.php" style="color: var(--primary);">adding a listing</a>.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Log -->
        <h2 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; margin-bottom: 20px;">
            <i class="fas fa-chart-line" style="color: var(--primary); margin-right: 12px;"></i> Recent Market Adjustments
        </h2>
        <div class="card" style="padding: 10px;">
            <?php if (count($logs) > 0): ?>
                <?php foreach ($logs as $log): 
                    $diff = $log['new_price'] - $log['old_price'];
                    $is_up = $diff >= 0;
                    $color = $is_up ? '#10b981' : '#ef4444';
                    $icon = $is_up ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                ?>
                    <div class="history-item">
                        <span class="hist-time"><?php echo date('H:i A', strtotime($log['created_at'])); ?></span>
                        <span class="hist-name"><?php echo htmlspecialchars($log['name']); ?></span>
                        <span class="hist-old">₹<?php echo number_format($log['old_price']); ?></span>
                        <span class="hist-new">₹<?php echo number_format($log['new_price']); ?></span>
                        <span class="hist-change" style="color: <?php echo $color; ?>">
                            <i class="fas <?php echo $icon; ?>"></i>
                            <?php echo $is_up ? '+' : '-'; ?>₹<?php echo number_format(abs($diff)); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                    The pricing engine is idling. Set boundaries and run the engine to see adjustments.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function runSmartPricing() {
            const btn = document.querySelector('.btn-run');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            fetch('reprice.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('Connection to pricing engine failed.');
                console.error(error);
            })
            .finally(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
