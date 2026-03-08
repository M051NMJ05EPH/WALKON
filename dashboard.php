<?php
session_start();
include 'config.php';

// Auth Check - Redirect guests to the public shop instead of forcing login
if (!isset($_SESSION['user_id'])) {
    header("Location: shop.php");
    exit();
}

$email = $_SESSION['email'];
$first_name = $_SESSION['first_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? '';
$display_name = trim($first_name . ' ' . $last_name) ?: $email;
$role = $_SESSION['role'] ?? 'customer';

// Strategic Role-Based Redirection
switch ($role) {
    case 'admin':
        header("Location: admin_dashboard.php");
        exit();
    case 'store':
    case 'store_owner':
        header("Location: store_dashboard.php");
        exit();
    case 'entrepreneur':
        header("Location: entrepreneur_dashboard.php");
        exit();
    case 'customer':
        header("Location: customer_dashboard.php");
        exit();
    default:
        // Log the unknown role to help debugging
        error_log("Unknown role encountered in dashboard.php: " . $role);
        header("Location: login.php");
        exit();
}

// Initializations
$total_inventory = $live_listings = $total_orders = 0;

// Statistical Fetching
try {
    if ($role === 'admin') {
        $total_inventory = $pdo->query("SELECT COUNT(*) FROM product_base")->fetchColumn() ?: 0;
        $live_listings = $pdo->query("SELECT COUNT(*) FROM product_base WHERE status = 'published'")->fetchColumn() ?: 0;
        $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
    } elseif (in_array($role, ['entrepreneur', 'store'])) {
        $stmt_inv = $pdo->prepare("SELECT COUNT(*) FROM product_base WHERE seller_id = ?");
        $stmt_inv->execute([$seller_id]);
        $total_inventory = $stmt_inv->fetchColumn() ?: 0;

        $stmt_live = $pdo->prepare("SELECT COUNT(*) FROM product_base WHERE seller_id = ? AND status = 'published'");
        $stmt_live->execute([$seller_id]);
        $live_listings = $stmt_live->fetchColumn() ?: 0;

        $stmt_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE seller_id = ?");
        $stmt_orders->execute([$seller_id]);
        $total_orders = $stmt_orders->fetchColumn() ?: 0;
    } else {
        // Customer
        $stmt_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt_orders->execute([$_SESSION['user_id']]);
        $total_orders = $stmt_orders->fetchColumn() ?: 0;
    }
} catch (PDOException $e) {
    $total_inventory = $live_listings = $total_orders = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | WALKON 6Valley</title>
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

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--sidebar-green);
            min-height: 100vh;
            color: #fff;
            position: fixed;
            left: 0; top: 0;
            transition: 0.3s;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 25px;
            display: flex;
            align-items: center; gap: 12px;
            background: rgba(0,0,0,0.1);
            margin-bottom: 10px;
        }
        .sidebar-header img { height: 35px; filter: brightness(0) invert(1); }
        .sidebar-header span { font-size: 1.4rem; font-weight: 800; }

        .sidebar-search { padding: 10px 20px; margin-bottom: 20px; }
        .sidebar-search input {
            width: 100%;
            padding: 10px 15px;
            background: rgba(255,255,255,0.1);
            border: none; border-radius: 8px;
            color: #fff; outline: none; font-size: 0.9rem;
        }

        .nav-label { padding: 15px 25px 5px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.4); font-weight: 800; }
        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 25px; color: rgba(255,255,255,0.8);
            text-decoration: none; font-weight: 500; transition: 0.3s;
        }
        .nav-link i { color: var(--primary-orange); width: 18px; text-align: center; }
        .nav-link:hover, .nav-link.active { background: var(--sidebar-hover); color: #fff; border-left: 4px solid var(--primary-orange); }

        /* Content Area */
        .content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
            min-height: 100vh;
        }

        .top-nav {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;
        }
        .welcome h1 { font-size: 2rem; font-weight: 800; color: var(--text-dark); }
        .welcome p { color: var(--text-muted); font-size: 1rem; }

        /* Analytics Grid */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 50px;
        }
        .stat-card {
            background: var(--white);
            border-radius: 20px;
            padding: 35px;
            box-shadow: var(--card-shadow);
            text-align: center;
            border-bottom: 4px solid #eee;
            transition: 0.3s;
        }
        .stat-card:hover { border-color: var(--primary-orange); transform: translateY(-5px); }
        .stat-label { font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 10px; }
        .stat-value { font-size: 3.5rem; font-weight: 800; color: var(--text-dark); }

        /* Module Grid */
        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        .module-card {
            background: var(--white);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            box-shadow: var(--card-shadow);
            border: 1px solid #f3f4f6;
            transition: 0.3s;
        }
        .module-card:hover { border-color: var(--primary-orange); }
        .module-icon {
            width: 70px; height: 70px;
            background: rgba(249, 115, 22, 0.1);
            color: var(--primary-orange);
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; margin: 0 auto 25px; font-size: 1.8rem;
        }
        .module-card h3 { font-size: 1.4rem; font-weight: 800; margin-bottom: 15px; }
        .module-card p { color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px; }
        
        .btn-action {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 12px;
            background: var(--sidebar-green);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-action:hover { background: var(--primary-orange); box-shadow: 0 10px 20px rgba(249, 115, 22, 0.2); }

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
            <a href="dashboard.php" class="nav-link active"><i class="fas fa-home"></i> Home</a>
            
            <?php if ($role === 'admin'): ?>
                <div class="nav-label">Global Admin</div>
                <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-user-shield"></i> Admin Panel</a>
                <a href="manage_users.php" class="nav-link"><i class="fas fa-users-cog"></i> User Management</a>
            <?php endif; ?>

            <div class="nav-label">Main Services</div>
            <?php if ($role === 'customer'): ?>
                <a href="shop.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Start Shopping</a>
                <a href="my_orders.php" class="nav-link"><i class="fas fa-box"></i> My Orders</a>
            <?php else: ?>
                <a href="my_listings.php" class="nav-link"><i class="fas fa-layer-group"></i> My Listings</a>
                <a href="add_listing.php" class="nav-link"><i class="fas fa-plus-circle"></i> Add Product</a>
            <?php endif; ?>

            <div class="nav-label">Accounts</div>
            <a href="profile.php" class="nav-link"><i class="fas fa-user-circle"></i> Profile Settings</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-power-off"></i> Logout</a>
        </nav>
    </aside>

    <main class="content">
        <header class="top-nav">
            <div class="welcome">
                <h1>Welcome back, <?= htmlspecialchars($first_name ?: 'User') ?>.</h1>
                <p><?= $role === 'admin' ? 'Global platform overview and administrative controls.' : 'Your personal professional commerce workspace.' ?></p>
            </div>
            <div style="display:flex; gap:15px; align-items:center;">
                <i class="fas fa-bell" style="font-size:1.2rem; color:var(--text-muted); cursor:pointer;"></i>
                <a href="profile.php"><img src="https://ui-avatars.com/api/?name=<?= $display_name ?>&background=f97316&color=fff" style="width:45px; height:45px; border-radius:50%;" alt="User"></a>
            </div>
        </header>

        <!-- Stats Section -->
        <div class="analytics-grid">
            <div class="stat-card">
                <div class="stat-label">Total platform Inventory</div>
                <div class="stat-value"><?= $total_inventory ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Platform Live Listings</div>
                <div class="stat-value" style="color:var(--primary-orange);"><?= $live_listings ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total System Orders</div>
                <div class="stat-value"><?= $total_orders ?></div>
            </div>
        </div>

        <!-- Key Modules -->
        <div class="module-grid">
            <div class="module-card">
                <div class="module-icon"><i class="fas fa-users"></i></div>
                <h3>User Management</h3>
                <p>Create and manage staff accounts, roles, and administrative permissions.</p>
                <a href="manage_users.php" class="btn-action">Manage Users</a>
            </div>

            <div class="module-card">
                <div class="module-icon"><i class="fas fa-sliders-h"></i></div>
                <h3>Store Settings</h3>
                <p>Configure business information, platform preferences, and global settings.</p>
                <a href="store_settings.php" class="btn-action">Configure</a>
            </div>

            <div class="module-card">
                <div class="module-icon"><i class="fas fa-headset"></i></div>
                <h3>Help Center</h3>
                <p>Need assistance with regular operations? Contact our concierge support team.</p>
                <a href="support.php" class="btn-action">Get Help</a>
            </div>
        </div>
    </main>

</body>
</html>
