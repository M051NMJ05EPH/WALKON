<?php
// store_dashboard.php - Premium Retail Store Command Center
session_start();
include 'config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'store' && $_SESSION['role'] !== 'store_owner')) {
    header("Location: login.php");
    exit();
}

$email      = $_SESSION['email'];
$first_name = $_SESSION['first_name'] ?? 'Store Owner';
$last_name  = $_SESSION['last_name'] ?? '';
$display_name = trim("$first_name $last_name") ?: $email;
$seller_id  = $_SESSION['seller_id'] ?? null;

// Defaults
$total_orders = $total_revenue = $total_products = $low_stock = $avg_order_value = 0;
$chart_labels = $revenue_data = $order_data = [];
$category_stats = $recent_orders = [];
$store_name = 'My Store';

try {
    if (!$seller_id) {
        $s = $pdo->prepare("SELECT id, business_name FROM sellers WHERE email = ?");
        $s->execute([$email]);
        $row = $s->fetch();
        if ($row) { $seller_id = $row['id']; $store_name = $row['business_name'] ?? $store_name; $_SESSION['seller_id'] = $seller_id; }
        else { $seller_id = -1; }
    } else {
        $s = $pdo->prepare("SELECT business_name FROM sellers WHERE id = ?");
        $s->execute([$seller_id]);
        $row = $s->fetch();
        if ($row) $store_name = $row['business_name'] ?? $store_name;
    }

    if ($seller_id != -1) {
        $total_orders   = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE seller_id = ?"); $total_orders->execute([$seller_id]); $total_orders = $total_orders->fetchColumn() ?: 0;
        $rev_stmt       = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status != 'cancelled' AND seller_id = ?"); $rev_stmt->execute([$seller_id]); $total_revenue = (float)$rev_stmt->fetchColumn();
        $avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;
        $prod_stmt      = $pdo->prepare("SELECT COUNT(*) FROM product_base WHERE seller_id = ?"); $prod_stmt->execute([$seller_id]); $total_products = $prod_stmt->fetchColumn() ?: 0;
        $low_stmt       = $pdo->prepare("SELECT COUNT(*) FROM product_stock ps JOIN product_base pb ON ps.product_id = pb.id WHERE ps.quantity < 10 AND pb.seller_id = ?"); $low_stmt->execute([$seller_id]); $low_stock = $low_stmt->fetchColumn() ?: 0;

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chart_labels[] = date('M j', strtotime($date));
            $r = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE seller_id=? AND DATE(order_date)=? AND status!='cancelled'"); $r->execute([$seller_id,$date]); $revenue_data[] = (float)$r->fetchColumn();
            $o = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE seller_id=? AND DATE(order_date)=?"); $o->execute([$seller_id,$date]); $order_data[] = (int)$o->fetchColumn();
        }

        $cat = $pdo->prepare("SELECT c.name, COUNT(pb.id) as cnt FROM product_base pb JOIN categories c ON pb.category_id=c.id WHERE pb.seller_id=? GROUP BY c.id ORDER BY cnt DESC LIMIT 6"); $cat->execute([$seller_id]); $category_stats = $cat->fetchAll(PDO::FETCH_ASSOC);
        $rec = $pdo->prepare("SELECT * FROM orders WHERE seller_id=? ORDER BY order_date DESC LIMIT 6"); $rec->execute([$seller_id]); $recent_orders = $rec->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) { /* silent */ }

$today_revenue = $revenue_data[6] ?? 0;
$yesterday_revenue = $revenue_data[5] ?? 0;
$revenue_change = $yesterday_revenue > 0 ? round((($today_revenue - $yesterday_revenue) / $yesterday_revenue) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($store_name) ?> — Store Dashboard | WALKON</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
    --primary:    #2563eb;       /* Royal Blue */
    --primary-hover: #1d4ed8;
    --navy:       #1e293b;
    --sidebar-w:  260px;
    --bg:         #ffffff;
    --white:      #ffffff;
    --border:     #e2e8f0;
    --text:       #1e293b;
    --muted:      #64748b;
    --red:        #ef4444;
    --amber:      #f59e0b;
    --green:      #10b981;
    --sky-light:  #f0f9ff;
    --sky-mid:    #e0f2fe;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Plus Jakarta Sans',sans-serif;}
body{
    background: radial-gradient(circle at 10% 20%, var(--sky-mid) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, var(--sky-light) 0%, transparent 40%),
                var(--bg);
    color:var(--text);display:flex;min-height:100vh;
}

/* ── SIDEBAR ── */
.sidebar{
    width:var(--sidebar-w);background:var(--white);border-right:1px solid var(--border);
    position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;
    display:flex;flex-direction:column;
}
.sidebar-brand{
    padding:28px 24px 20px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;gap:12px;
}
.brand-icon{
    width:40px;height:40px;background:var(--primary);border-radius:12px;
    display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;
}
.brand-text h2{font-size:1rem;font-weight:800;color:var(--navy);}
.brand-text span{font-size:0.72rem;color:var(--muted);font-weight:500;}

.sidebar-section{padding:20px 16px 8px;font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);}
.nav-item{
    display:flex;align-items:center;gap:12px;padding:11px 16px;margin:2px 8px;
    border-radius:10px;text-decoration:none;color:var(--muted);font-weight:600;font-size:0.875rem;
    transition:all 0.2s;cursor:pointer;
}
.nav-item i{width:20px;text-align:center;font-size:0.95rem;}
.nav-item:hover{background:var(--sky-light);color:var(--primary);}
.nav-item.active{background:var(--sky-mid);color:var(--primary);font-weight:700;}
.nav-item.active i{color:var(--primary);}
.nav-item.danger{color:#ef4444;}
.nav-item.danger:hover{background:#fef2f2;}

.sidebar-footer{margin-top:auto;padding:16px;border-top:1px solid var(--border);}
.user-pill{
    display:flex;align-items:center;gap:10px;padding:10px 12px;
    background:var(--bg);border-radius:12px;
}
.user-pill img{width:36px;height:36px;border-radius:10px;object-fit:cover;}
.user-pill-info h4{font-size:0.8rem;font-weight:700;color:var(--text);}
.user-pill-info span{font-size:0.7rem;color:var(--muted);}

/* ── MAIN ── */
.main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;}

/* Top Bar */
.topbar{
    background:var(--white);border-bottom:1px solid var(--border);
    padding:16px 32px;display:flex;align-items:center;justify-content:space-between;
    position:sticky;top:0;z-index:50;
}
.topbar-left h1{font-size:1.4rem;font-weight:800;color:var(--navy);font-family: 'Plus Jakarta Sans', sans-serif;}
.topbar-left p{font-size:0.85rem;color:var(--muted);margin-top:2px;}
.topbar-right{display:flex;align-items:center;gap:12px;}
.topbar-btn{
    display:flex;align-items:center;gap:8px;padding:9px 18px;border-radius:10px;
    font-weight:700;font-size:0.8rem;text-decoration:none;transition:0.2s;border:none;cursor:pointer;
}
.btn-primary-teal{background:var(--primary);color:#fff;}
.btn-primary-teal:hover{background:var(--primary-hover);transform:translateY(-2px);box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);}
.btn-outline{background:transparent;color:var(--text);border:1px solid var(--border);}
.btn-outline:hover{background:var(--bg);}
.notif-btn{
    width:38px;height:38px;border-radius:10px;border:1px solid var(--border);
    background:var(--white);display:flex;align-items:center;justify-content:center;
    color:var(--muted);cursor:pointer;transition:0.2s;position:relative;
}
.notif-btn:hover{background:var(--bg);}
.notif-dot{
    position:absolute;top:8px;right:8px;width:7px;height:7px;
    background:var(--red);border-radius:50%;border:2px solid #fff;
}

/* Content */
.content{padding:28px 32px;flex:1;}

/* KPI Strip */
.kpi-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:28px;}
.kpi-card{
    background:var(--white);border:1px solid var(--border);border-radius:16px;
    padding:22px 24px;display:flex;align-items:flex-start;gap:16px;
    box-shadow:var(--shadow-sm);transition:0.3s;
}
.kpi-card:hover{box-shadow:var(--shadow-md);transform:translateY(-2px);}
.kpi-icon{
    width:46px;height:46px;border-radius:12px;display:flex;align-items:center;
    justify-content:center;font-size:1.1rem;flex-shrink:0;
}
.kpi-icon.teal{background:var(--sky-light);color:var(--primary);}
.kpi-icon.amber{background:#fff7ed;color:var(--amber);}
.kpi-icon.blue{background:var(--sky-mid);color:var(--primary);}
.kpi-icon.red{background:#fef2f2;color:var(--red);}
.kpi-body{}
.kpi-label{font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--muted);}
.kpi-value{font-size:1.65rem;font-weight:800;color:var(--navy);margin:4px 0 6px;line-height:1;}
.kpi-badge{
    display:inline-flex;align-items:center;gap:4px;font-size:0.7rem;font-weight:700;
    padding:3px 8px;border-radius:20px;
}
.badge-up{background:#f0fdf4;color:#16a34a;}
.badge-down{background:#fef2f2;color:#dc2626;}
.badge-neutral{background:#f1f5f9;color:var(--muted);}

/* Charts Row */
.charts-row{display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:28px;}
.card{
    background:var(--white);border:1px solid var(--border);border-radius:16px;
    padding:24px;box-shadow:var(--shadow-sm);
}
.card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
.card-title{font-size:0.95rem;font-weight:800;color:var(--navy);}
.card-subtitle{font-size:0.75rem;color:var(--muted);margin-top:2px;}
.chart-wrap{height:240px;position:relative;}

/* Category Pills */
.cat-list{display:flex;flex-direction:column;gap:10px;}
.cat-item{display:flex;align-items:center;gap:10px;}
.cat-bar-wrap{flex:1;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden;}
.cat-bar{height:100%;background:var(--teal);border-radius:3px;transition:width 0.8s ease;}
.cat-name{font-size:0.8rem;font-weight:600;color:var(--text);width:100px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.cat-count{font-size:0.75rem;font-weight:700;color:var(--muted);width:30px;text-align:right;}

/* Bottom Row */
.bottom-row{display:grid;grid-template-columns:3fr 1fr;gap:20px;}

/* Orders Table */
.orders-table{width:100%;border-collapse:collapse;}
.orders-table th{
    text-align:left;padding:10px 14px;font-size:0.7rem;font-weight:800;
    text-transform:uppercase;letter-spacing:0.8px;color:var(--muted);
    border-bottom:1px solid var(--border);background:#fafafa;
}
.orders-table td{padding:14px;font-size:0.85rem;border-bottom:1px solid var(--border);}
.orders-table tr:last-child td{border-bottom:none;}
.orders-table tr:hover td{background:#fafafa;}
.order-id{font-weight:800;color:var(--navy);}
.status-chip{
    display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
    border-radius:20px;font-size:0.7rem;font-weight:700;text-transform:uppercase;
}
.chip-delivered{background:#f0fdf4;color:#16a34a;}
.chip-pending{background:#fffbeb;color:#d97706;}
.chip-processing{background:#eff6ff;color:#2563eb;}
.chip-cancelled{background:#fef2f2;color:#dc2626;}

/* Quick Actions */
.quick-actions{display:flex;flex-direction:column;gap:10px;}
.qa-btn{
    display:flex;align-items:center;gap:12px;padding:14px 16px;
    background:var(--bg);border:1px solid var(--border);border-radius:12px;
    text-decoration:none;color:var(--text);font-weight:600;font-size:0.85rem;
    transition:0.2s;
}
.qa-btn:hover{background:var(--teal-pale);border-color:var(--teal);color:var(--teal);}
.qa-btn i{width:20px;text-align:center;color:var(--teal);}

.empty-row td{text-align:center;padding:40px;color:var(--muted);}

@media(max-width:1200px){
    .kpi-strip{grid-template-columns:repeat(2,1fr);}
    .charts-row,.bottom-row{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="assets/shoe_logo_green.png" alt="WalkOn" style="width:40px;height:auto;">
        <div class="brand-text">
            <h2 style="display:flex;align-items:center;gap:2px;font-size:1.2rem;">WALK<span style="color:var(--teal);">ON</span></h2>
            <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px;"><?= htmlspecialchars($store_name) ?></span>
        </div>
    </div>

    <div class="sidebar-section">Overview</div>
    <a href="store_dashboard.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a>

    <div class="sidebar-section">Inventory</div>
    <a href="add_listing.php" class="nav-item"><i class="fas fa-plus-circle"></i> Add Listing</a>
    <a href="my_listings.php" class="nav-item"><i class="fas fa-boxes"></i> My Inventory</a>
    <a href="bulk_operations.php" class="nav-item"><i class="fas fa-layer-group"></i> Bulk Edit</a>

    <div class="sidebar-section">Insights</div>
    <a href="analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
    <a href="sellers.php" class="nav-item"><i class="fas fa-store"></i> Sellers</a>

    <div class="sidebar-section">Sales</div>
    <a href="my_orders.php" class="nav-item"><i class="fas fa-shopping-bag"></i> Orders</a>
    <a href="my_wallet.php" class="nav-item"><i class="fas fa-wallet"></i> Payouts</a>
    <a href="marketplaces.php" class="nav-item"><i class="fas fa-globe"></i> Channels</a>

    <div class="sidebar-section">Settings</div>
    <a href="store_settings.php" class="nav-item"><i class="fas fa-cog"></i> Store Settings</a>
    <a href="profile.php" class="nav-item"><i class="fas fa-user"></i> Profile</a>
    <a href="logout.php" class="nav-item danger"><i class="fas fa-sign-out-alt"></i> Logout</a>

    <div class="sidebar-footer">
        <div class="user-pill">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($display_name) ?>&background=0d9488&color=fff&bold=true" alt="User">
            <div class="user-pill-info">
                <h4><?= htmlspecialchars($first_name) ?></h4>
                <span>Store Owner</span>
            </div>
        </div>
    </div>
</aside>

<!-- MAIN -->
<div class="main">

    <!-- TOP BAR -->
    <div class="topbar">
        <div class="topbar-left">
            <h1>Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, <?= htmlspecialchars($first_name) ?> 👋</h1>
            <p><?= date('l, F j, Y') ?> &nbsp;·&nbsp; Store performance overview</p>
        </div>
        <div class="topbar-right">
            <div class="notif-btn"><i class="fas fa-bell"></i><span class="notif-dot"></span></div>
            <a href="analytics.php" class="topbar-btn btn-outline"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="bulk_operations.php" class="topbar-btn btn-outline"><i class="fas fa-layer-group"></i> Bulk Edit</a>
            <a href="my_listings.php" class="topbar-btn btn-outline"><i class="fas fa-boxes"></i> Inventory</a>
            <a href="export_orders.php" class="topbar-btn btn-outline" style="border-color: var(--teal); color: var(--teal);"><i class="fas fa-file-export"></i> Export CSV</a>
            <a href="add_listing.php" class="topbar-btn btn-primary-teal"><i class="fas fa-plus"></i> Add Listing</a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- KPI Strip -->
        <div class="kpi-strip">
            <div class="kpi-card">
                <div class="kpi-icon teal"><i class="fas fa-coins"></i></div>
                <div class="kpi-body">
                    <div class="kpi-label">Total Revenue</div>
                    <div class="kpi-value">₹<?= number_format($total_revenue, 0) ?></div>
                    <span class="kpi-badge <?= $revenue_change >= 0 ? 'badge-up' : 'badge-down' ?>">
                        <i class="fas fa-arrow-<?= $revenue_change >= 0 ? 'up' : 'down' ?>"></i>
                        <?= abs($revenue_change) ?>% today
                    </span>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon amber"><i class="fas fa-shopping-bag"></i></div>
                <div class="kpi-body">
                    <div class="kpi-label">Total Orders</div>
                    <div class="kpi-value"><?= $total_orders ?></div>
                    <span class="kpi-badge badge-neutral"><i class="fas fa-clock"></i> All time</span>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon blue"><i class="fas fa-receipt"></i></div>
                <div class="kpi-body">
                    <div class="kpi-label">Avg. Order Value</div>
                    <div class="kpi-value">₹<?= number_format($avg_order_value, 0) ?></div>
                    <span class="kpi-badge badge-neutral"><i class="fas fa-info-circle"></i> Per order</span>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon <?= $low_stock > 0 ? 'red' : 'teal' ?>"><i class="fas fa-<?= $low_stock > 0 ? 'exclamation-triangle' : 'check-circle' ?>"></i></div>
                <div class="kpi-body">
                    <div class="kpi-label">Live Products</div>
                    <div class="kpi-value"><?= $total_products ?></div>
                    <span class="kpi-badge <?= $low_stock > 0 ? 'badge-down' : 'badge-up' ?>">
                        <?= $low_stock > 0 ? "$low_stock low stock" : 'All stocked' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Revenue & Orders — Last 7 Days</div>
                        <div class="card-subtitle">Daily performance breakdown</div>
                    </div>
                    <a href="analytics.php" style="font-size:0.78rem;color:var(--teal);font-weight:700;text-decoration:none;">View Full Report →</a>
                </div>
                <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Category Split</div>
                        <div class="card-subtitle">Products by category</div>
                    </div>
                </div>
                <?php if (!empty($category_stats)):
                    $max_cat = max(array_column($category_stats, 'cnt') ?: [1]);
                ?>
                <div class="cat-list">
                    <?php foreach($category_stats as $cat): ?>
                    <div class="cat-item">
                        <span class="cat-name"><?= htmlspecialchars($cat['name']) ?></span>
                        <div class="cat-bar-wrap">
                            <div class="cat-bar" style="width:<?= round(($cat['cnt']/$max_cat)*100) ?>%"></div>
                        </div>
                        <span class="cat-count"><?= $cat['cnt'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color:var(--muted);font-size:0.85rem;text-align:center;padding:40px 0;">No products listed yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="bottom-row">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Recent Orders</div>
                        <div class="card-subtitle">Latest sales activity</div>
                    </div>
                    <a href="my_orders.php" style="font-size:0.78rem;color:var(--teal);font-weight:700;text-decoration:none;">View All →</a>
                </div>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recent_orders)): ?>
                        <tr class="empty-row"><td colspan="4"><i class="fas fa-inbox" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:8px;"></i>No orders yet</td></tr>
                        <?php else: foreach($recent_orders as $o):
                            $status = strtolower($o['status'] ?? 'pending');
                            $chip = match($status) { 'delivered'=>'chip-delivered','processing'=>'chip-processing','cancelled'=>'chip-cancelled',default=>'chip-pending' };
                        ?>
                        <tr>
                            <td><span class="order-id">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></span></td>
                            <td style="color:var(--muted)"><?= date('M j, Y', strtotime($o['order_date'])) ?></td>
                            <td style="font-weight:700;">₹<?= number_format($o['total_price'],2) ?></td>
                            <td><span class="status-chip <?= $chip ?>"><?= ucfirst($status) ?></span></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title">Quick Actions</div></div>
                <div class="quick-actions">
                    <a href="add_listing.php" class="qa-btn"><i class="fas fa-plus-circle"></i> Add New Product</a>
                    <a href="my_listings.php" class="qa-btn"><i class="fas fa-boxes"></i> Manage Inventory</a>
                    <a href="my_orders.php" class="qa-btn"><i class="fas fa-shopping-bag"></i> Process Orders</a>
                    <a href="my_wallet.php" class="qa-btn"><i class="fas fa-wallet"></i> View Payouts</a>
                    <a href="store_settings.php" class="qa-btn"><i class="fas fa-cog"></i> Store Settings</a>
                    <a href="marketplaces.php" class="qa-btn"><i class="fas fa-globe"></i> Sync Channels</a>
                </div>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const labels = <?= json_encode($chart_labels) ?>;
const revenueData = <?= json_encode($revenue_data) ?>;
const orderData = <?= json_encode($order_data) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Revenue (₹)',
                data: revenueData,
                backgroundColor: 'rgba(13,148,136,0.15)',
                borderColor: '#0d9488',
                borderWidth: 2,
                borderRadius: 6,
                yAxisID: 'y',
            },
            {
                label: 'Orders',
                data: orderData,
                type: 'line',
                borderColor: '#f59e0b',
                borderWidth: 2.5,
                pointBackgroundColor: '#f59e0b',
                pointRadius: 4,
                tension: 0.4,
                fill: false,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', labels: { font: { weight: '700', size: 11 }, padding: 16 } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { weight: '600', size: 11 }, color: '#64748b' } },
            y: { position: 'left', grid: { color: '#f1f5f9' }, ticks: { font: { weight: '600', size: 11 }, color: '#64748b', callback: v => '₹' + v } },
            y1: { position: 'right', grid: { display: false }, ticks: { font: { weight: '600', size: 11 }, color: '#f59e0b' } }
        }
    }
});
</script>
</body>
</html>
