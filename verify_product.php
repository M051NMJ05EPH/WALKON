<?php
include 'config.php';

$serial_number = $_GET['sn'] ?? '';
$product = null;
$authenticity = null;

if ($serial_number) {
    try {
        $stmt = $pdo->prepare("
            SELECT pa.*, pb.name as product_name, pb.id as pid,
                   s.name as store_name, s.business_name,
                   b.name as brand_name, b.logo_url as brand_logo,
                   c.name as category_name,
                   (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as product_image
            FROM product_authenticity pa
            JOIN product_base pb ON pa.product_id = pb.id
            JOIN sellers s ON pb.seller_id = s.id
            LEFT JOIN product_specs spec ON pb.id = spec.product_id
            LEFT JOIN brands b ON spec.brand_id = b.id
            LEFT JOIN categories c ON pb.category_id = c.id
            WHERE pa.serial_number = ?
        ");
        $stmt->execute([$serial_number]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $product = $data;
            $authenticity = $data;
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Authenticity | WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --secondary: #a855f7;
            --bg: #030712;
            --card-bg: rgba(17, 24, 39, 0.7);
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { 
            background: var(--bg); 
            color: var(--text-main); 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center;
            padding: 20px;
            background-image: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 50%),
                              radial-gradient(circle at 0% 0%, rgba(168, 85, 247, 0.05) 0%, transparent 30%);
        }

        .verify-card {
            max-width: 500px;
            width: 100%;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 40px;
            padding: 40px;
            border: 1px solid var(--border);
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo { font-size: 2rem; font-weight: 900; margin-bottom: 30px; letter-spacing: -1px; }
        .logo span { color: var(--primary); }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
        }

        .status-verified { background: rgba(16, 185, 129, 0.1); color: var(--primary); border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
        .status-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .status-not-found { background: rgba(255, 255, 255, 0.05); color: var(--text-dim); border: 1px solid var(--border); }

        .product-visual {
            position: relative;
            margin-bottom: 30px;
        }
        .product-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            border: 1px solid var(--border);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            text-align: left;
            margin-bottom: 30px;
        }
        .info-item h4 { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .info-item p { font-size: 1rem; font-weight: 600; color: #fff; }

        .brand-sec {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: var(--glass);
            border-radius: 20px;
            border: 1px solid var(--border);
            margin-bottom: 30px;
            text-align: left;
        }
        .brand-logo { width: 50px; height: 50px; border-radius: 12px; object-fit: contain; background: #fff; padding: 5px; }
        .brand-info h5 { font-size: 0.95rem; margin-bottom: 2px; }
        .brand-info p { font-size: 0.75rem; color: var(--text-dim); }

        .search-box {
            position: relative;
            width: 100%;
            margin-top: 20px;
        }
        .search-input {
            width: 100%;
            padding: 18px 25px;
            border-radius: 20px;
            background: var(--glass);
            border: 1px solid var(--border);
            color: #fff;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }
        .search-input:focus { border-color: var(--primary); box-shadow: 0 0 25px rgba(16, 185, 129, 0.1); }
        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .back-home { margin-top: 30px; color: var(--text-dim); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
        .back-home:hover { color: var(--primary); }
    </style>
</head>
<body>

    <div class="verify-card">
        <div class="logo">WALK<span>ON</span></div>

        <?php if ($product): ?>
            <!-- Authenticity Status -->
            <?php if ($authenticity['status'] === 'verified'): ?>
                <div class="status-badge status-verified">
                    <i class="fas fa-check-circle"></i> Authenticity Verified
                </div>
            <?php elseif ($authenticity['status'] === 'rejected'): ?>
                <div class="status-badge status-rejected">
                    <i class="fas fa-times-circle"></i> Verification Failed
                </div>
            <?php else: ?>
                <div class="status-badge status-pending">
                    <i class="fas fa-clock"></i> Verification Pending
                </div>
            <?php endif; ?>

            <!-- Product Info -->
            <div class="product-visual">
                <img src="<?= $product['product_image'] ?: 'https://via.placeholder.com/500x300?text=No+Product+Image' ?>" class="product-img">
            </div>

            <h2 style="font-size: 1.5rem; margin-bottom: 25px; line-height: 1.2;"><?= htmlspecialchars($product['product_name']) ?></h2>

            <div class="brand-sec">
                <img src="<?= $product['brand_logo'] ?: 'https://via.placeholder.com/100?text=' . urlencode($product['brand_name']) ?>" class="brand-logo">
                <div class="brand-info">
                    <h5><?= htmlspecialchars($product['brand_name'] ?: 'Official Brand') ?></h5>
                    <p>Verified Retail Partner: <?= htmlspecialchars($product['business_name'] ?: $product['store_name']) ?></p>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <h4>Serial Number</h4>
                    <p style="font-family: monospace; letter-spacing: 0.5px;"><?= $product['serial_number'] ?></p>
                </div>
                <div class="info-item">
                    <h4>Batch Number</h4>
                    <p><?= $product['batch_number'] ?: 'N/A' ?></p>
                </div>
                <div class="info-item">
                    <h4>Category</h4>
                    <p><?= htmlspecialchars($product['category_name']) ?></p>
                </div>
                <div class="info-item">
                    <h4>Reg. Date</h4>
                    <p><?= date('M d, Y', strtotime($product['created_at'])) ?></p>
                </div>
            </div>

            <p style="color: var(--text-dim); font-size: 0.85rem; padding: 0 20px;">
                This product has been registered by an authorized retailer on the WALKON blockchain network to ensure its authenticity.
            </p>

        <?php elseif ($serial_number): ?>
            <div class="status-badge status-not-found">
                <i class="fas fa-exclamation-triangle"></i> Invalid Serial Number
            </div>
            <h3 style="margin-bottom: 20px;">Verification Failed</h3>
            <p style="color: var(--text-dim); margin-bottom: 30px;">The serial number you provided was not found in our authenticity database. Please check the code and try again.</p>
            
            <form action="verify_product.php" method="GET" class="search-box">
                <input type="text" name="sn" class="search-input" placeholder="Enter Serial Number..." value="<?= htmlspecialchars($serial_number) ?>">
                <button type="submit" class="search-btn">Verify</button>
            </form>

        <?php else: ?>
            <h2 style="margin-bottom: 15px;">Product Verification</h2>
            <p style="color: var(--text-dim); margin-bottom: 30px;">Scan the QR code on your product or enter the official serial number below to verify its authenticity.</p>
            
            <form action="verify_product.php" method="GET" class="search-box">
                <input type="text" name="sn" class="search-input" placeholder="Example: WALKON-XXXXXXXX-0000">
                <button type="submit" class="search-btn">Verify</button>
            </form>
        <?php endif; ?>

        <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Back to Marketplace</a>
    </div>

</body>
</html>
