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
$success_msg = "";

// 1. Handle "Sync All" Action
if (isset($_POST['sync_all'])) {
    try {
        // Fetch all products for this seller
        $stmt_products = $pdo->prepare("SELECT id FROM product_base WHERE seller_id = ?");
        $stmt_products->execute([$user_id]); // This might need seller_id lookup first, but usually user_id = seller_id in simple setups. 
        // Wait, let's use the $seller_id we fetch later or fetching right now.
        
        $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $stmt_seller->execute([$email]);
        $seller = $stmt_seller->fetch();
        $s_id = $seller ? $seller['id'] : -1;

        if ($s_id != -1) {
            $prods = $pdo->prepare("SELECT pb.id, GROUP_CONCAT(pch.channel_name) as channels 
                                   FROM product_base pb 
                                   LEFT JOIN product_channels pch ON pb.id = pch.product_id
                                   WHERE pb.seller_id = ? 
                                   GROUP BY pb.id");
            $prods->execute([$s_id]);
            $list = $prods->fetchAll(PDO::FETCH_ASSOC);

            foreach ($list as $p) {
                if ($p['channels']) {
                    $channels = explode(',', $p['channels']);
                    foreach ($channels as $ch) {
                        $ch = trim($ch);
                        // Upsert or simple insert for log
                        // We use INSERT for history, but here we just want to set current expectation
                        $stmt = $pdo->prepare("INSERT INTO product_sync_logs (seller_id, product_id, channel, status) VALUES (?, ?, ?, 'pending')");
                        $stmt->execute([$s_id, $p['id'], $ch]);
                    }
                }
            }
            $success_msg = "Sync process started for " . count($list) . " products. Results will appear below.";
        }
    } catch (PDOException $e) {
        $success_msg = "Error starting sync: " . $e->getMessage();
    }
}

// Fetch Products - Get the actual seller_id for this user
try {
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller = $stmt_seller->fetch();
    
    $seller_id = $seller ? $seller['id'] : -1;

    // Updated for Normalized Schema + Latest Sync Logs
    // We fetch products and then use subqueries to get the LATEST status for each main channel
    $query = "SELECT pb.id, pb.name as product_name, ps.sku, pb.created_at,
                     (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
                     (SELECT status FROM product_sync_logs sl WHERE sl.product_id = pb.id AND sl.channel = 'Amazon' ORDER BY sync_date DESC LIMIT 1) as amazon_status,
                     (SELECT status FROM product_sync_logs sl WHERE sl.product_id = pb.id AND sl.channel = 'Shopify' ORDER BY sync_date DESC LIMIT 1) as shopify_status,
                     (SELECT status FROM product_sync_logs sl WHERE sl.product_id = pb.id AND sl.channel = 'Instagram' ORDER BY sync_date DESC LIMIT 1) as instagram_status,
                     (SELECT status FROM product_sync_logs sl WHERE sl.product_id = pb.id AND (sl.channel = 'TikTok Shop' OR sl.channel = 'TikTok') ORDER BY sync_date DESC LIMIT 1) as tiktok_status,
                     (SELECT status FROM product_sync_logs sl WHERE sl.product_id = pb.id AND sl.channel = 'eBay' ORDER BY sync_date DESC LIMIT 1) as ebay_status,
                     (SELECT MAX(sync_date) FROM product_sync_logs sl WHERE sl.product_id = pb.id) as last_sync
              FROM product_base pb
              LEFT JOIN product_skus ps ON pb.id = ps.product_id
              WHERE pb.seller_id = ?
              ORDER BY pb.created_at DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$seller_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Helper to render status icon
function renderStatus($status) {
    if (!$status) return '<span class="status-icon not-synced"><i class="fas fa-minus"></i></span>';
    
    switch ($status) {
        case 'success': return '<span class="status-icon synced" title="Successfully Synced"><i class="fas fa-check"></i></span>';
        case 'pending': return '<span class="status-icon" style="color:#007bff; background:#e7f1ff;" title="Pending Sync"><i class="fas fa-spinner fa-spin"></i></span>';
        case 'error':
        case 'failed':  return '<span class="status-icon error" title="Sync Error"><i class="fas fa-exclamation-triangle"></i></span>';
        default: return '<span class="status-icon not-synced"><i class="fas fa-question"></i></span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync Status - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding: 0; display: flex; flex-direction: column; min-height: 100vh; }

        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; flex: 1; width :100%; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 28px; }
        .nav-link { color: var(--text-light); text-decoration: none; margin-right: 20px; transition: 0.3s; }
        .nav-link:hover { color: var(--primary); }

        .card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 30px;
            overflow-x: auto;
        }

        .sync-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        .sync-table th {
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid var(--border);
            color: var(--text-light);
            font-weight: 500;
        }
        .sync-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .sync-table tr:last-child td { border-bottom: none; }
        .sync-table tr:hover { background-color: #f8f9fa; }

        .product-info { display: flex; align-items: center; gap: 15px; }
        .product-img {
            width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #eee;
        }
        .product-name { font-weight: 600; font-size: 15px; display: block; }
        .product-sku { font-size: 12px; color: var(--text-light); }

        .status-icon {
            font-size: 18px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }
        .synced { color: #28a745; background: #d4edda; }
        .not-synced { color: #ccc; background: #f1f1f1; }
        .error { color: #dc3545; background: #f8d7da; }

        .btn {
            background: var(--primary); color: white; border: none; padding: 10px 25px; border-radius: 30px; cursor: pointer; font-weight: 500;
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
        }
        .btn:hover { background: #218838; }
        .btn-outline {
            background: transparent; border: 2px solid var(--primary); color: var(--primary);
        }
        .btn-outline:hover { background: var(--primary); color: white; }

        .alert-success {
            background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px;
        }

        /* Footer Refined */
        footer {
          background: #05070A; border-top: 1px solid var(--border);
          padding: 80px 0 40px; color: #fff;
          margin-top: 50px;
          text-align: left;
           width: 100%;
        }
        .footer-container {
            max-width: 1400px; margin: 0 auto; padding: 0 2rem;
            display: grid; grid-template-columns: 1.2fr 2fr; gap: 4rem;
        }
        
        /* Footer Card */
        .footer-card {
            background: #0f131f; /* Darker card background */
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 24px; padding: 3rem;
            display: flex; flex-direction: column; gap: 1.5rem;
        }
        .footer-logo {
            display: flex; align-items: center; gap: 10px; text-decoration: none;
        }
        .brand-text {
            font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; line-height: 1;
        }
        .footer-desc {
            color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem;
        }
        
        .contact-info { display: flex; flex-direction: column; gap: 0.8rem; }
        .contact-item {
            display: flex; align-items: center; gap: 10px;
            color: #fff; font-size: 0.9rem;
        }
        .contact-item i { color: #10b981; width: 20px; }
        
        .social-links {
            display: flex; gap: 1rem; margin-top: 1rem;
        }
        .social-btn {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center;
            color: #94a3b8; text-decoration: none; transition: 0.3s;
        }
        .social-btn:hover {
            background: #10b981; color: #000; transform: translateY(-3px);
        }
        
        /* Footer Grid */
        .footer-nav-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
        }
        
        .footer-col h4 {
            color: #10b981; font-size: 0.85rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;
        }
        
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 1rem; }
        .footer-links a {
            color: #e2e8f0; text-decoration: none; font-size: 0.95rem;
            transition: 0.3s;
        }
        .footer-links a:hover { color: #10b981; padding-left: 5px; }

        @media (max-width: 1024px) {
            .footer-container { grid-template-columns: 1fr; }
            .footer-card { max-width: 500px; }
        }
        @media (max-width: 768px) {
            .footer-nav-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
                <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height: 36px; width: auto;">
                <span style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; color: var(--text-dark); letter-spacing: 0;">WALK<span style="color:var(--primary)">ON</span></span>
            </div>
            <h1 style="font-size: 28px; margin-top:5px;">Sync Status</h1>
            <p style="color:var(--text-light)">Real-time synchronization monitoring</p>
        </div>
        <div>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <form method="POST" style="display:inline;">
                <button type="submit" name="sync_all" class="btn"><i class="fas fa-sync"></i> Sync All Now</button>
            </form>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <table class="sync-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Product</th>
                    <th style="text-align:center;">Amazon</th>
                    <th style="text-align:center;">Shopify</th>
                    <th style="text-align:center;">Instagram</th>
                    <th style="text-align:center;">TikTok Shop</th>
                    <th style="text-align:center;">eBay</th>
                    <th style="text-align:right;">Last Update</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $p): ?>
                        <?php 
                            $img_url = $p['primary_image'] ? $p['primary_image'] : 'https://via.placeholder.com/50?text=No+Img';
                            $sync_time = $p['last_sync'] ? date('M d, H:i', strtotime($p['last_sync'])) : 'Never';
                        ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <img src="<?php echo htmlspecialchars($img_url); ?>" class="product-img" alt="Product">
                                    <div>
                                        <span class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></span>
                                        <span class="product-sku"><?php echo htmlspecialchars($p['sku']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:center;"><?php echo renderStatus($p['amazon_status']); ?></td>
                            <td style="text-align:center;"><?php echo renderStatus($p['shopify_status']); ?></td>
                            <td style="text-align:center;"><?php echo renderStatus($p['instagram_status']); ?></td>
                            <td style="text-align:center;"><?php echo renderStatus($p['tiktok_status']); ?></td>
                            <td style="text-align:center;"><?php echo renderStatus($p['ebay_status']); ?></td>
                            <td style="text-align:right; color:var(--text-light); font-size:14px;">
                                <?php echo $sync_time; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 40px;">No products found yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>
  <div class="footer-container">
    <!-- Left Card -->
    <div class="footer-card">
      <a href="index.php" class="footer-logo">
         <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height: 32px;">
         <div class="brand-text"><span style="color:#fff">WALK</span><span style="color:#10b981">ON</span></div>
      </a>
      <p class="footer-desc">Elevating the global footwear industry with intelligent multi-channel technology. One inventory, infinite possibilities.</p>
      
      <div class="contact-info">
        <div class="contact-item"><i class="fas fa-envelope"></i> support@walkon.com</div>
        <div class="contact-item"><i class="fas fa-phone"></i> +91 90745 85775</div>
        <div class="contact-item"><i class="fas fa-map-marker-alt"></i> Kottayam, Kerala, India</div>
      </div>

      <div class="social-links">
        <a href="#" class="social-btn"><i class="fas fa-phone-alt"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-whatsapp"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

    <!-- Right Columns -->
    <div class="footer-nav-grid">
      <div class="footer-col">
        <h4>NAVIGATION</h4>
        <ul class="footer-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="index.php#categories">Categories</a></li>
          <li><a href="index.php#features">Features</a></li>
          <li><a href="marketplaces.php">Marketplace</a></li>
          <li><a href="shop.php">Shop</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>SHOES</h4>
        <ul class="footer-links">
           <li><a href="shop.php">All Products</a></li>
           <li><a href="shop.php?category=1">Boots</a></li>
           <li><a href="shop.php?category=2">Formal Shoes</a></li>
           <li><a href="shop.php?category=3">Running Shoes</a></li>
           <li><a href="shop.php?category=4">Sandals & Slides</a></li>
           <li><a href="shop.php?category=5">Sneakers</a></li>
           <li><a href="shop.php?category=6">Sports</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>BRANDS</h4>
        <ul class="footer-links">
          <li><a href="shop.php">All Brands</a></li>
          <li><a href="shop.php?brand=1">adidas</a></li>
          <li><a href="shop.php?brand=2">ASICS</a></li>
          <li><a href="shop.php?brand=3">Bata</a></li>
          <li><a href="shop.php?brand=4">Clarks</a></li>
          <li><a href="shop.php?brand=5">Converse</a></li>
          <li><a href="shop.php?brand=6">Crocs</a></li>
          <li><a href="shop.php?brand=7">Dr. Martens</a></li>
          <li><a href="shop.php?brand=8">New Balance</a></li>
        </ul>
      </div>
    </div>
  </div>
</footer>

</body>
</html>
