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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pricing - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #28a745; --bg: #f8f9fa; --text: #333; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg); color: var(--text); padding: 40px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px; }
        .header h1 { font-size: 28px; display:flex; align-items:center; gap:10px; }
        .back-link { color: #666; text-decoration: none; font-weight: 500; }
        
        .card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 15px; color: #666; font-weight: 600; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        .product-info { display: flex; align-items: center; gap: 15px; }
        .product-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #eee; }
        
        .form-control {
            padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; width: 100px;
        }
        
        .btn-save {
            background: var(--primary); color: white; border: none; padding: 8px 20px; border-radius: 20px; cursor: pointer; font-weight: 500; transition: 0.3s;
        }
        .btn-save:hover { background: #218838; }
        
        /* Toggle Switch */
        .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(20px); }
        
        .alert { padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-tags" style="color:var(--primary);"></i> Smart Pricing Manager</h1>
        <div>
            <button class="btn-save" onclick="runSmartPricing()" style="background:#007bff; margin-right:10px;">Run Smart Pricing Now</button>
            <a href="dashboard.php" class="back-link">Back to Dashboard</a>
        </div>
    </div>

    <?php if($message): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <p style="margin-bottom:20px; color:#666;">
            Set your minimum and maximum price boundaries. Our system will automatically adjust prices within this range to stay competitive.
        </p>
        
        <?php if (count($products) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Current Price</th>
                        <th>Min Price</th>
                        <th>Max Price</th>
                        <th>Smart Pricing</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): 
                        $img = $p['image_url'] ? $p['image_url'] : 'https://via.placeholder.com/50';
                    ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="<?php echo htmlspecialchars($img); ?>" class="product-img" alt="img">
                                <div>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($p['product_name']); ?></div>
                                    <div style="font-size:12px; color:#888;">SKU: <?php echo htmlspecialchars($p['sku']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>₹<?php echo number_format((float)$p['price'], 2); ?></td>
                        
                        <!-- Form for each row -->
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <input type="hidden" name="update_pricing" value="1">
                            
                            <td>
                                <input type="number" step="0.01" name="min_price" class="form-control" placeholder="Min" value="<?php echo $p['min_price']; ?>">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="max_price" class="form-control" placeholder="Max" value="<?php echo $p['max_price']; ?>">
                            </td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="enabled" <?php echo $p['smart_pricing_status'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </td>
                            <td>
                                <button type="submit" class="btn-save">Save</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color:#888;">
                No products found. <a href="add_listing.php">Add a product first</a>.
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Activity Section -->
    <?php
    $log_stmt = $pdo->prepare("SELECT sl.*, pb.name 
                              FROM smart_pricing_log sl 
                              JOIN product_base pb ON sl.product_id = pb.id 
                              WHERE sl.seller_id = ? 
                              ORDER BY sl.created_at DESC LIMIT 10");
    $log_stmt->execute([$seller_id]);
    $logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="card" style="margin-top: 40px; background:white; padding:30px; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-history"></i> Recent Price Adjustments</h3>
        <?php if (count($logs) > 0): ?>
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #eee;">
                        <th style="padding:15px 0;">Time</th>
                        <th style="padding:15px 0;">Product</th>
                        <th style="padding:15px 0;">Old Price</th>
                        <th style="padding:15px 0;">New Price</th>
                        <th style="padding:15px 0; text-align:right;">Change</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): 
                        $diff = $log['new_price'] - $log['old_price'];
                        $color = $diff >= 0 ? '#28a745' : '#dc3545';
                        $icon = $diff >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                    ?>
                        <tr style="border-bottom: 1px solid #f9f9f9;">
                            <td style="padding:15px 0; font-size:14px; color:#666;"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></td>
                            <td style="padding:15px 0; font-weight:600;"><?php echo htmlspecialchars($log['name']); ?></td>
                            <td style="padding:15px 0; color:#888;">₹<?php echo number_format($log['old_price'], 2); ?></td>
                            <td style="padding:15px 0; font-weight:700; color:var(--primary);">₹<?php echo number_format($log['new_price'], 2); ?></td>
                            <td style="padding:15px 0; text-align:right; color:<?php echo $color; ?>; font-weight:600;">
                                <i class="fas <?php echo $icon; ?>" style="font-size:12px;"></i>
                                ₹<?php echo number_format(abs($diff), 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#888; text-align:center; padding:20px;">No recent adjustments found. Click "Run Engine" to start.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    function runSmartPricing() {
        const btn = document.querySelector('button[onclick="runSmartPricing()"]');
        const originalText = btn.innerText;
        btn.innerText = "Running...";
        btn.disabled = true;

        fetch('reprice.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('Error running smart pricing');
            console.error(error);
        })
        .finally(() => {
            btn.innerText = originalText;
            btn.disabled = false;
        });
    }
</script>
</body>
</html>
