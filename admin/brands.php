<?php
// admin/brands.php - Admin Brand Management
session_start();
include '../config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch Brands
try {
    $brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Management | WALKON Admin</title>
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
        
        .brand-icon { width: 35px; height: 35px; border-radius: 50%; background: #eee; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.7rem; }
        .btn-add { background: var(--sidebar-green); color: #fff; padding: 10px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 8px; font-size: 0.9rem; }
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
            <a href="brands.php" class="nav-link active"><i class="fas fa-tags"></i> Brands</a>
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
                    <h1>Brand Authorization.</h1>
                    <p style="color:var(--text-muted);">Managing official footprints and partner brands.</p>
                </div>
            </div>
            <a href="#" class="btn-add"><i class="fas fa-plus"></i> Add New Brand</a>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand Logo</th>
                        <th>Brand Name</th>
                        <th>Verified Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($brands as $brand): ?>
                    <tr>
                        <td style="color:var(--text-muted);">#<?= $brand['id'] ?></td>
                        <td><div class="brand-icon"><?= strtoupper(substr($brand['name'], 0, 2)) ?></div></td>
                        <td style="font-weight:700;"><?= htmlspecialchars($brand['name'] ?? 'Generic') ?></td>
                        <td><span style="color:var(--primary-orange); font-weight:800; font-size:0.75rem;"><i class="fas fa-shield-alt"></i> VERIFIED</span></td>
                        <td><a href="#" style="color:var(--text-muted); text-decoration:none;"><i class="fas fa-cog"></i> Settings</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
