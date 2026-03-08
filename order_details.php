<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'customer';

try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               pb.name as product_name, pb.id as product_id,
               pp.price as unit_price,
               s.business_name, s.name as seller_person_name,
               (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
               (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image
        FROM orders o
        JOIN product_base pb ON o.product_id = pb.id
        JOIN product_prices pp ON pb.id = pp.product_id
        JOIN sellers s ON o.seller_id = s.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order not found.");
    }

    if ($role === 'customer' && $order['user_id'] != $user_id) {
        die("Access denied.");
    }
    if (($role === 'store' || $role === 'entrepreneur' || $role === 'store_owner') && $order['seller_id'] != ($_SESSION['seller_id'] ?? 0) && $role !== 'admin') {
         die("Access denied.");
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$status_class = strtolower($order['status'] ?? 'pending');
$is_cancelled = ($status_class === 'cancelled');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $order_id ?> Details | WALKON</title>
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
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { 
            background:
                radial-gradient(ellipse at 0% 0%, rgba(37, 99, 235, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 100% 0%, rgba(96, 165, 250, 0.18) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 100%, rgba(37, 99, 235, 0.08) 0%, transparent 60%),
                linear-gradient(160deg, #e0eeff 0%, #f0f6ff 40%, #ffffff 70%, #e8f3ff 100%);
            color: var(--text-dark); min-height: 100vh; padding-bottom: 80px;
        }

        .container { max-width: 1100px; margin: 0 auto; padding: 40px 20px; }
        
        .back-nav { display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; font-weight: 700; font-size: 0.9rem; margin-bottom: 30px; transition: 0.2s; }
        .back-nav:hover { color: var(--primary); transform: translateX(-5px); }

        .detail-hero { margin-bottom: 40px; }
        .order-id-badge { display: inline-block; padding: 6px 14px; background: rgba(37, 99, 235, 0.1); color: var(--primary); border-radius: 50px; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
        .detail-hero h1 { font-size: 2.5rem; font-weight: 900; color: var(--navy); letter-spacing: -1.5px; line-height: 1; margin-bottom: 8px; }
        .detail-hero p { color: var(--text-muted); font-size: 1.1rem; font-weight: 500; }

        .grid-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }

        .glass-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.06);
            margin-bottom: 30px;
        }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .card-header h3 { font-size: 1.1rem; font-weight: 800; color: var(--navy); text-transform: uppercase; letter-spacing: 1px; }

        /* Timeline Styles */
        .timeline { margin-top: 10px; display: flex; flex-direction: column; gap: 0; position: relative; padding-left: 20px; }
        .timeline::before { content: ''; position: absolute; left: 34px; top: 10px; bottom: 10px; width: 4px; background: #dbeafe; border-radius: 4px; z-index: 1; }
        .timeline-step { display: flex; gap: 24px; padding-bottom: 30px; position: relative; z-index: 2; }
        .timeline-step:last-child { padding-bottom: 0; }
        .step-icon { width: 32px; height: 32px; border-radius: 50%; background: #fff; border: 4px solid #dbeafe; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: var(--text-muted); transition: 0.3s; }
        .timeline-step.completed .step-icon { background: var(--accent); border-color: #d1fae5; color: #fff; }
        .timeline-step.active .step-icon { background: var(--primary); border-color: #dbeafe; color: #fff; transform: scale(1.1); box-shadow: 0 0 20px rgba(37, 99, 235, 0.3); }
        .step-content h4 { font-size: 1rem; font-weight: 800; color: var(--navy); margin-bottom: 2px; }
        .step-content p { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }
        .step-time { font-size: 0.75rem; color: var(--primary); font-weight: 700; margin-top: 4px; }

        /* Product Breakdown */
        .product-strip { display: flex; gap: 24px; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 24px; margin-bottom: 24px; }
        .product-strip:last-child { border-bottom: none; padding-bottom: 0; margin-bottom: 0; }
        .prod-img { width: 120px; height: 120px; border-radius: 20px; border: 1px solid var(--border); object-fit: cover; background: #fff; }
        .prod-meta h4 { font-size: 1.25rem; font-weight: 900; color: var(--navy); line-height: 1.2; margin-bottom: 6px; }
        .prod-meta p { font-size: 0.9rem; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; }
        .price-badge { display: inline-block; padding: 4px 10px; background: var(--navy); color: #fff; border-radius: 8px; font-weight: 800; font-size: 0.85rem; }

        /* Summary Info */
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed var(--border); }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-size: 0.9rem; color: var(--text-muted); font-weight: 600; }
        .info-value { font-size: 0.95rem; color: var(--navy); font-weight: 800; text-align: right; }
        .total-row { background: var(--primary); color: #fff; border-radius: 16px; padding: 18px 24px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2); }
        .total-row span { font-weight: 800; font-size: 1.25rem; }

        /* Action Panel */
        .action-panel { display: flex; flex-direction: column; gap: 12px; margin-top: 20px; }
        .btn { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 16px; border-radius: 16px; font-weight: 800; font-size: 0.9rem; transition: 0.3s; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
        .btn-primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2); }
        .btn-outline { background: #fff; border-color: var(--border); color: var(--navy); }
        .btn-outline:hover { background: #f8fafc; border-color: var(--primary); color: var(--primary); }
        .btn:hover { transform: translateY(-3px); }

        .support-card { background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: #fff; border-radius: 28px; padding: 32px; position: relative; overflow: hidden; }
        .support-card h4 { font-size: 1.25rem; font-weight: 900; margin-bottom: 10px; position: relative; z-index: 2; }
        .support-card p { font-size: 0.9rem; opacity: 0.9; margin-bottom: 20px; position: relative; z-index: 2; line-height: 1.6; }
        .support-card i.bg-icon { position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.1; z-index: 1; transform: rotate(-15deg); }

        @media (max-width: 992px) {
            .grid-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="my_orders.php" class="back-nav"><i class="fas fa-arrow-left"></i> Back to My Orders</a>

    <div class="detail-hero">
        <span class="order-id-badge">Order #ORD-<?= $order_id ?></span>
        <h1>Order Lifecycle Detail</h1>
        <p>Placed on <?= date('d M Y', strtotime($order['order_date'] ?? 'now')) ?> &nbsp;•&nbsp; Status: <span style="color: var(--primary); font-weight: 900; text-transform: uppercase;"><?= $order['status'] ?></span></p>
    </div>

    <div class="grid-layout">
        <div class="main-content">
            <!-- Timeline Section -->
            <div class="glass-card">
                <div class="card-header">
                    <h3>Delivery Progress</h3>
                    <span style="font-size: 0.7rem; background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 50px; font-weight: 800;">REAL-TIME UPDATES</span>
                </div>
                
                <?php
                $all_steps = [
                    'pending' => ['label' => 'Order Placed', 'desc' => 'We have received your order and it\'s being prepared.', 'icon' => 'fa-clock'],
                    'processing' => ['label' => 'Processing', 'desc' => 'The seller is packaging your items and preparing for handover.', 'icon' => 'fa-box-open'],
                    'shipped' => ['label' => 'Out for Delivery', 'desc' => 'Package has been handed over to our courier partner.', 'icon' => 'fa-truck-fast'],
                    'delivered' => ['label' => 'Delivered', 'desc' => 'Enjoy your new WALK-ON gear! Hope to see you again soon.', 'icon' => 'fa-check-double']
                ];
                $steps_keys = array_keys($all_steps);
                $current_step_idx = array_search($status_class, $steps_keys);
                if($current_step_idx === false) $current_step_idx = 0;
                ?>

                <div class="timeline">
                    <?php foreach($all_steps as $key => $data): 
                        $idx = array_search($key, $steps_keys);
                        $state = '';
                        if($idx < $current_step_idx) $state = 'completed';
                        elseif($idx == $current_step_idx) $state = 'active';
                    ?>
                    <div class="timeline-step <?= $state ?>">
                        <div class="step-icon">
                            <i class="fas <?= $data['icon'] ?>"></i>
                        </div>
                        <div class="step-content">
                            <h4><?= $data['label'] ?></h4>
                            <p><?= $data['desc'] ?></p>
                            <?php if($state == 'active' || $state == 'completed'): ?>
                                <div class="step-time"><?= date('d M, H:i', strtotime($order['order_date'] . " +$idx days " . (rand(1, 10)) . " hours")) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if($is_cancelled): ?>
                <div style="margin-top: 30px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 20px; padding: 20px; display: flex; align-items: center; gap: 15px; color: #dc2626;">
                    <i class="fas fa-circle-exclamation" style="font-size: 1.5rem;"></i>
                    <div style="font-weight: 800;">This order was cancelled. Your refund (if applicable) is being processed.</div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Items Section -->
            <div class="glass-card">
                <div class="card-header">
                    <h3>Package Contents (1 Item)</h3>
                </div>
                <div class="product-strip">
                    <img src="<?= htmlspecialchars($order['primary_image'] ?? $order['fallback_image'] ?? 'assets/placeholder_shoe.png') ?>" class="prod-img">
                    <div class="prod-meta">
                        <h4><?= htmlspecialchars($order['product_name']) ?></h4>
                        <p>Seller: <span style="color: var(--primary);"><?= htmlspecialchars($order['business_name']) ?></span></p>
                        <p>Quantity: 1 &nbsp;•&nbsp; Unit Price: ₹<?= number_format($order['unit_price'], 2) ?></p>
                        <div class="price-badge">TOTAL: ₹<?= number_format($order['total_price'], 2) ?></div>
                    </div>
                    <div style="margin-left: auto;">
                        <a href="product_detail.php?id=<?= $order['product_id'] ?>" class="btn btn-outline" style="padding: 10px 20px; border-radius: 12px; font-size: 0.8rem;">
                            Review Page
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Info -->
            <div class="glass-card">
                <div class="card-header">
                    <h3>Shipping Information</h3>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                    <div>
                        <h5 style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px; letter-spacing: 1px;">Customer Contact</h5>
                        <p style="font-weight: 800; font-size: 1.1rem; margin-bottom: 4px;"><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></p>
                        <p style="font-size: 0.9rem; color: var(--text-muted);"><?= htmlspecialchars($_SESSION['email'] ?? 'Not available') ?></p>
                    </div>
                    <div>
                        <h5 style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px; letter-spacing: 1px;">Delivery Address</h5>
                        <p style="font-size: 0.95rem; line-height: 1.6; font-weight: 600;">
                            <?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'Address details missing.')) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar">
            <div class="glass-card">
                <div class="card-header">
                    <h3>Summary</h3>
                </div>
                <div class="info-row">
                    <span class="info-label">Original Price</span>
                    <span class="info-value">₹<?= number_format($order['total_price'], 2) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Platform Discount</span>
                    <span class="info-value" style="color: var(--accent);">- ₹0.00</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Courier Charges</span>
                    <span class="info-value" style="color: var(--accent);">PROMO: FREE</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tax (GST 18%)</span>
                    <span class="info-value">Included</span>
                </div>
                <div class="total-row">
                    <span>Grand Total</span>
                    <span>₹<?= number_format($order['total_price'], 2) ?></span>
                </div>
                
                <div class="action-panel">
                    <a href="invoice.php?id=<?= $order_id ?>" target="_blank" class="btn btn-outline">
                        <i class="fas fa-file-pdf"></i> Download PDF Invoice
                    </a>
                    <?php if(!$is_cancelled && $status_class !== 'delivered'): ?>
                        <button class="btn btn-outline" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.2);">
                            <i class="fas fa-xmark"></i> Request Cancellation
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="support-card">
                <i class="fas fa-headset bg-icon"></i>
                <h4>Need Assistance?</h4>
                <p>Having issues with your delivery or the product quality? Our concierge team is ready to help 24/7.</p>
                <div class="action-panel">
                    <a href="https://wa.me/?text=Hi%20WalkOn%20Support,%20I'm%20inquiring%20about%20Order%20ORD-<?= $order_id ?>" target="_blank" class="btn" style="background: rgba(255,255,255,0.2); color: #fff;">
                        <i class="fab fa-whatsapp"></i> Chat with Support
                    </a>
                    <a href="#" class="btn btn-white" style="background: #fff; color: #1e3a8a;">
                        <i class="fas fa-envelope"></i> Open Dispute Ticket
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
