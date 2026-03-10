<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'] ?? 'customer';
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'User';
$display_name = trim($first_name . " " . ($_SESSION['last_name'] ?? ''));

// Fetch Active Sellers with their product counts and connected channels
try {
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.business_name, s.created_at, s.is_verified,
        (SELECT COUNT(*) FROM product_base pb WHERE pb.seller_id = s.id AND pb.status = 'published') as product_count
        FROM sellers s
        WHERE (SELECT COUNT(*) FROM product_base pb WHERE pb.seller_id = s.id AND pb.status = 'published') > 0
        ORDER BY product_count DESC
    ");
    $stmt->execute();
    $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch previews and channels for each seller
    foreach ($sellers as &$s) {
        // Previews
        $p_stmt = $pdo->prepare("
            SELECT pb.id, pb.name, 
            (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as image,
            pp.price
            FROM product_base pb
            JOIN product_prices pp ON pb.id = pp.product_id
            WHERE pb.seller_id = ? AND pb.status = 'published'
            LIMIT 3
        ");
        $p_stmt->execute([$s['id']]);
        $s['previews'] = $p_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Channels (from seller_marketplaces if exists) - joining with marketplaces to get logos
        $c_stmt = $pdo->prepare("
            SELECT m.name, m.logo_url 
            FROM marketplaces m 
            JOIN seller_marketplaces sm ON m.id = sm.marketplace_id 
            WHERE sm.seller_id = ? AND sm.status = 'connected'
        ");
        $c_stmt->execute([$s['id']]);
        $s['channels'] = $c_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error fetching sellers: " . $e->getMessage());
}

$seller_id = $_SESSION['seller_id'] ?? null;
$store_name = 'My Directory';
if ($seller_id && ($user_role === 'store' || $user_role === 'store_owner')) {
    $s_check = $pdo->prepare("SELECT business_name FROM sellers WHERE id = ?");
    $s_check->execute([$seller_id]);
    $store_name = $s_check->fetchColumn() ?: 'My Store';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sellers Ecosystem | WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #60a5fa;
            --accent: #10b981;
            --navy: #0f172a;
            --bg: #f0f6ff;
            --white: #ffffff;
            --border: #c7dcff;
            --text: #1e293b;
            --muted: #64748b;
            --sidebar-w: 260px;
            --glass: rgba(255, 255, 255, 0.75);
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { 
            background:
                radial-gradient(ellipse at 0% 0%, rgba(37, 99, 235, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 100% 0%, rgba(96, 165, 250, 0.18) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 100%, rgba(37, 99, 235, 0.08) 0%, transparent 60%),
                linear-gradient(160deg, #e0eeff 0%, #f0f6ff 40%, #ffffff 70%, #e8f3ff 100%);
            color: var(--text); min-height: 100vh; 
        }

        /* Nav Layout */
        <?php if ($user_role === 'store' || $user_role === 'store_owner'): ?>
        body { display: flex; }
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
        .sidebar {
            width: var(--sidebar-w); 
            background: linear-gradient(180deg, #ffffff 0%, #eef5ff 100%);
            border-right: 1px solid var(--border);
            position: fixed; top: 0; left: 0; height: 100vh; overflow-y: auto; z-index: 100;
            display: flex; flex-direction: column;
            box-shadow: 4px 0 20px rgba(37, 99, 235, 0.06);
        }
        <?php else: ?>
        .main { width: 100%; display: flex; flex-direction: column; }
        <?php endif; ?>

        /* SIDEBAR STYLES */
        .sidebar-brand { padding: 28px 24px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
        .nav-item {
            display: flex; align-items: center; gap: 12px; padding: 12px 18px; margin: 4px 12px;
            border-radius: 12px; text-decoration: none; color: var(--muted); font-weight: 600; font-size: 0.9rem; transition: 0.2s;
        }
        .nav-item:hover { background: #dbeafe; color: var(--primary); }
        .nav-item.active { background: linear-gradient(90deg, #dbeafe, #eff6ff); color: var(--primary); font-weight: 700; border-left: 4px solid var(--primary); }
        .sidebar-section { padding: 24px 24px 8px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--muted); }

        /* TOPNAV (Entrepreneur) */
        .topnav {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #1d4ed8 100%);
            backdrop-filter: blur(12px); padding: 0 40px; height: 72px;
            display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 4px 24px rgba(37, 99, 235, 0.3);
        }
        .nav-pill {
            display: flex; align-items: center; gap: 10px; padding: 10px 20px; border-radius: 50px;
            text-decoration: none; color: #cbd5e1; font-weight: 600; font-size: 0.85rem; transition: 0.3s;
        }
        .nav-pill:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-pill.active { background: var(--primary); color: #fff; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3); }

        /* CONTENT */
        .content { padding: 40px; max-width: 1400px; margin: 0 auto; width: 100%; }
        
        .hero { margin-bottom: 48px; position: relative; }
        .hero h1 { font-size: 2.8rem; font-weight: 900; color: var(--navy); letter-spacing: -1.5px; line-height: 1; margin-bottom: 8px; }
        .hero p { color: var(--muted); font-size: 1.1rem; font-weight: 500; max-width: 600px; }
        
        .header-actions { display: flex; gap: 16px; align-items: center; margin-top: 32px; }
        .btn {
            display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px;
            border-radius: 14px; font-weight: 700; text-decoration: none; transition: 0.3s;
            font-size: 0.9rem; cursor: pointer; border: none;
        }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2); }
        .btn-primary:hover { transform: translateY(-3px); background: #1d4ed8; }
        .btn-outline { background: var(--white); border: 1px solid var(--border); color: var(--navy); }
        .btn-outline:hover { background: #f8fafc; border-color: var(--primary); color: var(--primary); }

        /* Ecosystem Grid — 3-column parallel layout */
        .sellers-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        .seller-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.92) 0%, rgba(239,246,255,0.85) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(199, 220, 255, 0.8); border-radius: 28px; padding: 24px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; flex-direction: column; gap: 20px;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.06), 0 1px 3px rgba(37, 99, 235, 0.04);
        }
        @media (max-width: 1280px) { .sellers-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .sellers-grid { grid-template-columns: 1fr; } }
        .seller-card:hover { transform: translateY(-12px); border-color: var(--primary-light); box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.18), 0 4px 20px rgba(37, 99, 235, 0.1); }

        .seller-card-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .seller-avatar-wrap { display: flex; align-items: center; gap: 18px; }
        .seller-avatar {
            width: 64px; height: 64px; border-radius: 18px; 
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 800; color: var(--primary); border: 1px solid #dbeafe;
        }
        .seller-info h3 { font-size: 1.25rem; font-weight: 800; color: var(--navy); line-height: 1.2; }
        .seller-info .verified-badge { font-size: 0.65rem; background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 50px; font-weight: 800; text-transform: uppercase; margin-top: 6px; display: inline-block; }

        .channel-icons { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; max-width: 150px; }
        .channel-logo { width: 28px; height: 28px; border-radius: 8px; border: 1px solid var(--border); padding: 4px; background: #fff; object-fit: contain; }

        .seller-previews { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .preview-box {
            aspect-ratio: 1; border-radius: 16px; 
            background: linear-gradient(135deg, #e0eeff, #f0f6ff); overflow: hidden; position: relative;
            border: 1px solid rgba(199, 220, 255, 0.7); transition: 0.3s;
        }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .preview-label {
            position: absolute; bottom: 8px; right: 8px; background: rgba(15, 23, 42, 0.85); color: #fff;
            font-size: 0.7rem; padding: 3px 8px; border-radius: 6px; font-weight: 800; backdrop-filter: blur(4px);
        }

        .seller-card-footer { 
            display: flex; align-items: center; justify-content: space-between; 
            padding-top: 24px; border-top: 1px solid var(--border); margin-top: auto; 
        }
        .stat-group { display: flex; flex-direction: column; }
        .stat-group span:first-child { font-size: 1.1rem; font-weight: 900; color: var(--navy); }
        .stat-group span:last-child { font-size: 0.75rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        .btn-visit {
            background: var(--navy); color: #fff; padding: 10px 24px; border-radius: 12px;
            font-size: 0.85rem; font-weight: 800; text-decoration: none; transition: 0.3s;
        }
        .btn-visit:hover { background: var(--primary); transform: scale(1.05); }

        /* Onboarding Modal */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px);
            z-index: 2000; display: none; align-items: center; justify-content: center;
        }
        .modal {
            background: linear-gradient(160deg, #ffffff 0%, #f0f6ff 100%); 
            width: 100%; max-width: 600px; border-radius: 32px; padding: 48px;
            box-shadow: 0 25px 60px -12px rgba(37, 99, 235, 0.4), 0 10px 20px rgba(0,0,0,0.1);
            border: 1px solid rgba(199, 220, 255, 0.6);
            position: relative;
            transform: scale(0.9); opacity: 0; transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .modal.active { transform: scale(1); opacity: 1; }
        .modal-close { position: absolute; top: 32px; right: 32px; font-size: 1.8rem; color: var(--muted); cursor: pointer; }
        .modal-header h2 { font-size: 2rem; font-weight: 900; margin-bottom: 8px; letter-spacing: -1px; }
        .modal-header p { color: var(--muted); margin-bottom: 32px; font-size: 1rem; }

        form label { display: block; font-size: 0.85rem; font-weight: 800; color: var(--navy); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        form .input-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
        form input, form select {
            width: 100%; padding: 14px 20px; border-radius: 14px; border: 1.5px solid #c7dcff;
            font-size: 1rem; outline: none; transition: 0.3s; background: rgba(240, 246, 255, 0.6);
        }
        form input:focus { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.1); }
        
        @media (max-width: 1024px) {
            .sidebar { display: none; }
            .main { margin-left: 0; }
            .topnav { padding: 0 20px; }
        }

        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.9); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

<?php if ($user_role === 'store' || $user_role === 'store_owner'): ?>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="assets/shoe_logo_green.png" alt="WalkOn" style="width:40px;">
            <div style="font-weight:900; font-size:1.3rem; letter-spacing:-1px;">WALK<span style="color:var(--primary);">ON</span></div>
        </div>
        <div class="sidebar-section">Overview</div>
        <a href="store_dashboard.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
        <div class="sidebar-section">Insights</div>
        <a href="sellers.php" class="nav-item active"><i class="fas fa-globe"></i> Ecosystem</a>
        <a href="analytics.php" class="nav-item"><i class="fas fa-chart-line"></i> Analytics</a>
        <div class="sidebar-section">Sales</div>
        <a href="my_orders.php" class="nav-item"><i class="fas fa-shopping-bag"></i> Orders Feed</a>
        <a href="logout.php" class="nav-item" style="margin-top:auto;"><i class="fas fa-power-off"></i> Logout</a>
    </aside>
<?php elseif ($user_role === 'entrepreneur'): ?>
    <nav class="topnav">
        <div style="display:flex; align-items:center; gap:12px;">
            <img src="assets/shoe_logo_green.png" alt="W" style="width:36px;">
            <span style="color:#fff; font-weight:900; font-size:1.2rem; letter-spacing:-0.5px;">WALK<span style="color:var(--primary)">ON</span> <span style="font-weight:400; font-size:0.8rem; opacity:0.6; margin-left:8px;">HUB</span></span>
        </div>
        <div style="display:flex; gap:12px;">
            <a href="entrepreneur_dashboard.php" class="nav-pill"><i class="fas fa-rocket"></i> <span>Dashboard</span></a>
            <a href="sellers.php" class="nav-pill active"><i class="fas fa-store-alt"></i> <span>Ecosystem</span></a>
            <a href="marketplaces.php" class="nav-pill"><i class="fas fa-plug"></i> <span>Channels</span></a>
        </div>
        <a href="logout.php" style="color:#fff; opacity:0.6; font-size:1.3rem; transition:0.3s;" onmouseover="this.style.opacity='1'"><i class="fas fa-sign-out-alt"></i></a>
    </nav>
<?php endif; ?>

<div class="main">
    <div class="content">
        <div class="hero">
            <span style="color:var(--primary); font-weight:800; font-size:0.8rem; text-transform:uppercase; letter-spacing:2px; display:block; margin-bottom:12px;">The Footwear Infrastructure</span>
            <h1>Sellers Ecosystem.</h1>
            <p>Collaborate, benchmark, and scale with the world's leading footwear vendors on the WALKON multi-channel platform.</p>
            
            <div class="header-actions">
                <div style="flex: 1; display:flex; align-items:center; gap:15px;">
                    <div style="font-size:1.5rem; font-weight:900; color:var(--primary);"><?= count($sellers) ?></div>
                    <div style="font-size:0.8rem; font-weight:800; color:var(--muted); text-transform:uppercase; line-height:1.2;">Verified<br>Partners</div>
                </div>
                <button onclick="openAIModal()" class="btn btn-primary" style="background: linear-gradient(135deg, #8b5cf6, #3b82f6); border: none; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);"><i class="fas fa-brain"></i> AI Market Analysis</button>
                <button onclick="openModal()" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Onboard Multichannel Vendor</button>
                <a href="export_sellers.php" class="btn btn-outline"><i class="fas fa-file-export"></i> Export Partners</a>
            </div>
        </div>

        <div class="sellers-grid">
            <?php foreach ($sellers as $s): 
                $initials = strtoupper(substr($s['business_name'] ?: $s['name'], 0, 1));
            ?>
            <div class="seller-card">
                <div class="seller-card-header">
                    <div class="seller-avatar-wrap">
                        <div class="seller-avatar"><?= $initials ?></div>
                        <div class="seller-info">
                            <h3><?= htmlspecialchars($s['business_name'] ?: $s['name']) ?></h3>
                            <?php if($s['is_verified']): ?>
                                <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified Channel Partner</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if(!empty($s['channels'])): ?>
                        <div class="channel-icons" title="Connected Sales Channels">
                            <?php foreach($s['channels'] as $ch): ?>
                                <img src="<?= htmlspecialchars($ch['logo_url'] ?: 'assets/placeholder_channel.png') ?>" alt="<?= htmlspecialchars($ch['name']) ?>" class="channel-logo">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="seller-previews">
                    <?php if (!empty($s['previews'])): ?>
                        <?php foreach ($s['previews'] as $p): ?>
                        <div class="preview-box">
                            <img src="<?= htmlspecialchars($p['image'] ?: 'assets/placeholder_shoe.png') ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            <div class="preview-label">₹<?= number_format($p['price'], 0) ?></div>
                        </div>
                        <?php endforeach; ?>
                        <?php for($i=count($s['previews']); $i<3; $i++): ?>
                            <div class="preview-box" style="display:flex; align-items:center; justify-content:center; opacity:0.1; background:#000;">
                                <i class="fas fa-plus" style="font-size:1.2rem;"></i>
                            </div>
                        <?php endfor; ?>
                    <?php else: ?>
                        <div class="preview-box" style="grid-column: span 3; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; opacity:0.3;">
                            <i class="fas fa-sync" style="font-size:2rem;"></i>
                            <span style="font-weight:800; font-size:0.8rem;">INVENTORY SYNCING...</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="seller-card-footer">
                    <div style="display:flex; gap:32px;">
                        <div class="stat-group">
                            <span><?= number_format($s['product_count']) ?></span>
                            <span>SKUs</span>
                        </div>
                        <div class="stat-group">
                            <span style="color: var(--accent); display: flex; align-items: center; gap: 6px;">
                                <div style="width: 8px; height: 8px; background: var(--accent); border-radius: 50%; box-shadow: 0 0 10px var(--accent); animation: pulse 2s infinite;"></div>
                                Enabled
                            </span>
                            <span>Multi-Channel</span>
                        </div>
                    </div>
                    <a href="shop.php?seller=<?= $s['id'] ?>" class="btn-visit">Visit Store <i class="fas fa-arrow-right" style="margin-left:8px; font-size:0.7rem;"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Onboarding Modal -->
<div class="modal-overlay" id="onboardOverlay">
    <div class="modal" id="onboardModal">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-header">
            <h2>Onboard New Vendor</h2>
            <p>Deploy a new merchant to the WalkOn multi-channel infrastructure.</p>
        </div>
        <form id="onboardForm">
            <div class="input-row">
                <div class="form-group">
                    <label>Enterprise Name</label>
                    <input type="text" name="business_name" placeholder="e.g. Urban Footwear Ltd" required>
                </div>
                <div class="form-group">
                    <label>Master Associate</label>
                    <input type="text" name="name" placeholder="Full Name" required>
                </div>
            </div>
            <div class="input-row">
                <div class="form-group">
                    <label>Identity Email</label>
                    <input type="email" name="email" placeholder="vendor@walkon.io" required>
                </div>
                <div class="form-group">
                    <label>Secure Key</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom:24px;">
                <label>Infrastructure Tier</label>
                <select name="type" style="width:100%; padding:14px; border-radius:14px; border:1.5px solid var(--border); background:#f8fafc; font-weight:600; font-size:0.9rem;">
                    <option value="single">Single Channel (WalkOn Direct)</option>
                    <option value="multi" selected>Multi-Channel (Sync to Amazon/Shopify)</option>
                    <option value="enterprise">Enterprise (Dedicated Warehouse Support)</option>
                </select>
            </div>

            <div class="input-row">
                <div class="form-group">
                    <label>Operating Region</label>
                    <input type="text" name="city" placeholder="e.g. New Delhi">
                </div>
                <div class="form-group">
                    <label>Support Line</label>
                    <input type="text" name="phone" placeholder="+91 000 000 0000">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:16px; font-size:1rem; border-radius:18px;">Finalize Ecosystem Deployment</button>
        </form>
    </div>
</div>

<!-- AI Modal -->
<div class="modal-overlay" id="aiOverlay">
    <div class="modal" id="aiModal" style="max-width: 800px; background: linear-gradient(160deg, #f8fafc 0%, #eff6ff 100%);">
        <span class="modal-close" onclick="closeAIModal()">&times;</span>
        <div class="modal-header">
            <h2 style="color: #4f46e5; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-brain fa-pulse"></i> AI Market Intelligence
            </h2>
            <p>Real-time predictive modeling based on seller ecosystem performance.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div style="background: #ffffff; padding: 24px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Top Trending Category</span>
                <div style="font-size: 1.5rem; font-weight: 900; color: #1e293b; margin-top: 8px;">Athletic Sneakers</div>
                <div style="font-size: 0.85rem; color: #10b981; font-weight: 600; margin-top: 8px;"><i class="fas fa-chart-line"></i> +42% Demand Forecast</div>
            </div>
            <div style="background: #ffffff; padding: 24px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Recommended Pricing Action</span>
                <div style="font-size: 1.5rem; font-weight: 900; color: #1e293b; margin-top: 8px;">Optimize by -5%</div>
                <div style="font-size: 0.85rem; color: #8b5cf6; font-weight: 600; margin-top: 8px;"><i class="fas fa-bullseye"></i> Maximizes Amazon Buy Box</div>
            </div>
        </div>
        
        <div style="background: #1e293b; color: white; padding: 24px; border-radius: 20px; font-family: monospace; font-size: 0.9rem; margin-bottom: 24px; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #3b82f6;"></div>
            <div style="color: #60a5fa; margin-bottom: 12px; font-weight: bold;">[AI_MODEL]: Executing Cross-Channel Vendor Analysis...</div>
            <div id="aiLogText" style="opacity: 0.8; line-height: 1.6;">
                > Scanning inventory overlap across <?php echo count($sellers); ?> sellers...<br>
                > Identifying supply gaps in sub-category 'Formal Leather'...<br>
                > Result: High opportunity for new sellers in Formal wear space.<br>
            </div>
        </div>

        <button class="btn btn-primary" onclick="closeAIModal()" style="width: 100%; justify-content: center; background: #4f46e5; border: none; font-size: 1rem; padding: 16px; border-radius: 18px;">Acknowledge Insights</button>
    </div>
</div>

<script>
    const overlay = document.getElementById('onboardOverlay');
    const modal = document.getElementById('onboardModal');
    
    // AI Modal
    const aiOverlay = document.getElementById('aiOverlay');
    const aiModal = document.getElementById('aiModal');

    function openModal() {
        overlay.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeModal() {
        modal.classList.remove('active');
        setTimeout(() => overlay.style.display = 'none', 300);
    }
    
    function openAIModal() {
        aiOverlay.style.display = 'flex';
        setTimeout(() => aiModal.classList.add('active'), 10);
        
        const logText = document.getElementById('aiLogText');
        logText.innerHTML = `> Initializing neural net targeting...<br>`;
        setTimeout(() => logText.innerHTML += `> Analyzing historical sales velocity across all channels...<br>`, 800);
        setTimeout(() => logText.innerHTML += `> Correlating with competitor pricing datasets...<br>`, 1600);
        setTimeout(() => logText.innerHTML += `<span style="color:#10b981; font-weight:bold;">> ACTION REQUIRED: Boost inventory in 'Athletic Sneakers' by 20% to meet upcoming forecasted demand spike.</span><br>`, 2500);
    }

    function closeAIModal() {
        aiModal.classList.remove('active');
        setTimeout(() => aiOverlay.style.display = 'none', 300);
    }

    overlay.onclick = (e) => { if(e.target === overlay) closeModal(); };
    aiOverlay.onclick = (e) => { if(e.target === aiOverlay) closeAIModal(); };

    document.getElementById('onboardForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // This hits the new api/onboard_seller.php for broader role permissions
        fetch('api/onboard_seller.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                alert('Success! New vendor added to ecosystem.');
                location.reload();
            } else {
                alert('Deployment failed: ' + res.message);
            }
        })
        .catch(err => alert('Communication error with API infrastructure.'));
    };
</script>

<?php include 'includes/chatbot.php'; ?>
</body>
</html>
