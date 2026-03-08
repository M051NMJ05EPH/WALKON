<?php
// admin/order_details.php - Admin Order Detail View
session_start();
include '../config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$order_id = intval($_GET['id'] ?? 0);
if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// Handle Status Update
$update_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    $new_status = $_POST['new_status'];
    if (in_array($new_status, $allowed_statuses)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$new_status, $order_id]);
        $update_msg = 'success';
    }
}

// Fetch Order Details
try {
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            s.business_name, s.email AS seller_email,
            pb.name AS product_name,
            pp.price AS product_price,
            pm.url AS product_image,
            b.name AS brand_name,
            c.name AS category_name
        FROM orders o
        LEFT JOIN sellers s ON o.seller_id = s.id
        LEFT JOIN product_base pb ON o.product_id = pb.id
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_media pm ON pb.id = pm.product_id AND pm.is_primary = 1
        LEFT JOIN product_specs ps ON pb.id = ps.product_id
        LEFT JOIN brands b ON ps.brand_id = b.id
        LEFT JOIN categories c ON pb.category_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header("Location: orders.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Status timeline config
$statuses = ['pending', 'processing', 'shipped', 'delivered'];
$status_icons = [
    'pending'    => 'fa-clock',
    'processing' => 'fa-cogs',
    'shipped'    => 'fa-truck',
    'delivered'  => 'fa-check-circle',
    'cancelled'  => 'fa-times-circle',
];
$current_status = strtolower($order['status']);
$is_cancelled = ($current_status === 'cancelled');
$current_step = array_search($current_status, $statuses);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $order_id ?> Details | WALKON Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --sidebar-green: #064e3b;
            --sidebar-hover: #065f46;
            --primary-orange: #f97316;
            --bg-light: #f3f4f6;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --white: #ffffff;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { background: var(--bg-light); display: flex; color: var(--text-dark); overflow-x: hidden; }

        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar-green); min-height: 100vh; color: #fff; position: fixed; left: 0; top: 0; z-index: 1000; }
        .sidebar-header { padding: 25px; display: flex; align-items: center; gap: 12px; background: rgba(0,0,0,0.1); }
        .sidebar-header img { height: 35px; filter: brightness(0) invert(1); }
        .sidebar-header span { font-size: 1.4rem; font-weight: 800; }
        .nav-label { padding: 15px 25px 5px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.4); font-weight: 800; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: rgba(255,255,255,0.8); text-decoration: none; font-weight: 500; transition: 0.3s; }
        .nav-link i { color: var(--primary-orange); width: 18px; text-align: center; }
        .nav-link:hover, .nav-link.active { background: var(--sidebar-hover); color: #fff; border-left: 4px solid var(--primary-orange); }

        /* Main Content */
        .content { margin-left: 260px; flex: 1; padding: 30px; min-height: 100vh; }

        /* Breadcrumb */
        .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; }
        .breadcrumb a { color: var(--primary-orange); text-decoration: none; font-weight: 600; }
        .breadcrumb i { font-size: 0.7rem; }

        /* Page Header */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 15px; }
        .page-header h1 { font-size: 1.8rem; font-weight: 800; }
        .page-header h1 span { color: var(--primary-orange); }
        .date-badge { font-size: 0.85rem; color: var(--text-muted); background: #fff; padding: 8px 16px; border-radius: 50px; box-shadow: var(--card-shadow); }

        /* Toast */
        .toast { position: fixed; top: 20px; right: 20px; background: #064e3b; color: #fff; padding: 14px 22px; border-radius: 12px; font-weight: 600; z-index: 9999; display: flex; align-items: center; gap: 10px; animation: slideIn 0.4s ease; box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
        @keyframes slideIn { from { opacity:0; transform: translateY(-20px); } to { opacity:1; transform: translateY(0); } }

        /* Grid Layout */
        .grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .grid-full { grid-column: 1 / -1; }

        /* Card */
        .card { background: var(--white); border-radius: 18px; padding: 28px; box-shadow: var(--card-shadow); }
        .card-title { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-muted); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .card-title i { color: var(--primary-orange); }

        /* Status Timeline */
        .timeline { display: flex; align-items: center; justify-content: space-between; position: relative; margin: 10px 0 28px; }
        .timeline::before { content: ''; position: absolute; top: 20px; left: 0; right: 0; height: 3px; background: #e5e7eb; z-index: 0; }
        .timeline-progress { position: absolute; top: 20px; left: 0; height: 3px; background: linear-gradient(90deg, var(--sidebar-green), var(--primary-orange)); z-index: 1; transition: width 0.6s ease; }
        .step { display: flex; flex-direction: column; align-items: center; gap: 8px; z-index: 2; flex: 1; }
        .step-icon { width: 42px; height: 42px; border-radius: 50%; border: 3px solid #e5e7eb; background: var(--white); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; color: #d1d5db; transition: 0.4s; }
        .step-icon.done { background: var(--sidebar-green); border-color: var(--sidebar-green); color: #fff; }
        .step-icon.active { background: var(--primary-orange); border-color: var(--primary-orange); color: #fff; box-shadow: 0 0 0 5px rgba(249, 115, 22, 0.2); }
        .step-icon.cancelled-icon { background: #ef4444; border-color: #ef4444; color: #fff; }
        .step-label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: capitalize; }
        .step-label.active-label { color: var(--primary-orange); }
        .step-label.done-label { color: var(--sidebar-green); }

        /* Info Grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .info-item label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); display: block; margin-bottom: 4px; }
        .info-item p { font-size: 0.92rem; font-weight: 600; color: var(--text-dark); }
        .info-item p.big { font-size: 1.3rem; font-weight: 800; color: var(--text-dark); }

        /* Product Card */
        .product-row { display: flex; align-items: center; gap: 18px; padding: 18px; background: #f9fafb; border-radius: 12px; border: 1px solid #f3f4f6; }
        .product-img { width: 75px; height: 75px; border-radius: 10px; object-fit: cover; border: 1px solid #e5e7eb; background: #f3f4f6; }
        .product-img-placeholder { width: 75px; height: 75px; border-radius: 10px; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #9ca3af; flex-shrink: 0; }
        .product-info { flex: 1; }
        .product-info .pname { font-weight: 800; font-size: 1rem; margin-bottom: 3px; }
        .product-info .pmeta { font-size: 0.8rem; color: var(--text-muted); }
        .product-price-col { text-align: right; }
        .product-price-col .amount { font-size: 1.2rem; font-weight: 800; color: var(--primary-orange); }
        .product-price-col .qty { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; }

        /* Status Badge */
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; }
        .status-pending    { background: #fff7ed; color: #c2410c; }
        .status-processing { background: #eff6ff; color: #1d4ed8; }
        .status-shipped    { background: #f5f3ff; color: #6d28d9; }
        .status-delivered  { background: #dcfce7; color: #166534; }
        .status-cancelled  { background: #fee2e2; color: #991b1b; }

        /* Status Update Form */
        .status-form { display: flex; flex-direction: column; gap: 12px; }
        .status-form select { width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #e5e7eb; font-family: 'Outfit', sans-serif; font-size: 0.9rem; font-weight: 600; background: #f9fafb; outline: none; color: var(--text-dark); }
        .btn-update { width: 100%; padding: 13px; border-radius: 10px; background: linear-gradient(135deg, var(--sidebar-green), #0d9488); color: #fff; font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; border: none; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-update:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(6,78,59,0.3); }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; background: #fff; border: 1px solid #e5e7eb; color: var(--text-dark); font-family: 'Outfit', sans-serif; font-size: 0.9rem; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-back:hover { background: var(--bg-light); }

        /* Seller card */
        .seller-info { display: flex; flex-direction: column; gap: 12px; }
        .seller-row { display: flex; align-items: center; gap: 10px; font-size: 0.88rem; font-weight: 500; }
        .seller-row i { color: var(--primary-orange); width: 16px; flex-shrink: 0; }

        /* Amount summary */
        .amount-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 0.9rem; }
        .amount-row:not(:last-child) { border-bottom: 1px dashed #f3f4f6; }
        .amount-row.total { font-weight: 800; font-size: 1.1rem; padding-top: 14px; }
        .amount-row.total span:last-child { color: var(--primary-orange); }

        /* Divider */
        .divider { border: none; border-top: 1px solid #f3f4f6; margin: 18px 0; }

        @media (max-width: 960px) {
            .grid { grid-template-columns: 1fr; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/shoe_logo_green.png" alt="W">
            <span>WALKON</span>
        </div>
        <nav>
            <a href="../admin_dashboard.php" class="nav-link"><i class="fas fa-grip-horizontal"></i> Dashboard</a>
            <a href="../pos.php" class="nav-link"><i class="fas fa-cash-register"></i> POS</a>

            <div class="nav-label">ORDER MANAGEMENT</div>
            <a href="orders.php" class="nav-link active"><i class="fas fa-shopping-basket"></i> Orders</a>
            <a href="refunds.php" class="nav-link"><i class="fas fa-undo-alt"></i> Refund Requests</a>

            <div class="nav-label">PRODUCT MANAGEMENT</div>
            <a href="categories.php" class="nav-link"><i class="fas fa-layer-group"></i> Category Setup</a>
            <a href="brands.php" class="nav-link"><i class="fas fa-tags"></i> Brands</a>
            <a href="listings.php" class="nav-link"><i class="fas fa-box-open"></i> In-House Products</a>

            <div class="nav-label">VENDOR MANAGEMENT</div>
            <a href="sellers.php" class="nav-link"><i class="fas fa-store"></i> Vendor List</a>
            <a href="payouts.php" class="nav-link"><i class="fas fa-wallet"></i> Withdraws</a>

            <div class="nav-label">Settings</div>
            <a href="../logout.php" class="nav-link"><i class="fas fa-power-off"></i> Logout</a>
        </nav>
    </aside>

    <main class="content">

        <?php if ($update_msg === 'success'): ?>
        <div class="toast" id="toast"><i class="fas fa-check-circle"></i> Order status updated successfully!</div>
        <script>setTimeout(() => document.getElementById('toast').remove(), 3500);</script>
        <?php endif; ?>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="orders.php">Orders</a>
            <i class="fas fa-chevron-right"></i>
            <span>Order #<?= $order_id ?></span>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Order <span>#<?= $order_id ?></span></h1>
                <p style="color:var(--text-muted); margin-top:4px; font-size:0.9rem;">View and manage the full order lifecycle.</p>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <span class="date-badge"><i class="fas fa-calendar-alt" style="margin-right:6px;color:var(--primary-orange);"></i><?= date('M d, Y · h:i A', strtotime($order['order_date'])) ?></span>
                <a href="orders.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>

        <div class="grid">

            <!-- LEFT COLUMN -->
            <div style="display:flex; flex-direction:column; gap:24px;">

                <!-- Order Timeline -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-route"></i> Order Status Timeline</div>
                    <?php if ($is_cancelled): ?>
                        <div style="display:flex; align-items:center; gap:12px; padding:16px; background:#fee2e2; border-radius:12px;">
                            <div class="step-icon cancelled-icon" style="width:42px;height:42px;border-radius:50%;flex-shrink:0;"><i class="fas fa-times-circle"></i></div>
                            <div>
                                <p style="font-weight:800; color:#991b1b;">Order Cancelled</p>
                                <p style="font-size:0.8rem; color:#b91c1c;">This order has been cancelled and is no longer active.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php
                            $progress = 0;
                            if ($current_step !== false && $current_step > 0) {
                                $progress = ($current_step / (count($statuses) - 1)) * 100;
                            }
                            ?>
                            <div class="timeline-progress" style="width:<?= $progress ?>%;"></div>
                            <?php foreach ($statuses as $i => $s): ?>
                            <?php
                                $cls = '';
                                $label_cls = '';
                                if ($current_step !== false) {
                                    if ($i < $current_step) { $cls = 'done'; $label_cls = 'done-label'; }
                                    elseif ($i === $current_step) { $cls = 'active'; $label_cls = 'active-label'; }
                                }
                            ?>
                            <div class="step">
                                <div class="step-icon <?= $cls ?>">
                                    <i class="fas <?= $status_icons[$s] ?>"></i>
                                </div>
                                <span class="step-label <?= $label_cls ?>"><?= ucfirst($s) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Current Status Badge -->
                    <div style="text-align:center; margin-top:6px;">
                        <span class="status-badge status-<?= $current_status ?>">
                            <i class="fas <?= $status_icons[$current_status] ?? 'fa-info-circle' ?>"></i>
                            <?= ucfirst($current_status) ?>
                        </span>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-shoe-prints"></i> Product Ordered</div>
                    <div class="product-row">
                        <?php if (!empty($order['product_image'])): ?>
                            <img src="../<?= htmlspecialchars($order['product_image']) ?>" alt="Product" class="product-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="product-img-placeholder" style="display:none;"><i class="fas fa-shoe-prints"></i></div>
                        <?php else: ?>
                            <div class="product-img-placeholder"><i class="fas fa-shoe-prints"></i></div>
                        <?php endif; ?>
                        <div class="product-info">
                            <div class="pname"><?= htmlspecialchars($order['product_name'] ?? 'Product #' . $order['product_id']) ?></div>
                            <div class="pmeta">
                                <?php if ($order['brand_name']): ?>
                                    <i class="fas fa-tag" style="margin-right:4px;"></i><?= htmlspecialchars($order['brand_name']) ?> &nbsp;·&nbsp;
                                <?php endif; ?>
                                <?php if ($order['category_name']): ?>
                                    <i class="fas fa-folder" style="margin-right:4px;"></i><?= htmlspecialchars($order['category_name']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="pmeta" style="margin-top:4px;">
                                Qty: <strong><?= $order['quantity'] ?></strong> &nbsp;·&nbsp; 
                                Unit Price: <strong>₹<?= number_format($order['unit_price'], 2) ?></strong>
                            </div>
                        </div>
                        <div class="product-price-col">
                            <div class="amount">₹<?= number_format($order['total_price'], 2) ?></div>
                            <div class="qty"><?= $order['quantity'] ?> × ₹<?= number_format($order['unit_price'], 2) ?></div>
                        </div>
                    </div>

                    <hr class="divider">
                    <!-- Amount Summary -->
                    <div class="amount-row">
                        <span style="color:var(--text-muted);">Subtotal</span>
                        <span>₹<?= number_format($order['unit_price'] * $order['quantity'], 2) ?></span>
                    </div>
                    <div class="amount-row">
                        <span style="color:var(--text-muted);">Shipping</span>
                        <span style="color:#16a34a; font-weight:700;">FREE</span>
                    </div>
                    <?php if (!empty($order['payment_status'])): ?>
                    <div class="amount-row">
                        <span style="color:var(--text-muted);">Payment Status</span>
                        <span style="font-weight:700; text-transform:uppercase;"><?= htmlspecialchars($order['payment_status']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="amount-row total">
                        <span>Grand Total</span>
                        <span>₹<?= number_format($order['total_price'], 2) ?></span>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-user-circle"></i> Customer Information</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Full Name</label>
                            <p><?= htmlspecialchars($order['customer_name'] ?: '—') ?></p>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <p><?= htmlspecialchars($order['customer_email'] ?: '—') ?></p>
                        </div>
                        <div class="info-item">
                            <label>Phone</label>
                            <p><?= htmlspecialchars($order['customer_phone'] ?: '—') ?></p>
                        </div>
                        <div class="info-item">
                            <label>Channel</label>
                            <p><?= htmlspecialchars($order['channel'] ?? 'Direct') ?></p>
                        </div>
                        <div class="info-item" style="grid-column: 1/-1;">
                            <label>Shipping Address</label>
                            <p><?= nl2br(htmlspecialchars($order['shipping_address'] ?: '—')) ?></p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div style="display:flex; flex-direction:column; gap:24px;">

                <!-- Update Status -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-edit"></i> Update Status</div>
                    <form method="POST" class="status-form">
                        <select name="new_status" id="new_status">
                            <option value="pending"    <?= $current_status === 'pending'    ? 'selected' : '' ?>>⏳ Pending</option>
                            <option value="processing" <?= $current_status === 'processing' ? 'selected' : '' ?>>⚙️ Processing</option>
                            <option value="shipped"    <?= $current_status === 'shipped'    ? 'selected' : '' ?>>🚚 Shipped</option>
                            <option value="delivered"  <?= $current_status === 'delivered'  ? 'selected' : '' ?>>✅ Delivered</option>
                            <option value="cancelled"  <?= $current_status === 'cancelled'  ? 'selected' : '' ?>>❌ Cancelled</option>
                        </select>
                        <button type="submit" class="btn-update">
                            <i class="fas fa-save"></i> Save Status
                        </button>
                    </form>
                </div>

                <!-- Order Meta -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-receipt"></i> Order Summary</div>
                    <div style="display:flex; flex-direction:column; gap:14px;">
                        <div class="info-item">
                            <label>Order ID</label>
                            <p class="big">#<?= $order_id ?></p>
                        </div>
                        <div class="info-item">
                            <label>Order Date</label>
                            <p><?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></p>
                        </div>
                        <div class="info-item">
                            <label>Total Amount</label>
                            <p style="font-size:1.4rem; font-weight:800; color:var(--primary-orange);">₹<?= number_format($order['total_price'], 2) ?></p>
                        </div>
                        <?php if (!empty($order['updated_at'])): ?>
                        <div class="info-item">
                            <label>Last Updated</label>
                            <p><?= date('d M Y, h:i A', strtotime($order['updated_at'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Seller / Store Info -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-store"></i> Store / Seller</div>
                    <div class="seller-info">
                        <?php if ($order['business_name']): ?>
                        <div class="seller-row">
                            <i class="fas fa-building"></i>
                            <span style="font-weight:700;"><?= htmlspecialchars($order['business_name']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($order['seller_email']): ?>
                        <div class="seller-row">
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($order['seller_email']) ?></span>
                        </div>
                        <?php endif; ?>


                        <?php if (!$order['business_name'] && !$order['seller_email']): ?>
                        <p style="color:var(--text-muted); font-size:0.85rem;">No seller information available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-bolt"></i> Quick Actions</div>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <a href="orders.php" class="btn-back" style="justify-content:center;">
                            <i class="fas fa-list"></i> All Orders
                        </a>
                        <a href="orders.php?status=<?= $current_status ?>" class="btn-back" style="justify-content:center;">
                            <i class="fas fa-filter"></i> Filter by Status
                        </a>
                        <a href="refunds.php" class="btn-back" style="justify-content:center;">
                            <i class="fas fa-undo-alt"></i> Refund Requests
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>
</html>
