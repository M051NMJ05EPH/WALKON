<?php
// entrepreneur_dashboard.php - Startup Command Center
session_start();
include 'config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entrepreneur') {
    header("Location: login.php");
    exit();
}

$email      = $_SESSION['email'];
$first_name = $_SESSION['first_name'] ?? 'Entrepreneur';
$last_name  = $_SESSION['last_name'] ?? '';
$display_name = trim("$first_name $last_name") ?: $email;
$seller_id  = $_SESSION['seller_id'] ?? null;

// Defaults
$total_orders = $total_revenue = $total_products = $low_stock = 0;
$total_purchases = 0;
$chart_labels = $revenue_data = [];
$recent_sales = $recent_purchases = [];

try {
    if (!$seller_id) {
        $s = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $s->execute([$email]);
        $row = $s->fetch();
        $seller_id = $row ? $row['id'] : -1;
        if ($seller_id != -1) $_SESSION['seller_id'] = $seller_id;
    }

    if ($seller_id != -1) {
        $o = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE seller_id=?"); $o->execute([$seller_id]); $total_orders = $o->fetchColumn() ?: 0;
        $r = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status!='cancelled' AND seller_id=?"); $r->execute([$seller_id]); $total_revenue = (float)$r->fetchColumn();
        $p = $pdo->prepare("SELECT COUNT(*) FROM product_base WHERE seller_id=?"); $p->execute([$seller_id]); $total_products = $p->fetchColumn() ?: 0;
        $l = $pdo->prepare("SELECT COUNT(*) FROM product_stock ps JOIN product_base pb ON ps.product_id=pb.id WHERE ps.quantity<10 AND pb.seller_id=?"); $l->execute([$seller_id]); $low_stock = $l->fetchColumn() ?: 0;
        $rs = $pdo->prepare("SELECT * FROM orders WHERE seller_id=? ORDER BY order_date DESC LIMIT 5"); $rs->execute([$seller_id]); $recent_sales = $rs->fetchAll(PDO::FETCH_ASSOC);

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chart_labels[] = date('D', strtotime($date));
            $d = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE seller_id=? AND DATE(order_date)=? AND status!='cancelled'"); $d->execute([$seller_id,$date]); $revenue_data[] = (float)$d->fetchColumn();
        }
    }

    $bp = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_email=?"); $bp->execute([$email]); $total_purchases = $bp->fetchColumn() ?: 0;
    $rp = $pdo->prepare("SELECT * FROM orders WHERE customer_email=? ORDER BY order_date DESC LIMIT 4"); $rp->execute([$email]); $recent_purchases = $rp->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { /* silent */ }

$conversion_rate = $total_products > 0 ? round(($total_orders / max($total_products, 1)) * 10, 1) : 0;
$avg_order = $total_orders > 0 ? $total_revenue / $total_orders : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Entrepreneur Hub — <?= htmlspecialchars($first_name) ?> | WALKON</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{
    --primary:  #2563eb;       /* Royal Blue */
    --primary-hover: #1d4ed8;
    --secondary: #10b981;     /* Emerald Green */
    --bg:       #ffffff;
    --sky-light: #f0f9ff;
    --sky-mid:   #e0f2fe;
    --surface:  rgba(255, 255, 255, 0.8);
    --card:     #ffffff;
    --border:   #bae6fd;
    --text:     #1e293b;       /* Deep Navy */
    --text-green: #10b981;    /* Green Text */
    --muted:    #64748b;
    --muted2:   #94a3b8;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Space Grotesk',sans-serif;}
body{
    background: radial-gradient(circle at 10% 20%, var(--sky-mid) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, var(--sky-light) 0%, transparent 40%),
                var(--bg);
    background-attachment: fixed;
    color: var(--text);
    min-height: 100vh;
    overflow-x: hidden;
}

/* TOP NAV */
.topnav{
    position:sticky;top:0;z-index:100;
    background: linear-gradient(135deg, #10b981 0%, #2563eb 100%);
    backdrop-filter:blur(20px);
    border-bottom:1px solid rgba(255,255,255,0.1);
    padding:0 32px;height:64px;
    display:flex;align-items:center;justify-content:space-between;
    box-shadow: 0 4px 20px rgba(37, 99, 235, 0.05);
}
.logo-area{display:flex;align-items:center;gap:14px;}
.logo-text{font-size:1.1rem;font-weight:700;letter-spacing:-0.5px;color:#ffffff;}
.logo-text span{color:#10b981;}

.nav-center{display:flex;align-items:center;gap:4px;}
.nav-pill{
    display:flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;
    text-decoration:none;color:rgba(255,255,255,0.8);font-weight:600;font-size:0.82rem;
    transition:0.2s;
}
.nav-pill:hover{background:rgba(255,255,255,0.1);color:#ffffff;}
.nav-pill.active{background:rgba(255,255,255,0.2);color:#ffffff;}
.nav-pill i{font-size:0.85rem;}

.nav-right{display:flex;align-items:center;gap:10px;}
.role-tag{
    padding:5px 12px;border-radius:20px;font-size:0.7rem;font-weight:700;
    background:rgba(255,255,255,0.1);
    border:1px solid rgba(255,255,255,0.2);color:#ffffff;
    letter-spacing:0.5px;text-transform:uppercase;
}
.avatar{
    width:36px;height:36px;border-radius:10px;object-fit:cover;
    border:2px solid rgba(249,115,22,0.4);cursor:pointer;
}
.logout-btn{
    display:flex;align-items:center;gap:6px;padding:7px 14px;
    background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);
    border-radius:8px;color:#ef4444;font-size:0.78rem;font-weight:700;
    text-decoration:none;transition:0.2s;
}
.logout-btn:hover{background:rgba(239,68,68,0.2);}

/* MAIN LAYOUT */
.page{max-width:1400px;margin:0 auto;padding:32px;position:relative;z-index:1;}

/* HERO HEADER */
.hero-header{
    background:rgba(255,255,255,0.7);backdrop-filter:blur(10px);
    border:1px solid var(--border);border-radius:20px;padding:28px 32px;
    margin-bottom:28px;display:flex;align-items:center;justify-content:space-between;
    position:relative;overflow:hidden;
    box-shadow: 0 10px 30px rgba(37,99,235,0.05);
}
.hero-header::before{
    content:'';position:absolute;right:-60px;top:-60px;
    width:200px;height:200px;border-radius:50%;
    background:radial-gradient(circle,rgba(249,115,22,0.12),transparent 70%);
}
.hero-text h1{font-size:1.8rem;font-weight:800;letter-spacing:-0.5px;margin-bottom:6px; color: var(--text);}
.hero-text h1 span{color:var(--text-green);}
.hero-text p{color:var(--muted);font-weight:500;font-size:0.9rem;}
.hero-actions{display:flex;gap:10px;}
.btn-grad{
    display:flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;
    font-weight:700;font-size:0.82rem;text-decoration:none;transition:0.3s;border:none;cursor:pointer;
}
.btn-orange{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;}
.btn-orange:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,0.25);}
.btn-ghost{background:rgba(255,255,255,0.8);border:1px solid var(--border);color:var(--text); backdrop-filter: blur(5px);}
.btn-ghost:hover{background:rgba(255,255,255,1); border-color: var(--primary);}

/* METRICS GRID */
.metrics-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;margin-bottom:32px;}
.metric-card{
    background:var(--card);border:1px solid var(--border);
    border-radius:24px;padding:24px;position:relative;overflow:hidden;
    transition:0.3s cubic-bezier(0.4,0,0.2,1);
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.03);
}
.metric-card:hover{transform:translateY(-5px);border-color:var(--primary);box-shadow:0 12px 30px rgba(37, 99, 235, 0.1);}
.metric-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;}
.metric-icon{
    width:42px;height:42px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    font-size:1.1rem;margin-bottom:0;
}
.metric-value{font-size:2rem;font-weight:800;color:var(--text);margin-bottom:4px;}
.metric-label{font-size:0.82rem;color:var(--muted);font-weight:600;}
.metric-icon{
    width:40px;height:40px;border-radius:10px;display:flex;align-items:center;
    justify-content:center;font-size:1rem;
}
.mi-orange{background:rgba(249,115,22,0.15);color:var(--orange);}
.mi-purple{background:rgba(139,92,246,0.15);color:var(--purple);}
.mi-cyan{background:rgba(6,182,212,0.15);color:var(--cyan);}
.mi-green{background:rgba(16,185,129,0.15);color:var(--green);}

.metric-trend{
    font-size:0.68rem;font-weight:700;padding:3px 8px;border-radius:20px;
    display:flex;align-items:center;gap:3px;
}
.trend-up{background:rgba(16,185,129,0.12);color:var(--green);}
.trend-down{background:rgba(239,68,68,0.12);color:var(--red);}
.trend-neutral{background:rgba(100,116,139,0.15);color:var(--muted2);}

.metric-value{font-size:1.8rem;font-weight:700;letter-spacing:-1px;margin-bottom:4px;}
.metric-label{font-size:0.75rem;color:var(--muted);font-weight:500;}

/* DUAL MODE SECTION */
.dual-header{
    display:flex;align-items:center;gap:12px;margin-bottom:20px;
}
.mode-badge{
    padding:6px 14px;border-radius:20px;font-size:0.72rem;font-weight:700;
    text-transform:uppercase;letter-spacing:0.5px;
}
.mode-sell{background:rgba(249,115,22,0.12);color:var(--orange);border:1px solid rgba(249,115,22,0.25);}
.mode-buy{background:rgba(139,92,246,0.12);color:var(--purple);border:1px solid rgba(139,92,246,0.25);}

/* CONTENT GRID */
.content-grid{display:grid;grid-template-columns:3fr 2fr;gap:20px;margin-bottom:24px;}
.content-grid-3{display:grid;grid-template-columns:2fr 1fr 1fr;gap:20px;}

.glass-card{
    background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;
}
.gc-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
.gc-title{font-size:0.9rem;font-weight:700;color:var(--text);}
.gc-link{font-size:0.75rem;color:var(--orange);font-weight:700;text-decoration:none;}
.gc-link:hover{color:var(--orange2);}

/* Chart */
.chart-wrap{height:220px;position:relative;}

/* Sales Table */
.sales-table{width:100%;border-collapse:collapse;}
.sales-table th{
    text-align:left;padding:8px 12px;font-size:0.68rem;font-weight:700;
    text-transform:uppercase;letter-spacing:0.8px;color:var(--muted);
    border-bottom:1px solid var(--border);
}
.sales-table td{padding:12px;font-size:0.82rem;border-bottom:1px solid var(--border); color: var(--text);}
.sales-table tr:last-child td{border-bottom:none;}
.sales-table tr:hover td{background:rgba(37, 99, 235, 0.03);}
.s-id{font-weight:700;color:var(--primary);}
.s-chip{
    display:inline-flex;align-items:center;gap:4px;padding:3px 8px;
    border-radius:20px;font-size:0.65rem;font-weight:700;text-transform:uppercase;
}
.sc-delivered{background:rgba(16,185,129,0.1);color:var(--green);}
.sc-pending{background:rgba(249,115,22,0.1);color:var(--orange);}
.sc-processing{background:rgba(6,182,212,0.1);color:var(--cyan);}
.sc-cancelled{background:rgba(239,68,68,0.1);color:var(--red);}

/* Purchase cards */
.purchase-list{display:flex;flex-direction:column;gap:10px;}
.purchase-item{
    display:flex;justify-content:space-between;align-items:center;
    background:#ffffff;border:1px solid var(--border);
    border-radius:10px;padding:12px 14px;transition:0.2s;
    box-shadow: 0 2px 10px rgba(37, 99, 235, 0.02);
}
.purchase-item:hover{border-color:rgba(139,92,246,0.3);}
.pi-left{}
.pi-id{font-size:0.8rem;font-weight:700;color:var(--purple2);}
.pi-date{font-size:0.7rem;color:var(--muted);margin-top:2px;}
.pi-right{text-align:right;}
.pi-amount{font-size:0.9rem;font-weight:700;color:var(--green);}
.pi-status{font-size:0.65rem;color:var(--muted);margin-top:2px;text-transform:uppercase;}

/* Wallet Card */
.wallet-card{
    background:linear-gradient(135deg,rgba(249,115,22,0.15),rgba(139,92,246,0.15));
    border:1px solid rgba(249,115,22,0.2);border-radius:16px;padding:24px;
    position:relative;overflow:hidden;
}
.wallet-card::before{
    content:'';position:absolute;bottom:-30px;right:-30px;
    width:120px;height:120px;border-radius:50%;
    background:radial-gradient(circle,rgba(249,115,22,0.2),transparent 70%);
}
.wallet-label{font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted2);margin-bottom:8px;}
.wallet-amount{font-size:2rem;font-weight:700;letter-spacing:-1px;margin-bottom:4px;}
.wallet-sub{font-size:0.75rem;color:var(--muted2);margin-bottom:20px;}
.wallet-btn{
    display:flex;align-items:center;justify-content:center;gap:8px;
    padding:10px;border-radius:10px;background:rgba(255,255,255,0.1);
    border:1px solid rgba(255,255,255,0.15);color:var(--text);
    font-weight:700;font-size:0.8rem;text-decoration:none;transition:0.2s;
}
.wallet-btn:hover{background:rgba(255,255,255,0.18);}

/* Quick Links */
.quick-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.ql-item{
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:8px;padding:16px 10px;background:rgba(255,255,255,0.03);
    border:1px solid var(--border);border-radius:12px;
    text-decoration:none;color:var(--muted2);font-size:0.75rem;font-weight:600;
    transition:0.2s;text-align:center;
}
.ql-item:hover{background:rgba(249,115,22,0.08);border-color:rgba(249,115,22,0.3);color:var(--orange);}
.ql-item i{font-size:1.2rem;color:var(--orange);}

.empty-state{text-align:center;padding:30px;color:var(--muted);font-size:0.85rem;}
.empty-state i{font-size:2rem;opacity:0.3;display:block;margin-bottom:8px;}

/* QUICK TOOLBAR */
.quick-toolbar{
    display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap;
}
.qt-btn{
    display:flex;align-items:center;gap:8px;padding:10px 18px;
    border-radius:10px;text-decoration:none;font-weight:700;font-size:0.8rem;
    border:1px solid transparent;transition:all 0.25s;
}
.qt-btn i{font-size:0.9rem;}
.qt-btn:hover{transform:translateY(-2px);}
.qt-orange{background:rgba(249,115,22,0.12);color:var(--orange);border-color:rgba(249,115,22,0.25);}
.qt-orange:hover{background:rgba(249,115,22,0.22);box-shadow:0 4px 16px rgba(249,115,22,0.2);}
.qt-purple{background:rgba(139,92,246,0.12);color:var(--purple2);border-color:rgba(139,92,246,0.25);}
.qt-purple:hover{background:rgba(139,92,246,0.22);box-shadow:0 4px 16px rgba(139,92,246,0.2);}
.qt-cyan{background:rgba(6,182,212,0.12);color:var(--cyan);border-color:rgba(6,182,212,0.25);}
.qt-cyan:hover{background:rgba(6,182,212,0.22);box-shadow:0 4px 16px rgba(6,182,212,0.2);}
.qt-green{background:rgba(16,185,129,0.12);color:var(--green);border-color:rgba(16,185,129,0.25);}
.qt-green:hover{background:rgba(16,185,129,0.22);box-shadow:0 4px 16px rgba(16,185,129,0.2);}
.qt-amber{background:rgba(245,158,11,0.12);color:#f59e0b;border-color:rgba(245,158,11,0.25);}
.qt-amber:hover{background:rgba(245,158,11,0.22);box-shadow:0 4px 16px rgba(245,158,11,0.2);}
.qt-blue{background:rgba(59,130,246,0.12);color:#60a5fa;border-color:rgba(59,130,246,0.25);}
.qt-blue:hover{background:rgba(59,130,246,0.22);box-shadow:0 4px 16px rgba(59,130,246,0.2);}

@media(max-width:1100px){
    .metrics-grid{grid-template-columns:repeat(2,1fr);}
    .content-grid,.content-grid-3{grid-template-columns:1fr;}
    .nav-center{display:none;}
    .quick-toolbar{gap:8px;}
    .qt-btn span{display:none;}
    .qt-btn{padding:10px 12px;}
}
</style>
</head>
<body>

<!-- TOP NAV -->
<nav class="topnav">
    <div class="logo-area">
        <img src="assets/shoe_logo_green.png" alt="WalkOn" style="width:36px;height:auto;">
        <span class="logo-text">WALK<span style="color:var(--green);">ON</span></span>
    </div>
    <div class="nav-center">
        <a href="entrepreneur_dashboard.php" class="nav-pill active"><i class="fas fa-rocket"></i> Hub</a>
        <a href="add_listing.php" class="nav-pill"><i class="fas fa-plus-circle"></i> Add Listing</a>
        <a href="my_listings.php" class="nav-pill"><i class="fas fa-boxes"></i> My Inventory</a>
        <a href="bulk_operations.php" class="nav-pill"><i class="fas fa-layer-group"></i> Bulk Edit</a>
        <a href="analytics.php" class="nav-pill"><i class="fas fa-chart-line"></i> Analytics</a>
        <a href="sellers.php" class="nav-pill"><i class="fas fa-store"></i> Sellers</a>
        <a href="my_orders.php" class="nav-pill"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="my_wallet.php" class="nav-pill"><i class="fas fa-wallet"></i> Wallet</a>
    </div>
    <div class="nav-right">
        <span class="role-tag">⚡ Entrepreneur</span>
        <a href="profile.php">
            <img class="avatar" src="https://ui-avatars.com/api/?name=<?= urlencode($display_name) ?>&background=f97316&color=fff&bold=true" alt="<?= htmlspecialchars($first_name) ?>">
        </a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<!-- PAGE -->
<div class="page">

    <!-- HERO -->
    <div class="hero-header">
        <div class="hero-text">
            <h1>Welcome back, <span><?= htmlspecialchars($first_name) ?></span> 🚀</h1>
            <p><?= date('l, F j, Y') ?> &nbsp;·&nbsp; You're operating in dual mode — selling &amp; buying</p>
        </div>
        <div class="hero-actions">
            <a href="add_listing.php" class="btn-grad btn-orange"><i class="fas fa-plus"></i> Add Listing</a>
            <a href="bulk_operations.php" class="btn-grad btn-ghost"><i class="fas fa-layer-group"></i> Bulk Edit</a>
            <a href="shop.php" class="btn-grad btn-ghost"><i class="fas fa-shopping-bag"></i> Shop</a>
            <a href="export_orders.php" class="btn-grad btn-ghost" style="border-color: var(--secondary); color: var(--secondary);"><i class="fas fa-file-export"></i> Export CSV</a>
        </div>
    </div>

    <!-- QUICK ACCESS TOOLBAR -->
    <div class="quick-toolbar">
        <a href="add_listing.php" class="qt-btn qt-orange"><i class="fas fa-plus-circle"></i><span>Add Listing</span></a>
        <a href="my_listings.php" class="qt-btn qt-purple"><i class="fas fa-boxes"></i><span>My Inventory</span></a>
        <a href="bulk_operations.php" class="qt-btn qt-cyan"><i class="fas fa-layer-group"></i><span>Bulk Edit</span></a>
        <a href="analytics.php" class="qt-btn qt-green"><i class="fas fa-chart-line"></i><span>Analytics</span></a>
        <a href="sellers.php" class="qt-btn qt-amber"><i class="fas fa-store"></i><span>Sellers</span></a>
        <a href="my_orders.php" class="qt-btn qt-blue"><i class="fas fa-shopping-bag"></i><span>Orders</span></a>
    </div>

    <!-- METRICS -->
    <div class="metrics-grid">
        <div class="metric-card mc-orange">
            <div class="metric-top">
                <div class="metric-icon mi-orange"><i class="fas fa-fire"></i></div>
                <span class="metric-trend trend-up"><i class="fas fa-arrow-up"></i> Selling</span>
            </div>
            <div class="metric-value">₹<?= number_format($total_revenue, 0) ?></div>
            <div class="metric-label">Total Revenue Earned</div>
        </div>
        <div class="metric-card mc-purple">
            <div class="metric-top">
                <div class="metric-icon mi-purple"><i class="fas fa-bolt"></i></div>
                <span class="metric-trend trend-neutral"><i class="fas fa-minus"></i> Orders</span>
            </div>
            <div class="metric-value"><?= $total_orders ?></div>
            <div class="metric-label">Sales Fulfilled</div>
        </div>
        <div class="metric-card mc-cyan">
            <div class="metric-top">
                <div class="metric-icon mi-cyan"><i class="fas fa-layer-group"></i></div>
                <span class="metric-trend <?= $low_stock > 0 ? 'trend-down' : 'trend-up' ?>">
                    <i class="fas fa-<?= $low_stock > 0 ? 'exclamation' : 'check' ?>"></i>
                    <?= $low_stock > 0 ? "$low_stock alerts" : 'Healthy' ?>
                </span>
            </div>
            <div class="metric-value"><?= $total_products ?></div>
            <div class="metric-label">Active Listings</div>
        </div>
        <div class="metric-card mc-green">
            <div class="metric-top">
                <div class="metric-icon mi-green"><i class="fas fa-shopping-cart"></i></div>
                <span class="metric-trend trend-neutral"><i class="fas fa-minus"></i> Buying</span>
            </div>
            <div class="metric-value"><?= $total_purchases ?></div>
            <div class="metric-label">My Purchases</div>
        </div>
    </div>

    <!-- SELLING SECTION -->
    <div class="dual-header">
        <span class="mode-badge mode-sell">🔥 Selling Mode</span>
        <span style="color:var(--muted);font-size:0.8rem;">Your store performance</span>
    </div>

    <div class="content-grid" style="margin-bottom:28px;">
        <div class="glass-card">
            <div class="gc-header">
                <div class="gc-title">Revenue Trend — Last 7 Days</div>
                <a href="analytics.php" class="gc-link">Full Analytics →</a>
            </div>
            <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
        </div>

        <div class="glass-card">
            <div class="gc-header">
                <div class="gc-title">Recent Sales</div>
                <a href="my_orders.php" class="gc-link">View All →</a>
            </div>
            <?php if(empty($recent_sales)): ?>
            <div class="empty-state"><i class="fas fa-inbox"></i>No sales yet. Add your first listing!</div>
            <?php else: ?>
            <table class="sales-table">
                <thead><tr><th>Order</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach($recent_sales as $s):
                    $st = strtolower($s['status'] ?? 'pending');
                    $sc = match($st){ 'delivered'=>'sc-delivered','processing'=>'sc-processing','cancelled'=>'sc-cancelled',default=>'sc-pending' };
                ?>
                <tr>
                    <td><span class="s-id">#<?= str_pad($s['id'],4,'0',STR_PAD_LEFT) ?></span><br><span style="font-size:0.7rem;color:var(--muted)"><?= date('M j', strtotime($s['order_date'])) ?></span></td>
                    <td style="font-weight:700;">₹<?= number_format($s['total_price'],0) ?></td>
                    <td><span class="s-chip <?= $sc ?>"><?= ucfirst($st) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- BUYING SECTION -->
    <div class="dual-header">
        <span class="mode-badge mode-buy">💜 Buying Mode</span>
        <span style="color:var(--muted);font-size:0.8rem;">Your purchase history</span>
    </div>

    <div class="content-grid-3">
        <div class="glass-card">
            <div class="gc-header">
                <div class="gc-title">My Recent Purchases</div>
                <a href="my_orders.php?view=purchases" class="gc-link">View All →</a>
            </div>
            <?php if(empty($recent_purchases)): ?>
            <div class="empty-state"><i class="fas fa-shopping-bag"></i>No purchases yet. <a href="shop.php" style="color:var(--purple);">Start shopping!</a></div>
            <?php else: ?>
            <div class="purchase-list">
                <?php foreach($recent_purchases as $b): ?>
                <div class="purchase-item">
                    <div class="pi-left">
                        <div class="pi-id">#<?= str_pad($b['id'],4,'0',STR_PAD_LEFT) ?></div>
                        <div class="pi-date"><?= date('M j, Y', strtotime($b['order_date'])) ?></div>
                    </div>
                    <div class="pi-right">
                        <div class="pi-amount">₹<?= number_format($b['total_price'],0) ?></div>
                        <div class="pi-status"><?= $b['status'] ?? 'pending' ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="wallet-card">
            <div class="wallet-label">💰 Estimated Wallet</div>
            <div class="wallet-amount">₹<?= number_format($total_revenue * 0.9, 0) ?></div>
            <div class="wallet-sub">After platform commission</div>
            <a href="my_wallet.php" class="wallet-btn"><i class="fas fa-wallet"></i> Open Wallet</a>
        </div>

        <div class="glass-card">
            <div class="gc-header"><div class="gc-title">Quick Actions</div></div>
            <div class="quick-grid">
                <a href="add_listing.php" class="ql-item"><i class="fas fa-plus-circle"></i>Add Listing</a>
                <a href="my_listings.php" class="ql-item"><i class="fas fa-boxes"></i>Inventory</a>
                <a href="shop.php" class="ql-item"><i class="fas fa-store"></i>Shop Now</a>
                <a href="profile.php" class="ql-item"><i class="fas fa-user-cog"></i>Profile</a>
                <a href="marketplaces.php" class="ql-item"><i class="fas fa-globe"></i>Channels</a>
                <a href="my_orders.php" class="ql-item"><i class="fas fa-history"></i>History</a>
            </div>
        </div>
    </div>

</div><!-- /page -->

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const grad = ctx.createLinearGradient(0,0,0,220);
grad.addColorStop(0,'rgba(249,115,22,0.15)');
grad.addColorStop(1,'rgba(249,115,22,0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?= json_encode($revenue_data) ?>,
            borderColor: '#f97316',
            borderWidth: 3,
            pointBackgroundColor: '#f97316',
            pointRadius: 6,
            pointHoverRadius: 8,
            tension: 0.4,
            fill: true,
            backgroundColor: grad
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#ffffff',
                borderColor: 'rgba(249,115,22,0.3)',
                borderWidth: 1,
                titleColor: '#1e293b',
                bodyColor: '#475569',
                titleFont: { family: "'Space Grotesk', sans-serif" },
                bodyFont: { family: "'Space Grotesk', sans-serif" },
                callbacks: { label: ctx => ' ₹' + ctx.parsed.y.toLocaleString() }
            }
        },
        scales: {
            x: { 
                grid: { color: 'rgba(0,0,0,0.05)' }, 
                ticks: { color: '#64748b', font: { family: "'Space Grotesk', sans-serif", weight: '600', size: 11 } } 
            },
            y: { 
                grid: { color: 'rgba(0,0,0,0.05)' }, 
                ticks: { color: '#64748b', font: { family: "'Space Grotesk', sans-serif", weight: '600', size: 11 }, callback: v => '₹' + v } 
            }
        }
    }
});
</script>
</body>
</html>
