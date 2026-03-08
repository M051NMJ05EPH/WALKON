<?php
// admin/sellers.php - Admin Vendor Management Suite (Mixed Sky Edition)
session_start();
include '../config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch Sellers
try {
    $stmt = $pdo->prepare("
        SELECT s.*, 
        (SELECT COUNT(*) FROM product_base pb WHERE pb.seller_id = s.id) as total_products,
        (SELECT COUNT(*) FROM orders o WHERE o.seller_id = s.id) as total_orders,
        (SELECT SUM(total_price) FROM orders o WHERE o.seller_id = s.id AND o.status != 'cancelled') as total_revenue
        FROM sellers s
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary Counts
    $total_vendors = count($sellers);
    $total_platform_revenue = 0;
    foreach($sellers as $s) {
        $total_platform_revenue += ($s['total_revenue'] ?? 0);
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vendors | WALKON Admin</title>
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
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --glass: rgba(255, 255, 255, 0.8);
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { 
            background: radial-gradient(circle at 10% 20%, var(--sky-mid) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, var(--sky-light) 0%, transparent 40%),
                        var(--bg);
            display: flex; color: var(--text-dark); overflow-x: hidden; 
            min-height: 100vh;
        }

        /* Sidebar (Matching admin_dashboard.php) */
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
            margin-bottom: 10px;
        }
        .sidebar-header img { height: 35px; }
        .sidebar-header span { font-size: 1.4rem; font-weight: 800; color: var(--primary); }

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
        .content { margin-left: 260px; flex: 1; padding: 40px; min-height: 100vh; }
        
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .page-title h1 { 
            font-size: 2.2rem; 
            font-weight: 800; 
            background: linear-gradient(to right, var(--text-dark), var(--primary)); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }
        .page-title p { color: var(--text-muted); font-size: 1rem; }

        .header-actions { display: flex; gap: 15px; align-items: center; }
        .btn-create {
            background: var(--primary);
            color: #fff;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }
        .btn-create:hover { background: var(--primary-hover); transform: translateY(-2px); }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            padding: 25px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
        }
        .stat-label { font-size: 0.85rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 10px; display: block; }
        .stat-value { font-size: 1.8rem; font-weight: 800; color: var(--text-dark); }
        .stat-trend { font-size: 0.75rem; margin-top: 8px; display: flex; align-items: center; gap: 5px; }
        .trend-up { color: var(--accent); }

        /* Table Card */
        .card { 
            background: var(--glass); 
            backdrop-filter: blur(10px);
            border-radius: 24px; 
            padding: 0; 
            box-shadow: var(--card-shadow); 
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .card-header { padding: 25px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 1.2rem; font-weight: 800; }

        .table-wrapper { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th { 
            text-align: left; 
            font-size: 0.75rem; 
            color: var(--text-muted); 
            padding: 20px 25px; 
            background: rgba(248, 250, 252, 0.5); 
            text-transform: uppercase; 
            letter-spacing: 1px;
            font-weight: 800;
        }
        .table td { padding: 20px 25px; font-size: 0.95rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover { background: rgba(37, 99, 235, 0.02); }

        .store-cell { display: flex; align-items: center; gap: 15px; }
        .store-logo { 
            width: 45px; height: 45px; 
            border-radius: 12px; 
            background: linear-gradient(135deg, var(--sky-mid), var(--sky-light)); 
            display: flex; align-items: center; justify-content: center; 
            font-weight: 800; color: var(--primary);
            font-size: 1.2rem;
            border: 1px solid var(--border);
        }
        .store-name { font-weight: 700; color: var(--text-dark); margin-bottom: 2px; }
        .store-id { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; }

        .badge { padding: 6px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .badge-active { background: #dcfce7; color: #166534; }
        
        .btn-manage {
            padding: 8px 16px;
            border-radius: 8px;
            background: var(--sky-light);
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: 0.3s;
            border: 1px solid var(--border);
        }
        .btn-manage:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none; align-items: center; justify-content: center;
        }
        .modal {
            background: #fff;
            width: 100%;
            max-width: 500px;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            transform: translateY(20px);
            transition: 0.3s;
        }
        .modal.active { transform: translateY(0); }
        .modal-close { position: absolute; top: 20px; right: 20px; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; }
        .modal-header h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: 10px; }
        .modal-header p { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 30px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid var(--border);
            outline: none;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }

        .btn-submit {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: var(--primary);
            color: #fff;
            border: none;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn-submit:hover { background: var(--primary-hover); }

        .btn-back { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; background: #fff; border: 1px solid var(--border); color: var(--text-dark); font-family: 'Outfit', sans-serif; font-size: 0.9rem; font-weight: 600; text-decoration: none; transition: 0.3s; margin-right: 15px; }
        .btn-back:hover { background: var(--sky-light); transform: translateX(-3px); }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
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
            <a href="sellers.php" class="nav-link active"><i class="fas fa-store"></i> Vendor List</a>
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
                    <h1>Vendor Partnerships.</h1>
                    <p>Onboard and manage platform sellers efficiently.</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-create" onclick="openModal()">
                    <i class="fas fa-plus"></i> Add New Vendor
                </button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Vendors</span>
                <div class="stat-value"><?= $total_vendors ?></div>
                <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> 12% growth</div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Active Listings</span>
                <div class="stat-value"><?= array_sum(array_column($sellers, 'total_products')) ?></div>
                <div class="stat-trend"><i class="fas fa-boxes"></i> Across all stores</div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Platform GMV</span>
                <div class="stat-value">₹<?= number_format($total_platform_revenue / 1000, 1) ?>k</div>
                <div class="stat-trend trend-up"><i class="fas fa-chart-line"></i> Total Revenue</div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Pending Payouts</span>
                <div class="stat-value">₹0.00</div>
                <div class="stat-trend"><i class="fas fa-clock"></i> Next cycle</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Active Vendors</h2>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Showing <?= count($sellers) ?> specialized partners</div>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Store Details</th>
                            <th>Contact Info</th>
                            <th>Inventory</th>
                            <th>Performance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($sellers)): ?>
                            <tr><td colspan="6" style="text-align:center; padding:60px; color:var(--text-muted);">No vendors found. Onboard your first partner to get started.</td></tr>
                        <?php else: ?>
                            <?php foreach($sellers as $s): ?>
                            <tr>
                                <td>
                                    <div class="store-cell">
                                        <div class="store-logo"><?= strtoupper(substr($s['business_name'] ?? ($s['name'] ?? 'S'), 0, 1)) ?></div>
                                        <div>
                                            <div class="store-name"><?= htmlspecialchars($s['business_name'] ?? ($s['name'] ?? 'Unnamed Store')) ?></div>
                                            <div class="store-id">ID: #<?= $s['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:600; font-size:0.9rem;"><?= htmlspecialchars($s['email']) ?></div>
                                    <div style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($s['phone'] ?? '+91 0000000000') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight:700; color:var(--text-dark);"><?= $s['total_products'] ?> Products</div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);">SKU Count</div>
                                </td>
                                <td>
                                    <div style="font-weight:800; color:var(--accent);">₹<?= number_format($s['total_revenue'] ?? 0, 2) ?></div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?= $s['total_orders'] ?> Orders processed</div>
                                </td>
                                <td><span class="badge badge-active">Active</span></td>
                                <td><a href="seller_details.php?id=<?= $s['id'] ?>" class="btn-manage">Manage</a></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal for Adding Vendor -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal" id="vendorModal" style="max-width: 650px;">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h2>Onboard New Vendor</h2>
                <p>Register a new partner store with full profile details.</p>
            </div>
            <form id="vendorForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Owner Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                    </div>
                    <div class="form-group">
                        <label>Business / Store Name</label>
                        <input type="text" name="business_name" class="form-control" placeholder="e.g. Sky Shoes Hub" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" name="email" class="form-control" placeholder="vendor@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 9876543210" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Website / Portfolio URL</label>
                    <input type="url" name="website_url" class="form-control" placeholder="https://example.com">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" placeholder="e.g. Mumbai">
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country" class="form-control" placeholder="e.g. India">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: center;">
                    <div class="form-group">
                        <label>Initial Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Set temporary password" required>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 20px;">
                        <input type="checkbox" name="is_verified" style="width: 20px; height: 20px; cursor: pointer;">
                        <label style="margin-bottom: 0;">Mark as Verified Store</label>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Register Vendor Profile</button>
            </form>
        </div>
    </div>

    <script>
        const modalOverlay = document.getElementById('modalOverlay');
        const modal = document.getElementById('vendorModal');

        function openModal() {
            modalOverlay.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function closeModal() {
            modal.classList.remove('active');
            setTimeout(() => modalOverlay.style.display = 'none', 300);
        }

        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeModal();
        });

        document.getElementById('vendorForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Handle checkbox explicitly as it doesn't appear in FormData if unchecked
            data.is_verified = this.querySelector('input[name="is_verified"]').checked;

            fetch('../api/create_seller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Vendor registered successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection failed. Please try again.');
            });
        });
    </script>
</body>
</html>
