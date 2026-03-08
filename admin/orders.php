<?php
// admin/orders.php - Admin Order Management Suite
session_start();
include '../config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Filters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Fetch Orders
try {
    $query = "SELECT o.*, s.business_name FROM orders o LEFT JOIN sellers s ON o.seller_id = s.id WHERE 1=1";
    $params = [];

    if ($status_filter !== 'all') {
        $query .= " AND o.status = ?";
        $params[] = $status_filter;
    }

    if ($search) {
        $query .= " AND (o.id LIKE ? OR o.customer_email LIKE ? OR s.business_name LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $query .= " ORDER BY o.order_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary Counts
    $stats = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
    $total_orders = array_sum($stats);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | WALKON Admin</title>
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
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { background: var(--bg-light); display: flex; color: var(--text-dark); overflow-x: hidden; }

        /* Sidebar Styles (Consistent with admin_dashboard.php) */
        .sidebar { width: 260px; background: var(--sidebar-green); min-height: 100vh; color: #fff; position: fixed; left: 0; top: 0; z-index: 1000; }
        .sidebar-header { padding: 25px; display: flex; align-items: center; gap: 12px; background: rgba(0,0,0,0.1); }
        .sidebar-header img { height: 35px; filter: brightness(0) invert(1); }
        .sidebar-header span { font-size: 1.4rem; font-weight: 800; }
        .nav-label { padding: 15px 25px 5px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.4); font-weight: 800; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: rgba(255,255,255,0.8); text-decoration: none; font-weight: 500; transition: 0.3s; }
        .nav-link i { color: var(--primary-orange); width: 18px; text-align: center; }
        .nav-link:hover, .nav-link.active { background: var(--sidebar-hover); color: #fff; border-left: 4px solid var(--primary-orange); }

        .content { margin-left: 260px; flex: 1; padding: 30px; min-height: 100vh; }
        
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .page-title h1 { font-size: 1.8rem; font-weight: 800; }
        
        .filter-stripe { background: #fff; padding: 15px 25px; border-radius: 12px; display: flex; gap: 20px; align-items: center; margin-bottom: 30px; box-shadow: var(--card-shadow); }
        .search-box { flex: 1; position: relative; }
        .search-box input { width: 100%; padding: 10px 15px 10px 40px; border-radius: 8px; border: 1px solid #ddd; outline: none; }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

        .status-tab { display: flex; gap: 10px; margin-bottom: 25px; }
        .tab-btn { padding: 8px 20px; border-radius: 50px; text-decoration: none; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); background: #eee; transition: 0.3s; }
        .tab-btn.active { background: var(--primary-orange); color: #fff; }

        .card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: var(--card-shadow); overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table th { text-align: left; font-size: 0.75rem; color: var(--text-muted); padding: 15px; background: #f9fafb; text-transform: uppercase; border-bottom: 2px solid #f3f4f6; }
        .table td { padding: 15px; font-size: 0.9rem; border-bottom: 1px solid #f3f4f6; }
        .table th:last-child, .table td:last-child { text-align: center; width: 120px; }
        
        .status-pill { padding: 4px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; }
        .status-pending { background: #fff7ed; color: #c2410c; }
        .status-processing { background: #eff6ff; color: #1d4ed8; }
        .status-delivered { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .btn-view:hover { background: #fff7ed; text-decoration: underline; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; background: #fff; border: 1px solid #e5e7eb; color: var(--text-dark); font-family: 'Outfit', sans-serif; font-size: 0.9rem; font-weight: 600; text-decoration: none; transition: 0.3s; margin-right: 15px; }
        .btn-back:hover { background: var(--bg-light); transform: translateX(-3px); }
    </style>
</head>
<body>

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
        <div class="page-header">
            <div style="display: flex; align-items: center;">
                <a href="javascript:history.back()" class="btn-back"><i class="fas fa-arrow-left"></i> Back</a>
                <div class="page-title">
                    <h1>Order Lifecycle Management.</h1>
                    <p style="color:var(--text-muted);">Monitor and process platform-wide transactions.</p>
                </div>
            </div>
        </div>

        <div class="status-tab">
            <a href="?status=all" class="tab-btn <?= $status_filter === 'all' ? 'active' : '' ?>">All Orders (<?= $total_orders ?>)</a>
            <a href="?status=pending" class="tab-btn <?= $status_filter === 'pending' ? 'active' : '' ?>">Pending (<?= $stats['pending'] ?? 0 ?>)</a>
            <a href="?status=delivered" class="tab-btn <?= $status_filter === 'delivered' ? 'active' : '' ?>">Delivered (<?= $stats['delivered'] ?? 0 ?>)</a>
            <a href="?status=cancelled" class="tab-btn <?= $status_filter === 'cancelled' ? 'active' : '' ?>">Cancelled (<?= $stats['cancelled'] ?? 0 ?>)</a>
        </div>

        <div class="filter-stripe">
            <form method="GET" class="search-box">
                <i class="fas fa-search"></i>
                <input type="hidden" name="status" value="<?= $status_filter ?>">
                <input type="text" name="search" placeholder="Search by Order ID, Customer, or Store..." value="<?= htmlspecialchars($search) ?>">
            </form>
            <button class="tab-btn active" style="background:var(--sidebar-green);">Export CSV</button>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Store</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">No orders found matching your criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td style="font-weight:700;">#<?= $order['id'] ?></td>
                            <td><span style="font-weight:600;"><?= htmlspecialchars($order['business_name'] ?? 'Direct') ?></span></td>
                            <td><?= htmlspecialchars($order['customer_email']) ?></td>
                            <td style="font-weight:800;">₹<?= number_format($order['total_price'], 2) ?></td>
                            <td><span class="status-pill status-<?= strtolower($order['status']) ?>"><?= $order['status'] ?></span></td>
                            <td style="color:var(--text-muted);"><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                            <td><a href="order_details.php?id=<?= $order['id'] ?>" class="btn-view">Detail View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
