<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Get the actual seller_id for this user
try {
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller = $stmt_seller->fetch();
    
    $seller_id = $seller ? $seller['id'] : -1;
    
    $search = trim($_GET['search'] ?? '');
    $status_filter = trim($_GET['status'] ?? '');
    $category_filter = trim($_GET['category'] ?? '');
    $channel_filter = trim($_GET['channel'] ?? '');

    // Get unique categories and statuses for filters
    // Categories now come from the categories table linked to product_base
    $categories = $pdo->prepare("SELECT DISTINCT c.name FROM categories c 
                                JOIN product_base pb ON pb.category_id = c.id 
                                WHERE pb.seller_id = ?");
    $categories->execute([$seller_id]);
    $category_list = $categories->fetchAll(PDO::FETCH_COLUMN);

    $statuses = $pdo->prepare("SELECT DISTINCT status FROM product_base WHERE seller_id = ?");
    $statuses->execute([$seller_id]);
    $status_list = $statuses->fetchAll(PDO::FETCH_COLUMN);

    // Build Dynamic Query for Normalized Schema
    $query = "SELECT 
                pb.id, 
                pb.name as product_name, 
                pb.status, 
                pb.created_at,
                pp.price, 
                ps.sku, 
                pst.quantity,
                (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image,
                GROUP_CONCAT(pc.channel_name) as channels_str
              FROM product_base pb
              LEFT JOIN product_prices pp ON pb.id = pp.product_id
              LEFT JOIN product_skus ps ON pb.id = ps.product_id
              LEFT JOIN product_stock pst ON pb.id = pst.product_id
              LEFT JOIN categories c ON pb.category_id = c.id
              LEFT JOIN product_channels pc ON pb.id = pc.product_id
              WHERE pb.seller_id = ?";
    
    $params = [$seller_id];

    if ($search) {
        $query .= " AND (pb.name LIKE ? OR ps.sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($status_filter) {
        $query .= " AND pb.status = ?";
        $params[] = $status_filter;
    }
    if ($category_filter) {
        $query .= " AND c.name = ?";
        $params[] = $category_filter;
    }
    // Channel filter needs having because of Group Concat or separate join, 
    // but effectively searching the group concat string is easier for this simple filter
    if ($channel_filter) {
        // We can't use HAVING easily with pagination/other filters mixed sometimes in simple implementations
        // But here we are grouping by ID so HAVING is fine, OR we filter on the join.
        // Let's filter on the ON clause or WHERE if we weren't grouping.
        // Since we are using GROUP_CONCAT, we need to Group By first.
        // Actually, to filter by "channel", simpler to add a WHERE EXISTS clause or similar.
        // But for simply string matching the result:
        $query .= " AND EXISTS (SELECT 1 FROM product_channels pch WHERE pch.product_id = pb.id AND pch.channel_name = ?)";
        $params[] = $channel_filter;
    }

    $query .= " GROUP BY pb.id, pp.price, ps.sku, pst.quantity"; 
    $query .= " ORDER BY pb.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching listings: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Listings - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding: 0; display: flex; flex-direction: column; min-height: 100vh; }

        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; flex: 1; width: 100%; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { font-size: 28px; }
        .btn-add {
            background: var(--primary); color: white; padding: 10px 20px; border-radius: 30px; text-decoration: none; font-weight: 600;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }
        
        .card {
            background: white; 
            border-radius: 20px; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.03);
            position: relative;
        }
        .card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
        }
        
        .card-img-top {
            width: 100%; 
            height: 220px; 
            object-fit: cover;
            background: #f0f0f0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .card-body { 
            padding: 24px; 
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .card-title { 
            font-size: 18px; 
            font-weight: 600; 
            margin-bottom: 12px; 
            line-height: 1.4;
            height: 2.8em; /* Exactly 2 lines */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            color: #1a1a1a;
        }
        .card-price { 
            color: var(--primary); 
            font-weight: 700; 
            font-size: 20px; 
            margin-bottom: 4px; 
        }
        .card-sku { 
            color: #999; 
            font-size: 12px; 
            margin-bottom: 16px; 
            font-family: monospace;
        }
        
        .channels { 
            margin-bottom: 20px; 
            display: flex;
            gap: 12px;
            color: #666;
        }
        .channel-icon { 
            font-size: 16px;
            transition: color 0.2s;
        }
        .channel-icon:hover { color: var(--primary); }
        
        .card-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px dashed #eee;
        }

        .status-badge {
            display: inline-block; 
            padding: 6px 14px; 
            border-radius: 30px; 
            font-size: 11px; 
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #eafff0; 
            color: #1db954;
        }

        .stock-count {
            font-size: 13px; 
            color: #777;
            font-weight: 500;
        }

        .empty-state { text-align: center; padding: 80px 20px; color: #888; }
        .empty-state i { font-size: 60px; color: #ddd; margin-bottom: 24px; }

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
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
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
        
        .btn-cancel {
            background: #fff0f1;
            color: #ff4757;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            border: 1px solid #ffe0e2;
        }
        .btn-cancel:hover { background: #ffe0e2; transform: translateY(-2px); }

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
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        .btn-add:hover { 
            background: var(--primary-dark);
            transform: translateY(-2px); 
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
        }

        .card-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 8px;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 10;
        }
        .card:hover .card-actions { 
            opacity: 1; 
            transform: translateY(0);
        }
        .btn-remove {
            background: white;
            color: #ff4757;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .btn-remove:hover { 
            background: #ff4757; 
            color: white; 
            transform: scale(1.1); 
        }

        /* Filter Enhancements */
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
        .filters-wrapper {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }

        /* Autocomplete Styles */
        .search-form { position: relative; }
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
        .suggestion-item.active i { color: var(--primary); }

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
            <h1 style="font-size: 28px; margin-top:5px;">My Listings</h1>
            <p>Manage your active products</p>
        </div>
        <div>
            <a href="export_listings.php?<?php echo http_build_query($_GET); ?>" class="btn-add" style="background:#17a2b8; box-shadow: 0 4px 12px rgba(23, 162, 184, 0.2); margin-right: 10px;">
                <i class="fas fa-file-export"></i> Download Report
            </a>
            <a href="dashboard.php" class="btn-cancel" style="margin-right:10px;"><i class="fas fa-times"></i> Cancel</a>
            <a href="add_listing.php" class="btn-add"><i class="fas fa-plus"></i> Add New Listing</a>
        </div>
    </div>

    <div class="filters-wrapper">
        <form action="my_listings.php" method="GET">
            <div class="search-form">
                <input type="text" name="search" id="searchInput" class="search-input" placeholder="Search by product name or SKU..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                <div id="suggestions" class="suggestions-dropdown"></div>
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
                <?php if ($search || $status_filter || $category_filter || $channel_filter): ?>
                    <a href="my_listings.php" class="btn-search" style="background:#6c757d; text-decoration:none;">Clear All</a>
                <?php endif; ?>
            </div>
            
            <div class="filter-group">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach($status_list as $s): ?>
                        <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $status_filter == $s ? 'selected' : ''; ?>>
                            <?php echo ucfirst(htmlspecialchars($s)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="category" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach($category_list as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category_filter == $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
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

    <?php if (count($listings) > 0): ?>
        <div class="grid">
            <?php foreach ($listings as $product): ?>
                <?php 
                    // Image Logic: Prefer primary, fall back to any other
                    $first_image = $product['primary_image'] ?? $product['fallback_image'] ?? 'https://via.placeholder.com/280x200?text=No+Image';
                    
                    // Channels Logic: Explode the GROUP_CONCAT string
                    $channels = !empty($product['channels_str']) ? explode(',', $product['channels_str']) : [];
                ?>
                <div class="card">
                    <a href="product_details.php?id=<?php echo $product['id']; ?>" style="text-decoration:none; color:inherit;">
                        <img src="<?php echo htmlspecialchars($first_image); ?>" class="card-img-top" alt="Product">
                    </a>
                    
                    <div class="card-actions">
                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn-remove" onclick="return confirm('Are you sure you want to remove this product?')" title="Remove Product">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>

                    <div class="card-body">
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" style="text-decoration:none; color:inherit;">
                            <h3 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <div class="card-price">₹<?php echo number_format($product['price'], 2); ?></div>
                            <div class="card-sku">SKU: <?php echo htmlspecialchars($product['sku']); ?></div>
                        </a>
                        
                        <div class="channels">
                            <?php foreach($channels as $ch): ?>
                                <i class="fab fa-<?php echo strtolower(trim($ch)); ?> channel-icon" title="<?php echo htmlspecialchars($ch); ?>"></i>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="card-footer">
                            <span class="status-badge"><?php echo htmlspecialchars($product['status']); ?></span>
                            <span class="stock-count"><?php echo $product['quantity']; ?> in stock</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 20px;"></i>
            <h3>No listings found</h3>
            <p>Get started by adding your first product.</p>
            <a href="add_listing.php" class="btn-add" style="margin-top:20px; display:inline-block;">Add Listing</a>
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
