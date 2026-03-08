<?php
session_start();
include 'config.php';

// Auth Check & Role Verification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    // 1. Core Analytics
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
    $total_stores = $pdo->query("SELECT COUNT(*) FROM sellers")->fetchColumn() ?: 0;
    $total_products = $pdo->query("SELECT COUNT(*) FROM product_base")->fetchColumn() ?: 0;
    $total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn() ?: 0;

    // 2. Order Breakdown (Status)
    $pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending' OR status = 'processing'")->fetchColumn() ?: 0;
    $delivered_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn() ?: 0;
    $canceled_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'canceled'")->fetchColumn() ?: 0;
    $confirmed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'confirmed'")->fetchColumn() ?: 0;

    // 3. Admin Wallet Metrics
    $total_commission = $pdo->query("SELECT SUM(commission_deducted) FROM wallet_transactions")->fetchColumn() ?: 0;
    $in_house_earning = ($total_commission * 0.4); 
    $total_tax = ($total_commission * 0.05);

    // 4. Pending Governance Actions
    $brand_requests = $pdo->query("SELECT ba.*, b.name as brand_name, s.business_name as seller_name, s.name as owner_name
                                  FROM brand_approvals ba
                                  JOIN brands b ON ba.brand_id = b.id
                                  JOIN sellers s ON ba.seller_id = s.id
                                  WHERE ba.status = 'pending'
                                  ORDER BY ba.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    $product_queue = $pdo->query("SELECT pb.*, s.business_name as seller_name, b.name as brand_name
                                 FROM product_base pb
                                 JOIN sellers s ON pb.seller_id = s.id
                                 LEFT JOIN product_specs spec ON pb.id = spec.product_id
                                 LEFT JOIN brands b ON spec.brand_id = b.id
                                 WHERE pb.approval_status = 'pending'
                                 ORDER BY pb.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    $disputes = $pdo->query("SELECT d.*, s.business_name as seller_name, u.first_name as customer_name
                             FROM disputes d
                             JOIN sellers s ON d.seller_id = s.id
                             JOIN users u ON d.customer_id = u.id
                             WHERE d.status = 'open'
                             ORDER BY d.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | 6Valley inspired</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;       /* Royal Blue */
            --primary-hover: #1d4ed8;
            --accent: #10b981;        /* Emerald Green */
            --bg: #ffffff;
            --text-dark: #1e293b;     /* Deep Navy */
            --text-muted: #64748b;
            --white: #ffffff;
            --border: #e2e8f0;
            --sky-light: #f0f9ff;
            --sky-mid: #e0f2fe;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { 
            background: radial-gradient(circle at 10% 20%, var(--sky-mid) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, var(--sky-light) 0%, transparent 40%),
                        var(--bg);
            display: flex; color: var(--text-dark); overflow-x: hidden; 
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #fff;
            min-height: 100vh;
            color: var(--text-dark);
            position: fixed;
            left: 0; top: 0;
            transition: 0.3s;
            z-index: 1000;
            border-right: 1px solid var(--border);
        }
        .sidebar-header {
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(0,0,0,0.1);
            margin-bottom: 10px;
        }
        .sidebar-header img { height: 35px; }
        .sidebar-header span { font-size: 1.4rem; font-weight: 800; color: var(--primary); }

        .sidebar-search { padding: 10px 20px; margin-bottom: 20px; }
        .sidebar-search input {
            width: 100%;
            padding: 10px 15px;
            background: var(--sky-light);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-dark);
            outline: none;
            font-size: 0.9rem;
        }
        .sidebar-search input::placeholder { color: rgba(255,255,255,0.5); }

        .nav-label { padding: 15px 25px 5px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 800; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .nav-link i { color: var(--primary); width: 18px; text-align: center; }
        .nav-link:hover, .nav-link.active { background: var(--sky-mid); color: var(--primary); border-left: 4px solid var(--primary); }

        /* Content Area */
        .content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
            min-height: 100vh;
        }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .user-profile { display: flex; align-items: center; gap: 12px; cursor: pointer; }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }

        /* Dashboard Highlights */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .section-header h2 { font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .section-header h2 i { color: var(--primary); }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border);
        }
        .stat-top { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .stat-icon-wrapper {
            width: 45px; height: 45px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
        .stat-main-val { font-size: 1.5rem; font-weight: 800; display: block; }
        .stat-main-lbl { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; }

        .stat-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #f3f4f6;
        }
        .detail-item { font-size: 0.75rem; display: flex; justify-content: space-between; align-items: center; }
        .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin-right: 4px; }

        /* Admin Wallet */
        .wallet-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 40px;
        }
        .inhouse-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--card-shadow);
            display: flex; align-items: center; gap: 20px;
            background-image: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, transparent 100%);
        }
        .inhouse-card i { font-size: 2.5rem; color: var(--primary); }
        .inhouse-val { font-size: 2rem; font-weight: 800; }
        
        .commission-grid {
            background: var(--white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .comm-box {
            padding: 15px;
            border-radius: 12px;
            background: #fafafa;
            border-left: 4px solid #e5e7eb;
        }
        .comm-box.active { border-color: var(--primary); background: var(--sky-light); }
        .comm-box h5 { font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 5px; }
        .comm-val { font-size: 1.25rem; font-weight: 700; }

        /* Approval Tables */
        .approval-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .card { background: var(--white); border-radius: 20px; padding: 25px; box-shadow: var(--card-shadow); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h3 { font-size: 1rem; font-weight: 700; border-bottom: 2px solid var(--primary); padding-bottom: 5px; }

        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; font-size: 0.75rem; color: var(--text-muted); padding: 10px; background: #f9fafb; border-bottom: 1px solid #f3f4f6; }
        .table td { padding: 12px 10px; font-size: 0.85rem; border-bottom: 1px solid #f3f4f6; }

        .status-pill { padding: 3px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .pill-pending { background: #fff7ed; color: #c2410c; }

        .btn-sm {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }
        .btn-approve { background: #dcfce7; color: #166534; }
        .btn-approve:hover { background: #166534; color: #fff; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="assets/shoe_logo_green.png" alt="W">
            <span>WALKON</span>
        </div>

        <div class="sidebar-search">
            <input type="text" placeholder="Search menu...">
        </div>

        <nav>
            <a href="#" class="nav-link active"><i class="fas fa-grip-horizontal"></i> Dashboard</a>
            <a href="pos.php" class="nav-link"><i class="fas fa-cash-register"></i> POS</a>

            <div class="nav-label">ORDER MANAGEMENT</div>
            <a href="admin/orders.php" class="nav-link"><i class="fas fa-shopping-basket"></i> Orders</a>
            <a href="admin/refunds.php" class="nav-link"><i class="fas fa-undo-alt"></i> Refund Requests</a>

            <div class="nav-label">PRODUCT MANAGEMENT</div>
            <a href="admin/categories.php" class="nav-link"><i class="fas fa-layer-group"></i> Category Setup</a>
            <a href="admin/brands.php" class="nav-link"><i class="fas fa-tags"></i> Brands</a>
            <a href="admin/listings.php" class="nav-link"><i class="fas fa-box-open"></i> In-House Products</a>

            <div class="nav-label">VENDOR MANAGEMENT</div>
            <a href="admin/sellers.php" class="nav-link"><i class="fas fa-store"></i> Vendor List</a>
            <a href="admin/payouts.php" class="nav-link"><i class="fas fa-wallet"></i> Withdraws</a>

            <div class="nav-label">PLATFORM HUBS</div>
            <a href="store_dashboard.php" class="nav-link"><i class="fas fa-store-alt"></i> Retail Store Hub</a>
            <a href="entrepreneur_dashboard.php" class="nav-link"><i class="fas fa-rocket"></i> Entrepreneur Hub</a>

            <div class="nav-label">Settings</div>
            <a href="logout.php" class="nav-link"><i class="fas fa-power-off"></i> Logout</a>
        </nav>
    </aside>

    <main class="content">
        <header class="top-nav">
            <div class="welcome">
                <h1 style="font-size:1.8rem; font-weight: 800; background: linear-gradient(to right, var(--text-dark), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Welcome Admin.</h1>
                <p style="color:var(--text-muted); font-size:0.9rem;">Monitor your business analytics and statistics.</p>
            </div>
            <div class="user-profile">
                <i class="fas fa-bell" style="font-size:1.2rem; margin-right:15px; color:var(--text-muted);"></i>
                <a href="profile.php"><img src="https://ui-avatars.com/api/?name=Admin&background=f97316&color=fff" alt="Admin"></a>
            </div>
        </header>

        <!-- Business Analytics -->
        <div class="section-header">
            <h2><i class="fas fa-chart-line"></i> Business Analytics</h2>
            <select style="padding:8px; border:1px solid #ddd; border-radius:8px; font-size:0.8rem;">
                <option>Overall statistics</option>
            </select>
        </div>

        <div class="grid-4">
            <!-- Orders -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon-wrapper" style="background: var(--sky-light); color: var(--primary);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <span class="stat-main-val"><?= $total_orders ?></span>
                        <span class="stat-main-lbl">Total Order</span>
                    </div>
                </div>
                <div class="stat-details">
                    <div class="detail-item"><span><span class="dot" style="background:#f97316;"></span> Pending</span> <span style="font-weight:700;"><?= $pending_orders ?></span></div>
                    <div class="detail-item"><span><span class="dot" style="background:#22c55e;"></span> Confirmed</span> <span style="font-weight:700;"><?= $confirmed_orders ?></span></div>
                    <div class="detail-item"><span><span class="dot" style="background:#3b82f6;"></span> Delivered</span> <span style="font-weight:700;"><?= $delivered_orders ?></span></div>
                    <div class="detail-item"><span><span class="dot" style="background:#ef4444;"></span> Canceled</span> <span style="font-weight:700;"><?= $canceled_orders ?></span></div>
                </div>
            </div>

            <!-- Stores -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon-wrapper" style="background: #f0fdf4; color: #166534;">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <span class="stat-main-val"><?= $total_stores ?></span>
                        <span class="stat-main-lbl">Total Stores</span>
                    </div>
                </div>
                <div style="height:40px; border-radius:8px; background:linear-gradient(90deg, #f0fdf4, #fff); margin-top:15px;"></div>
            </div>

            <!-- Products -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon-wrapper" style="background: #eff6ff; color: #1e40af;">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <span class="stat-main-val"><?= $total_products ?></span>
                        <span class="stat-main-lbl">Total Products</span>
                    </div>
                </div>
                <p style="font-size:0.75rem; color:var(--text-muted); margin-top:20px;">Catalog growth: +12% this month</p>
            </div>

            <!-- Customers -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon-wrapper" style="background: #fdf2f8; color: #9d174d;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <span class="stat-main-val"><?= $total_customers ?></span>
                        <span class="stat-main-lbl">Total Customers</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:5px; margin-top:20px;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#22c55e;"></span>
                    <span style="font-size:0.75rem; color:var(--text-muted);">8 active users right now</span>
                </div>
            </div>
        </div>

        <!-- Admin Wallet -->
        <div class="section-header">
            <h2><i class="fas fa-wallet"></i> Admin Wallet</h2>
        </div>

        <div class="wallet-row">
            <div class="inhouse-card">
                <i class="fas fa-coins"></i>
                <div>
                    <span style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">In-House Earning</span>
                    <div class="inhouse-val">₹<?= number_format($in_house_earning, 2) ?></div>
                </div>
            </div>
            <div class="commission-grid">
                <div class="comm-box active">
                    <h5>Commission Earned</h5>
                    <div class="comm-val">₹<?= number_format($total_commission, 2) ?></div>
                </div>
                <div class="comm-box">
                    <h5>Delivery Charge Earned</h5>
                    <div class="comm-val">₹0.00</div>
                </div>
                <div class="comm-box">
                    <h5>Total Tax Collected</h5>
                    <div class="comm-val">₹<?= number_format($total_tax, 2) ?></div>
                </div>
                <div class="comm-box">
                    <h5>Pending Amount</h5>
                    <div class="comm-val">₹0.00</div>
                </div>
            </div>
        </div>

        <!-- Approval Sections -->
        <div class="approval-row">
            <!-- Brand Approvals -->
            <div class="card">
                <div class="card-header">
                    <h3>Brand Authorization</h3>
                    <a href="#" style="font-size:0.75rem; color:var(--primary-orange); font-weight:700; text-decoration:none;">View All</a>
                </div>
                <table class="table">
                    <thead>
                        <tr><th>Seller</th><th>Brand</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($brand_requests as $br): ?>
                        <tr id="brand-row-<?= $br['id'] ?>">
                            <td><?= $br['seller_name'] ?></td>
                            <td style="font-weight:700;"><?= $br['brand_name'] ?></td>
                            <td><button onclick="handleA('Ba', <?= $br['id'] ?>, 'approved')" class="btn-sm btn-approve">Authorize</button></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($brand_requests)): ?><tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:20px;">No pending brands.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Product Queue -->
            <div class="card">
                <div class="card-header">
                    <h3>Listing Queue</h3>
                    <a href="#" style="font-size:0.75rem; color:var(--primary-orange); font-weight:700; text-decoration:none;">View All</a>
                </div>
                <table class="table">
                    <thead>
                        <tr><th>Product</th><th>Seller</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($product_queue as $pq): ?>
                        <tr id="prod-row-<?= $pq['id'] ?>">
                            <td style="font-weight:600;"><?= htmlspecialchars($pq['name']) ?></td>
                            <td><?= $pq['seller_name'] ?></td>
                            <td><button onclick="handleA('Pr', <?= $pq['id'] ?>, 'approved')" class="btn-sm btn-approve">Publish</button></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($product_queue)): ?><tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:20px;">No pending products.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Disputes -->
        <div class="card" style="margin-bottom:40px;">
            <div class="card-header">
                <h3>Customer Disputes</h3>
                <span class="status-pill pill-pending"><?= count($disputes) ?> New Cases</span>
            </div>
            <table class="table">
                <thead>
                    <tr><th>Customer</th><th>Order #</th><th>Reason</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if(empty($disputes)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:20px;">No open disputes.</td></tr>
                    <?php else: ?>
                        <?php foreach($disputes as $ds): ?>
                        <tr id="dispute-row-<?= $ds['id'] ?>">
                            <td style="font-weight:700;"><?= htmlspecialchars($ds['customer_name']) ?></td>
                            <td>#<?= $ds['order_id'] ?></td>
                            <td><?= htmlspecialchars($ds['reason']) ?></td>
                            <td><button onclick="handleA('Ds', <?= $ds['id'] ?>, 'resolved')" class="btn-sm btn-approve">Resolve</button></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <script>
        function handleA(type, id, action) {
            let api = '';
            let body = {};
            let rowId = '';

            if(type === 'Ba') { api = 'api/approve_brand.php'; body = { request_id: id, action: action }; rowId = 'brand-row-' + id; }
            else if(type === 'Pr') { api = 'api/approve_product.php'; body = { product_id: id, action: action }; rowId = 'prod-row-' + id; }
            else if(type === 'Ds') { api = 'api/resolve_dispute.php'; body = { dispute_id: id, action: action }; rowId = 'dispute-row-' + id; }

            fetch(api, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const row = document.getElementById(rowId);
                    row.style.opacity = '0.3';
                    row.style.pointerEvents = 'none';
                    setTimeout(() => row.remove(), 1000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Request failed. Check console for details.');
            });
        }
    </script>
</body>
</html>
