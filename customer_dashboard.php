<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'Trendsetter';
$email = $_SESSION['email'];

// 1. Fetch Stats
try {
    // Total Orders (using user_id)
    $stmt_orders_count = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt_orders_count->execute([$user_id]);
    $total_orders = $stmt_orders_count->fetchColumn() ?: 0;

    // Wishlist Count
    $stmt_wish_count = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt_wish_count->execute([$user_id]);
    $wishlist_count = $stmt_wish_count->fetchColumn() ?: 0;

    // 2. Fetch Recent Orders
    $stmt_recent_orders = $pdo->prepare("
        SELECT o.*, pb.name as product_name, 
               (SELECT url FROM product_media pm WHERE pm.product_id = o.product_id AND is_primary = 1 LIMIT 1) as image
        FROM orders o
        JOIN product_base pb ON o.product_id = pb.id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC
        LIMIT 3
    ");
    $stmt_recent_orders->execute([$user_id]);
    $recent_orders = $stmt_recent_orders->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Recent Wishlist
    $stmt_recent_wish = $pdo->prepare("
        SELECT pb.id, pb.name, pp.price,
               (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as image
        FROM wishlist w
        JOIN product_base pb ON w.product_id = pb.id
        JOIN product_prices pp ON pb.id = pp.product_id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
        LIMIT 4
    ");
    $stmt_recent_wish->execute([$user_id]);
    $recent_wish = $stmt_recent_wish->fetchAll(PDO::FETCH_ASSOC);

    // 4. Fetch Recommendations (Fixed broken images by replacing with reliable unsplash links)
    $stmt_reco = $pdo->prepare("
        SELECT pb.id, pb.name, pp.price,
               COALESCE((SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1), 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400') as image
        FROM product_base pb
        JOIN product_prices pp ON pb.id = pp.product_id
        WHERE pb.status = 'published'
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt_reco->execute();
    $recommendations = $stmt_reco->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Fail silently in production
    $total_orders = $wishlist_count = 0;
    $recent_orders = $recent_wish = $recommendations = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --bg: #ffffff;
            --light-bg: #f0f9ff;
            --surface: rgba(255, 255, 255, 0.85);
            --card: #ffffff;
            --border: #bae6fd;
            --primary: #2563eb;       /* Royal Blue */
            --primary-glow: rgba(37, 99, 235, 0.15);
            --secondary: #10b981;     /* Emerald Green */
            --text-main: #131b2a;
            --text-blue: #2563eb;
            --text-muted: #64748b;
            --sidebar-w: 280px;
            --glass: rgba(255, 255, 255, 0.7);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 50%, #e0f2fe 100%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            padding: 2.5rem 1.5rem;
            z-index: 1000;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            margin-bottom: 3.5rem;
            padding: 0 1rem;
        }

        .sidebar-logo img { height: 36px; }
        .sidebar-logo span { font-weight: 800; font-size: 1.4rem; letter-spacing: -1px; color: var(--text-main); }

        .nav-group { margin-bottom: 2.5rem; }
        .nav-label { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 1.2rem; display: block; padding: 0 1rem; }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.9rem 1.2rem;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            border-radius: 14px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 0.4rem;
        }

        .nav-item i { font-size: 1.1rem; width: 24px; text-align: center; }
        .nav-item:hover {
            background: var(--primary-glow);
            color: var(--primary);
        }
        .nav-item.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
        }
        .nav-item.active i { color: #fff; }

        .logout-btn { margin-top: auto; color: #ef4444; }
        .logout-btn:hover { background: rgba(239, 68, 68, 0.08); color: #ef4444; }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-w);
            flex: 1;
            padding: 3rem 4.5rem;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3.5rem;
        }

        .welcome-text h1 { font-size: 2.8rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -1.5px; color: var(--text-main); }
        .welcome-text p { color: var(--text-muted); font-size: 1.15rem; font-weight: 500; }

        .search-trigger {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 0.85rem 1.8rem;
            border-radius: 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            color: var(--text-muted);
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.05);
        }
        .search-trigger:hover { border-color: var(--primary); color: var(--primary); box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08); }

        /* --- KPI STRIP --- */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.8rem;
            margin-bottom: 3.5rem;
        }

        .kpi-card {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 2rem;
            border-radius: 28px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            transition: 0.3s;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.03);
        }
        .kpi-card:hover { transform: translateY(-5px); border-color: var(--primary); box-shadow: 0 12px 30px rgba(37, 99, 235, 0.08); }

        .kpi-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-glow);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.3rem;
        }

        .kpi-val { font-size: 2.2rem; font-weight: 800; color: var(--text-main); letter-spacing: -1px; }
        .kpi-label { font-size: 0.85rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        /* --- DASHBOARD GRID --- */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 2.5rem;
            margin-bottom: 3.5rem;
        }

        .section-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 36px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.04);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-header h3 { font-size: 1.4rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; }
        .section-header a { color: var(--primary); text-decoration: none; font-size: 0.9rem; font-weight: 700; transition: 0.2s; }
        .section-header a:hover { opacity: 0.8; gap: 8px; }

        /* --- ORDERS --- */
        .order-list { display: flex; flex-direction: column; gap: 1.2rem; }
        .order-item {
            background: var(--light-bg);
            border: 1px solid var(--border);
            padding: 1.2rem;
            border-radius: 24px;
            display: flex;
            align-items: center;
            gap: 1.8rem;
            transition: 0.3s;
        }
        .order-item:hover { border-color: var(--primary); transform: scale(1.02); background: #ffffff; box-shadow: 0 8px 24px rgba(37, 99, 235, 0.06); }
        .order-img { width: 85px; height: 85px; border-radius: 18px; object-fit: cover; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        .order-info { flex: 1; }
        .order-info h4 { font-size: 1.1rem; margin-bottom: 6px; font-weight: 700; color: var(--text-main); }
        .order-info p { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }

        .order-status {
            padding: 0.5rem 1.2rem;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .status-shipped { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .status-delivered { background: rgba(16, 185, 129, 0.1); color: #10b981; }

        /* --- WISHLIST GRID --- */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.2rem;
        }
        .wish-item {
            text-decoration: none;
            display: block;
            border-radius: 24px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
        .wish-item img { width: 100%; aspect-ratio: 1; object-fit: cover; transition: 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        .wish-overlay {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 1.2rem; background: linear-gradient(transparent, rgba(19, 27, 42, 0.85));
            transform: translateY(100%); transition: 0.4s;
            color: #fff;
        }
        .wish-item:hover .wish-overlay { transform: translateY(0); }
        .wish-item:hover img { transform: scale(1.15); }
        .wish-overlay h5 { font-size: 0.85rem; font-weight: 700; }

        /* --- RECOMMENDATIONS --- */
        .reco-shelf { margin-top: 5rem; }
        .reco-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }
        .reco-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 32px;
            padding: 1.5rem;
            text-decoration: none;
            color: inherit;
            transition: 0.3s;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.03);
        }
        .reco-card:hover { transform: translateY(-8px); border-color: var(--primary); box-shadow: 0 15px 35px rgba(37, 99, 235, 0.1); }
        .reco-img { width: 100%; aspect-ratio: 1.2; border-radius: 22px; object-fit: cover; margin-bottom: 1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        .reco-card h4 { font-size: 1rem; margin-bottom: 8px; font-weight: 700; color: var(--text-main); }
        .reco-card p { font-size: 1rem; color: var(--primary); font-weight: 800; }

        @media (max-width: 1200px) {
            .main-content { padding: 3rem; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .dashboard-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 2rem; }
            .reco-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <a href="Index.php" class="sidebar-logo">
            <img src="assets/shoe_logo_green.png" alt="WalkOn">
            <span>WALK<span style="color:var(--primary)">ON</span></span>
        </a>

        <div class="nav-group">
            <span class="nav-label">Main Menu</span>
            <a href="customer_dashboard.php" class="nav-item active">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="shop.php" class="nav-item">
                <i class="fas fa-shopping-bag"></i> Shop Now
            </a>
            <a href="my_orders.php" class="nav-item">
                <i class="fas fa-box"></i> My Orders
            </a>
            <a href="wishlist.php" class="nav-item">
                <i class="fas fa-heart"></i> Wishlist
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-label">Account</span>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user-circle"></i> Profile
            </a>
            <a href="my_wallet.php" class="nav-item">
                <i class="fas fa-wallet"></i> Wallet
            </a>
            <a href="support.php" class="nav-item">
                <i class="fas fa-headset"></i> Support
            </a>
        </div>

        <a href="logout.php" class="nav-item logout-btn" style="margin-top:auto">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        
        <header class="header">
            <div class="welcome-text">
                <h1>Hello, <?= htmlspecialchars($first_name) ?>! 👋</h1>
                <p>Ready to find your next favorite pair?</p>
            </div>
            <div class="search-trigger" onclick="window.location.href='shop.php'">
                <i class="fas fa-search"></i>
                <span>Search styles, brands...</span>
            </div>
        </header>

        <!-- KPI Strip -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="kpi-val"><?= $total_orders ?></div>
                <div class="kpi-label">Total Orders</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-heart"></i></div>
                <div class="kpi-val"><?= $wishlist_count ?></div>
                <div class="kpi-label">Saved Items</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-star"></i></div>
                <div class="kpi-val">4.8</div>
                <div class="kpi-label">Review Score</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="kpi-val">2</div>
                <div class="kpi-label">Active Perks</div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Recent Orders -->
            <div class="section-card">
                <div class="section-header">
                    <h3>Recent Activity</h3>
                    <a href="my_orders.php">View All Tracking <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="order-list">
                    <?php if (empty($recent_orders)): ?>
                        <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            <i class="fas fa-shopping-basket" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No orders yet. Time for a shopping spree?</p>
                        </div>
                    <?php else: foreach ($recent_orders as $order): 
                        $status_class = 'status-' . strtolower($order['status']);
                    ?>
                        <div class="order-item">
                            <img src="<?= $order['image'] ?: 'assets/placeholder_shoe.png' ?>" class="order-img" alt="Product">
                            <div class="order-info">
                                <h4><?= htmlspecialchars($order['product_name']) ?></h4>
                                <p>Order #WLK-<?= $order['id'] ?> • <?= date('j M, Y', strtotime($order['order_date'])) ?></p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; margin-bottom: 5px;">₹<?= number_format($order['total_price'], 2) ?></div>
                                <span class="order-status <?= $status_class ?>"><?= $order['status'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Wishlist Preview -->
            <div class="section-card">
                <div class="section-header">
                    <h3>Your Favorites</h3>
                    <a href="wishlist.php">Full List</a>
                </div>
                <?php if (empty($recent_wish)): ?>
                     <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); border: 2px dashed var(--border); border-radius: 24px;">
                        <i class="far fa-heart" style="font-size: 1.5rem; margin-bottom: 10px; opacity: 0.3;"></i>
                        <p style="font-size: 0.85rem;">Items you heart will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="wishlist-grid">
                        <?php foreach ($recent_wish as $wish): ?>
                            <a href="product_detail.php?id=<?= $wish['id'] ?>" class="wish-item">
                                <img src="<?= $wish['image'] ?: 'assets/placeholder_shoe.png' ?>" alt="Wishlist">
                                <div class="wish-overlay">
                                    <h5><?= htmlspecialchars($wish['name']) ?></h5>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="reco-shelf">
            <div class="section-header">
                <div>
                    <h3>Handpicked For You</h3>
                    <p style="color:var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Based on current trends and top-rated styles.</p>
                </div>
                <a href="shop.php" class="nav-item" style="margin:0; background:var(--primary-glow); color: var(--primary);">Explore Store</a>
            </div>
            <div class="reco-grid" style="margin-top: 2rem;">
                <?php foreach ($recommendations as $p): ?>
                    <a href="product_detail.php?id=<?= $p['id'] ?>" class="reco-card">
                        <img src="<?= $p['image'] ?: 'assets/placeholder_shoe.png' ?>" class="reco-img" alt="Reco">
                        <h4><?= htmlspecialchars($p['name']) ?></h4>
                        <p>₹<?= number_format($p['price'], 0) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    </main>

</body>
</html>
