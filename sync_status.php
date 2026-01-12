<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$success_msg = "";

// Simulate "Sync All" Action
if (isset($_POST['sync_all'])) {
    $success_msg = "Sync process started for all active channels. Updates will appear shortly.";
}

// Fetch Products - Get the actual seller_id for this user
try {
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller = $stmt_seller->fetch();
    
    $seller_id = $seller ? $seller['id'] : -1;

    $stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
    $stmt->execute([$seller_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Helper to check channel
function hasChannel($channelStr, $target) {
    $channels = explode(',', $channelStr);
    $channels = array_map('trim', $channels); 
    // Case insensitive check
    return in_array(strtolower($target), array_map('strtolower', $channels));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync Status - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #28a745;
            --text-dark: #333;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --border: #e9ecef;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding: 40px; }

        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 28px; }
        .nav-link { color: var(--text-light); text-decoration: none; margin-right: 20px; transition: 0.3s; }
        .nav-link:hover { color: var(--primary); }

        .card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 30px;
            overflow-x: auto;
        }

        .sync-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        .sync-table th {
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid var(--border);
            color: var(--text-light);
            font-weight: 500;
        }
        .sync-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .sync-table tr:last-child td { border-bottom: none; }
        .sync-table tr:hover { background-color: #f8f9fa; }

        .product-info { display: flex; align-items: center; gap: 15px; }
        .product-img {
            width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #eee;
        }
        .product-name { font-weight: 600; font-size: 15px; display: block; }
        .product-sku { font-size: 12px; color: var(--text-light); }

        .status-icon {
            font-size: 18px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }
        .synced { color: #28a745; background: #d4edda; }
        .not-synced { color: #ccc; background: #f1f1f1; }
        .error { color: #dc3545; background: #f8d7da; }

        .btn {
            background: var(--primary); color: white; border: none; padding: 10px 25px; border-radius: 30px; cursor: pointer; font-weight: 500;
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
        }
        .btn:hover { background: #218838; }
        .btn-outline {
            background: transparent; border: 2px solid var(--primary); color: var(--primary);
        }
        .btn-outline:hover { background: var(--primary); color: white; }

        .alert-success {
            background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>Sync Status</h1>
            <p style="color:var(--text-light)">Real-time synchronization monitoring</p>
        </div>
        <div>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <form method="POST" style="display:inline;">
                <button type="submit" name="sync_all" class="btn"><i class="fas fa-sync"></i> Sync All Now</button>
            </form>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <table class="sync-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Product</th>
                    <th style="text-align:center;">Amazon</th>
                    <th style="text-align:center;">Shopify</th>
                    <th style="text-align:center;">Flipkart</th>
                    <th style="text-align:center;">Instagram</th>
                    <th style="text-align:center;">TikTok</th>
                    <th style="text-align:center;">eBay</th>
                    <th style="text-align:right;">Last Update</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $p): ?>
                        <?php 
                            // Robust image selection
                            $images_raw = $p['images'];
                            $img_url = 'https://via.placeholder.com/50';

                            if (!empty($images_raw)) {
                                $decoded = json_decode($images_raw, true);
                                $candidates = is_array($decoded) ? $decoded : [$images_raw];
                                
                                foreach ($candidates as $url) {
                                    $is_local = (strpos($url, 'uploads/') === 0);
                                    $is_http = (strpos($url, 'http') === 0);
                                    
                                    $path = parse_url($url, PHP_URL_PATH);
                                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                    $is_image_ext = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                                    
                                    if (($is_local && file_exists($url)) || ($is_http && $is_image_ext)) {
                                        $img_url = $url;
                                        break;
                                    }
                                }
                            }
                            $ch = $p['channels'];
                        ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <img src="<?php echo htmlspecialchars($img_url); ?>" class="product-img" alt="Product">
                                    <div>
                                        <span class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></span>
                                        <span class="product-sku"><?php echo htmlspecialchars($p['sku']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <?php if (hasChannel($ch, 'Amazon')): ?>
                                    <span class="status-icon synced"><i class="fas fa-check"></i></span>
                                <?php else: ?>
                                    <span class="status-icon not-synced"><i class="fas fa-minus"></i></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if (hasChannel($ch, 'Shopify')): ?>
                                    <span class="status-icon synced"><i class="fas fa-check"></i></span>
                                <?php else: ?>
                                    <span class="status-icon not-synced"><i class="fas fa-minus"></i></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if (hasChannel($ch, 'Flipkart')): ?>
                                    <span class="status-icon synced"><i class="fas fa-check"></i></span>
                                <?php else: ?>
                                    <span class="status-icon not-synced"><i class="fas fa-minus"></i></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if (hasChannel($ch, 'Instagram')): ?>
                                    <span class="status-icon synced"><i class="fas fa-check"></i></span>
                                <?php else: ?>
                                    <span class="status-icon not-synced"><i class="fas fa-minus"></i></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if (hasChannel($ch, 'TikTok')): ?>
                                    <span class="status-icon synced"><i class="fas fa-check"></i></span>
                                <?php else: ?>
                                    <span class="status-icon not-synced"><i class="fas fa-minus"></i></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if (hasChannel($ch, 'eBay')): ?>
                                    <span class="status-icon synced"><i class="fas fa-check"></i></span>
                                <?php else: ?>
                                    <span class="status-icon not-synced"><i class="fas fa-minus"></i></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right; color:var(--text-light); font-size:14px;">
                                <?php echo date('M d, H:i', strtotime($p['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 40px;">No products found yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
