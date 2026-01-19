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

try {
    // 1. Get seller record
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller = $stmt_seller->fetch();
    
    if (!$seller) {
        // Auto-fix: If seller doesn't exist, redirect to fix_backend or show error
        die("<div style='padding:40px; text-align:center; font-family:sans-serif;'>
                <h2>Account Sync Required</h2>
                <p>Your seller profile needs to be synchronized. Please run the backend fix to continue.</p>
                <a href='fix_backend.php' style='display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Run Backend Sync</a>
             </div>");
    }

    $seller_id = $seller['id'];

    $search = trim($_GET['search'] ?? '');
    $order_status = trim($_GET['order_status'] ?? '');
    $payment_status = trim($_GET['payment_status'] ?? '');
    $channel_filter = trim($_GET['channel'] ?? '');

    // Get unique statuses for filters
    $order_statuses = $pdo->prepare("SELECT DISTINCT order_status FROM orders WHERE seller_id = ? AND order_status IS NOT NULL");
    $order_statuses->execute([$seller_id]);
    $order_status_list = $order_statuses->fetchAll(PDO::FETCH_COLUMN);

    $payment_statuses = $pdo->prepare("SELECT DISTINCT payment_status FROM orders WHERE seller_id = ? AND payment_status IS NOT NULL");
    $payment_statuses->execute([$seller_id]);
    $payment_status_list = $payment_statuses->fetchAll(PDO::FETCH_COLUMN);

    // 2. Fetch orders with product details (Updated for Normalized Schema)
    $sql = "SELECT 
                o.*, 
                pb.name as product_name, 
                ps.sku,
                (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image
            FROM orders o 
            LEFT JOIN product_base pb ON o.product_id = pb.id
            LEFT JOIN product_skus ps ON pb.id = ps.product_id
            WHERE o.seller_id = ?";
    
    $params = [$seller_id];

    if ($search) {
        $sql .= " AND (o.id LIKE ? OR pb.name LIKE ? OR o.customer_name LIKE ? OR ps.sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($order_status) {
        $sql .= " AND o.order_status = ?";
        $params[] = $order_status;
    }
    if ($payment_status) {
        $sql .= " AND o.payment_status = ?";
        $params[] = $payment_status;
    }
    if ($channel_filter) {
        $sql .= " AND o.channel = ?";
        $params[] = $channel_filter;
    }

    $sql .= " ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    if ($e->getCode() == '42S02') { // Table not found
        die("<div style='padding:40px; text-align:center; font-family:sans-serif;'>
                <h2>Database Table Missing</h2>
                <p>The orders table is missing from your database.</p>
                <a href='fix_backend.php' style='display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Create Tables Now</a>
             </div>");
    }
    die("Error fetching orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders & Payment Status - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --secondary: #1e293b;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding: 0; display: flex; flex-direction: column; min-height: 100vh; }

        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; flex: 1; width :100%; }

        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { font-size: 28px; }
        
        .btn-back {
            background: #fff; color: var(--text-dark); padding: 10px 20px; border-radius: 30px; text-decoration: none; font-weight: 500; border: 1px solid var(--border);
        }

        .order-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-collapse: collapse;
        }
        .order-table th, .order-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .order-table th { background: #fcfcfc; font-weight: 600; color: #555; font-size: 14px; }
        
        .product-cell { display: flex; align-items: center; gap: 15px; }
        .product-img { width: 50px; height: 50px; border-radius: 6px; object-fit: cover; background: #eee; }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-paid { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #e2e3e5; color: #383d41; }
        
        .empty-state { text-align: center; padding: 60px; color: #888; background: white; border-radius: 15px; }

        /* Search and Filter Styles (Consistent with my_listings.php) */
        .search-container {
            margin-bottom: 40px;
            background: white;
            padding: 10px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .search-form {
            display: flex; 
            width: 100%; 
            gap: 10px;
            position: relative;
        }
        .search-input {
            flex-grow: 1;
            padding: 14px 20px;
            border: 1px solid #f0f0f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.3s;
            background: #fafafa;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
        }
        .btn-search {
            background: #1a1a1a;
            color: white;
            border: none;
            padding: 0 30px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-search:hover { background: #333; transform: translateY(-2px); }

        .filters-wrapper {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }
        .filter-group {
            display: flex;
            gap: 12px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .filter-select {
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
            background: #fafafa;
            font-size: 14px;
            font-family: inherit;
            color: #555;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 140px;
        }
        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.05);
            background: white;
        }

        /* Autocomplete Styles */
        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            z-index: 1000;
            margin-top: 8px;
            border: 1px solid #f0f0f0;
            display: none;
            overflow: hidden;
        }
        .suggestion-item {
            padding: 12px 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            border-bottom: 1px solid #f8f9fa;
        }
        .suggestion-item:last-child { border-bottom: none; }
        .suggestion-item:hover, .suggestion-item.active {
            background: #f0fff4;
            color: var(--primary);
        }
        .suggestion-item i { color: #ccc; font-size: 14px; }
        .suggestion-item.active i { color: var(--primary); }

        .btn-add {
            background: var(--primary); 
            color: white; 
            padding: 12px 24px; 
            border-radius: 12px; 
            text-decoration: none; 
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }
        .btn-add:hover { 
            background: #059669; 
            transform: translateY(-2px); 
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
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
            <h1 style="font-size: 28px; margin-top:5px;">Orders & Payment Status</h1>
            <p>Track your sales and payments</p>
        </div>
        <div>
            <a href="export_orders.php?<?php echo http_build_query($_GET); ?>" class="btn-add" style="background:#17a2b8; box-shadow: 0 4px 12px rgba(23, 162, 184, 0.2); margin-right: 10px;">
                <i class="fas fa-file-export"></i> Download Report
            </a>
            <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

    <div class="filters-wrapper">
        <form action="my_orders.php" method="GET">
            <div class="search-form">
                <input type="text" name="search" id="searchInput" class="search-input" placeholder="Search by Order ID, Product, or Customer..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                <div id="suggestions" class="suggestions-dropdown"></div>
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
                <?php if ($search || $order_status || $payment_status || $channel_filter): ?>
                    <a href="my_orders.php" class="btn-search" style="background:#6c757d; text-decoration:none;">Clear All</a>
                <?php endif; ?>
            </div>
            
            <div class="filter-group">
                <select name="order_status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Order Status</option>
                    <?php foreach($order_status_list as $s): ?>
                        <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $order_status == $s ? 'selected' : ''; ?>>
                            <?php echo ucfirst(htmlspecialchars($s)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="payment_status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Payment Status</option>
                    <?php foreach($payment_status_list as $s): ?>
                        <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $payment_status == $s ? 'selected' : ''; ?>>
                            <?php echo ucfirst(htmlspecialchars($s)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="channel" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Channels</option>
                    <?php 
                        $available_channels = ['Amazon', 'Flipkart', 'Shopify', 'Ebay', 'TikTok Shop'];
                        foreach($available_channels as $ch): 
                    ?>
                        <option value="<?php echo htmlspecialchars($ch); ?>" <?php echo $channel_filter == $ch ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ch); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <?php if (count($orders) > 0): ?>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Order Status</th>
                    <th>Payment Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php 
                        // Image Logic: Prefer primary, fall back to any other
                        $img_url = $order['primary_image'] ?? $order['fallback_image'] ?? 'https://via.placeholder.com/50?text=No+Img';
                    ?>
                    <tr>
                        <td>#ORD-<?php echo $order['id']; ?></td>
                        <td>
                            <div class="product-cell">
                                <img src="<?php echo htmlspecialchars($img_url); ?>" class="product-img" onerror="this.src='https://via.placeholder.com/50'">
                                <div>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($order['product_name']); ?></div>
                                    <div style="font-size:12px; color:#888;">SKU: <?php echo htmlspecialchars($order['sku']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            <div style="font-size:12px; color:#888;"><?php echo htmlspecialchars($order['channel']); ?></div>
                        </td>
                        <td style="font-weight:700;">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                                <?php echo htmlspecialchars($order['order_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['payment_status']); ?>">
                                <?php echo htmlspecialchars($order['payment_status']); ?>
                            </span>
                        </td>
                        <td style="font-size:13px; color:#666;"><?php echo date('d M, Y', strtotime($order['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-cart" style="font-size: 50px; margin-bottom: 20px;"></i>
            <h3>No orders found yet</h3>
            <p>Your orders will appear here once customers start buying your products.</p>
        </div>
    <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestions');
    let currentFocus = -1;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length < 1) {
            suggestionsBox.style.display = 'none';
            return;
        }

        fetch(`get_suggestions.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    suggestionsBox.innerHTML = '';
                    data.forEach((item, index) => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.innerHTML = `<i class="fas fa-search"></i> ${item}`;
                        div.addEventListener('click', function() {
                            searchInput.value = item;
                            suggestionsBox.style.display = 'none';
                            searchInput.form.submit();
                        });
                        suggestionsBox.appendChild(div);
                    });
                    suggestionsBox.style.display = 'block';
                } else {
                    suggestionsBox.style.display = 'none';
                }
            });
    });

    searchInput.addEventListener('keydown', function(e) {
        const items = suggestionsBox.getElementsByClassName('suggestion-item');
        if (e.keyCode == 40) { // Down
            currentFocus++;
            addActive(items);
        } else if (e.keyCode == 38) { // Up
            currentFocus--;
            addActive(items);
        } else if (e.keyCode == 13) { // Enter
            if (currentFocus > -1) {
                if (items[currentFocus]) items[currentFocus].click();
                e.preventDefault();
            }
        }
    });

    function addActive(items) {
        if (!items) return false;
        removeActive(items);
        if (currentFocus >= items.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (items.length - 1);
        items[currentFocus].classList.add('active');
        items[currentFocus].scrollIntoView({ block: 'nearest' });
    }

    function removeActive(items) {
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove('active');
        }
    }

    document.addEventListener('click', function(e) {
        if (e.target !== searchInput) {
            suggestionsBox.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
