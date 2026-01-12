<?php
session_start();

// If not logged in → Send back to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$first_name = $_SESSION['first_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? '';
$display_name = trim($first_name . ' ' . $last_name) ?: $email;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WALKON Shoes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background:#f0f2f5; }
        .header {
            background:#28a745;
            color:white;
            padding:20px 40px;
            text-align:center;
            box-shadow:0 4px 20px rgba(0,0,0,0.1);
        }
        .header h1 { font-size:32px; margin-bottom:8px; }
        .header p { opacity:0.9; }
        .container {
            max-width:1200px;
            margin:40px auto;
            padding:20px;
        }
        .welcome-card {
            background:white;
            padding:40px;
            border-radius:20px;
            box-shadow:0 15px 40px rgba(0,0,0,0.1);
            text-align:center;
            margin-bottom:40px;
        }
        .welcome-card h2 {
            font-size:28px;
            color:#28a745;
            margin-bottom:10px;
        }
        .grid {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(300px,1fr));
            gap:30px;
        }
        .card {
            background:white;
            padding:30px;
            border-radius:16px;
            text-align:center;
            box-shadow:0 10px 30px rgba(0,0,0,0.08);
            transition:0.3s;
        }
        .card:hover { transform:translateY(-10px); }
        .card i { font-size:50px; color:#28a745; margin-bottom:20px; }
        .card h3 { margin-bottom:15px; color:#333; }
        .btn {
            display:inline-block;
            background:#28a745;
            color:white;
            padding:14px 30px;
            border-radius:50px;
            text-decoration:none;
            font-weight:600;
            margin-top:20px;
            transition:0.3s;
        }
        .btn:hover { background:#218838; }
        .logout {
            position:fixed;
            top:20px;
            right:40px;
            background:rgba(255,255,255,0.2);
            color:white;
            padding:10px 20px;
            border-radius:30px;
            text-decoration:none;
            font-weight:600;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="header">
        <h1>WALKON Shoes</h1>
        <p>Multi-Channel E-Commerce Platform</p>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>Welcome back, <?php echo htmlspecialchars($display_name); ?>!</h2>
            <p>Manage your shoe listings across Amazon, Flipkart, Shopify, Instagram, TikTok Shop, eBay, and more – all in one place.</p>
        </div>

        <div class="grid">
            <div class="card">
                <i class="fas fa-plus-circle"></i>
                <h3>Add New Listing</h3>
                <p>Upload photos, set prices, and sync to all channels instantly.</p>
                <a href="add_listing.php" class="btn">Add Shoe</a>
            </div>

            <div class="card">
                <i class="fas fa-list-alt"></i>
                <h3>My Listings</h3>
                <p>View, edit, or remove your current products.</p>
                <a href="my_listings.php" class="btn">View All</a>
            </div>

            <div class="card">
                <i class="fas fa-sync-alt"></i>
                <h3>Sync Status</h3>
                <p>Check real-time sync across all platforms.</p>
                <a href="sync_status.php" class="btn">Check Sync</a>
            </div>

            <div class="card">
                <i class="fas fa-chart-bar"></i>
                <h3>Sales Analytics</h3>
                <p>Track performance and earnings.</p>
                <a href="analytics.php" class="btn">View Report</a>
            </div>

            <!-- My Orders (New) -->
            <div class="card">
                <i class="fas fa-shopping-bag"></i>
                <h3>My Orders</h3>
                <p>Track customer orders and payment status.</p>
                <a href="my_orders.php" class="btn">View Orders</a>
            </div>

            <!-- Smart Pricing (New) -->
            <div class="card">
                <i class="fas fa-tags"></i>
                <h3>Smart Pricing</h3>
                <p>Automatically adjust prices based on competition.</p>
                <a href="smart_pricing.php" class="btn">Manage Pricing</a>
            </div>

            <!-- Bulk Operations (New) -->
            <div class="card">
                <i class="fas fa-layer-group"></i>
                <h3>Bulk Operations</h3>
                <p>Edit thousands of SKUs and descriptions in seconds.</p>
                <a href="bulk_operations.php" class="btn">Bulk Edit</a>
            </div>


        </div>
    </div>

</body>
</html>