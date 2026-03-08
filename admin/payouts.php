<?php
// admin/payouts.php - Admin Withdrawal/Payout Management
session_start();
include '../config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch Payout Requests
try {
    $stmt = $pdo->prepare("
        SELECT wt.*, s.business_name, w.balance as current_balance 
        FROM wallet_transactions wt
        JOIN wallets w ON wt.wallet_id = w.id
        JOIN sellers s ON w.seller_id = s.id
        WHERE wt.type = 'payout'
        ORDER BY wt.created_at DESC
    ");
    $stmt->execute();
    $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawals | WALKON Admin</title>
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

        .sidebar { width: 260px; background: var(--sidebar-green); min-height: 100vh; color: #fff; position: fixed; left: 0; top: 0; z-index: 1000; }
        .sidebar-header { padding: 25px; display: flex; align-items: center; gap: 12px; background: rgba(0,0,0,0.1); }
        .sidebar-header img { height: 35px; filter: brightness(0) invert(1); }
        .sidebar-header span { font-size: 1.4rem; font-weight: 800; }
        .nav-label { padding: 15px 25px 5px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.4); font-weight: 800; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: rgba(255,255,255,0.8); text-decoration: none; font-weight: 500; transition: 0.3s; }
        .nav-link i { color: var(--primary-orange); width: 18px; text-align: center; }
        .nav-link:hover, .nav-link.active { background: var(--sidebar-hover); color: #fff; border-left: 4px solid var(--primary-orange); }

        .content { margin-left: 260px; flex: 1; padding: 30px; min-height: 100vh; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title h1 { font-size: 1.8rem; font-weight: 800; }
        
        .card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: var(--card-shadow); }
        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; font-size: 0.75rem; color: var(--text-muted); padding: 15px; background: #f9fafb; text-transform: uppercase; border-bottom: 2px solid #f3f4f6; }
        .table td { padding: 15px; font-size: 0.9rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        
        .btn-status { padding: 6px 15px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; border: none; cursor: pointer; }
        .btn-pending { background: #fff7ed; color: #c2410c; }
        .btn-paid { background: #dcfce7; color: #166534; }
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
            <a href="orders.php" class="nav-link"><i class="fas fa-shopping-basket"></i> Orders</a>
            <a href="refunds.php" class="nav-link"><i class="fas fa-undo-alt"></i> Refund Requests</a>

            <div class="nav-label">PRODUCT MANAGEMENT</div>
            <a href="categories.php" class="nav-link"><i class="fas fa-layer-group"></i> Category Setup</a>
            <a href="brands.php" class="nav-link"><i class="fas fa-tags"></i> Brands</a>
            <a href="listings.php" class="nav-link"><i class="fas fa-box-open"></i> In-House Products</a>

            <div class="nav-label">VENDOR MANAGEMENT</div>
            <a href="sellers.php" class="nav-link"><i class="fas fa-store"></i> Vendor List</a>
            <a href="payouts.php" class="nav-link active"><i class="fas fa-wallet"></i> Withdraws</a>

            <div class="nav-label">Settings</div>
            <a href="../logout.php" class="nav-link"><i class="fas fa-power-off"></i> Logout</a>
        </nav>
    </aside>

    <main class="content">
        <div class="page-header">
            <div style="display: flex; align-items: center;">
                <a href="javascript:history.back()" class="btn-back"><i class="fas fa-arrow-left"></i> Back</a>
                <div class="page-title">
                    <h1>Financial Settlement.</h1>
                    <p style="color:var(--text-muted);">Process and verify vendor withdrawal requests.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Seller / Store</th>
                        <th>Requested Amount</th>
                        <th>Wallet Balance</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($payouts)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">No payout requests found.</td></tr>
                    <?php else: ?>
                        <?php foreach($payouts as $p): ?>
                        <tr>
                            <td>#<?= $p['id'] ?></td>
                            <td><span style="font-weight:700;"><?= htmlspecialchars($p['business_name']) ?></span></td>
                            <td style="font-weight:800; color:var(--primary-orange);">₹<?= number_format($p['amount'], 2) ?></td>
                            <td>₹<?= number_format($p['current_balance'], 2) ?></td>
                            <td style="color:var(--text-muted);"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                            <td><span class="btn-status btn-pending">PENDING</span></td>
                            <td><button class="btn-status" style="background:var(--sidebar-green); color:#fff;">Approve Payout</button></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
