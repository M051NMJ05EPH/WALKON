<?php
session_start();
include 'config.php';

$tracking_result = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['tracking_id'])) {
    $search_term = trim($_POST['tracking_id'] ?? $_GET['tracking_id'] ?? '');
    
    if (!empty($search_term)) {
        try {
            // Search by Order ID or Tracking Number
            // Clean the input if it's an Order ID (remove ORD- prefix if present)
            $clean_id = str_ireplace('ORD-', '', $search_term);
            
            $stmt = $pdo->prepare("SELECT o.*, pb.name as product_name, pm.url as product_image 
                                  FROM orders o 
                                  JOIN product_base pb ON o.product_id = pb.id 
                                  LEFT JOIN product_media pm ON pb.id = pm.product_id AND pm.is_primary = 1
                                  WHERE o.id = ? OR o.tracking_number = ?");
            $stmt->execute([$clean_id, $search_term]);
            $tracking_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tracking_result) {
                $error_message = "No order found with ID or Tracking Number: " . htmlspecialchars($search_term);
            }
        } catch (PDOException $e) {
            $error_message = "System error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking & Delivery - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-purple: #a855f7;
            --primary-purple-dark: #9333ea;
            --primary-purple-glow: rgba(168, 85, 247, 0.4);
            --bg-purple: #c084fc; /* Main purple background from screenshot */
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-white: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-purple);
            color: var(--text-white);
            min-height: 100vh;
            padding: 40px 20px;
            overflow-x: hidden;
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 30px;
            padding: 10px 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            border: 1px solid var(--glass-border);
            transition: 0.3s;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(-5px);
        }

        .main-card {
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            border-radius: 40px;
            padding: 60px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-section {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 15px;
        }

        .header-icon {
            font-size: 2.2rem;
            background: var(--glass-bg);
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
        }

        .header-text h1 {
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 5px;
        }

        .header-desc {
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 850px;
        }

        .tracking-box-inner {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            border: 1px solid var(--glass-border);
        }

        .tracking-label {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 15px;
            display: block;
        }

        .input-group {
            display: flex;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            padding: 6px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .input-group:focus-within {
            background: rgba(255, 255, 255, 0.25);
            border-color: white;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
        }

        .input-group input {
            flex: 1;
            background: transparent;
            border: none;
            padding: 12px 25px;
            font-size: 1.1rem;
            color: white;
            outline: none;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .track-btn {
            background: white;
            color: var(--primary-purple-dark);
            border: none;
            padding: 0 35px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .track-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .help-note {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Results Card */
        .results-card {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 25px;
            padding: 30px;
            margin-top: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: none; /* Initially hidden, shown via result */
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .status-shipped { background: #8b5cf6; color: white; }
        .status-processing { background: #3b82f6; color: white; }
        .status-delivered { background: #10b981; color: white; }

        /* Timeline Styles */
        .timeline {
            margin-top: 40px;
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: rgba(255, 255, 255, 0.2);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-dot {
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            background: var(--primary-purple);
            border-radius: 50%;
            border: 2px solid white;
            z-index: 2;
        }

        .timeline-item.active .timeline-dot {
            background: #10b981;
            box-shadow: 0 0 15px #10b981;
        }

        .timeline-content h4 {
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        .timeline-content p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* Delivery Timelines Card */
        .secondary-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 40px;
            padding: 50px;
            border: 1px solid var(--glass-border);
            margin-bottom: 30px;
        }

        .secondary-card h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .timeline-list {
            list-style: none;
        }

        .timeline-list li {
            font-size: 1.1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .timeline-list li i {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .main-card { padding: 40px 25px; }
            .header-text h1 { font-size: 2rem; }
            .input-group { flex-direction: column; border-radius: 20px; padding: 15px; }
            .track-btn { width: 100%; height: 55px; border-radius: 12px; margin-top: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="main-card">
            <div class="header-section">
                <div class="header-icon">
                    <i class="fas fa-map-marked-alt" style="color: white;"></i>
                </div>
                <div class="header-text">
                    <h1>Track Your Order</h1>
                </div>
            </div>
            
            <p class="header-desc">
                Monitor your WALKON order in real-time with our advanced tracking system. Get instant updates on every step of your delivery journey.
            </p>
            
            <div class="tracking-box-inner">
                <label class="tracking-label">Enter Your Tracking Number</label>
                <form id="trackingForm" action="help_tracking.php" method="POST" class="input-group">
                    <input type="text" name="tracking_id" placeholder="Enter tracking number or order ID..." value="<?php echo htmlspecialchars($_POST['tracking_id'] ?? $_GET['tracking_id'] ?? ''); ?>" required autocomplete="off">
                    <button type="submit" class="track-btn" id="trackBtn" style="position: relative; z-index: 10;">
                        <i class="fas fa-search"></i> Track
                    </button>
                </form>

                <?php if ($error_message): ?>
                    <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); padding: 15px; border-radius: 15px; color: white; margin-top: 20px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($tracking_result): ?>
                    <!-- ... (Result card remains the same) ... -->
                    <div class="results-card" style="display: block; animation: fadeIn 0.5s ease;">
                        <span class="status-badge status-<?php echo strtolower($tracking_result['status']); ?>">
                            <?php echo ucfirst($tracking_result['status']); ?>
                        </span>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px;">
                            <div>
                                <h3 style="font-size: 1.4rem;">Order #ORD-<?php echo $tracking_result['id']; ?></h3>
                                <p style="color: var(--text-muted); margin: 0;">Ordered on <?php echo date('F j, Y', strtotime($tracking_result['order_date'])); ?></p>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-size: 0.9rem; color: var(--text-muted);">Estimated Delivery</p>
                                <p style="font-weight: 700; color: #10b981;">3-4 Days</p>
                            </div>
                        </div>

                        <!-- Product Summary -->
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px;">
                            <img src="<?php echo htmlspecialchars($tracking_result['product_image'] ?: 'assets/placeholder.png'); ?>" style="width: 50px; height: 50px; border-radius: 10px; background: white; object-fit: contain;">
                            <div>
                                <h4 style="font-size: 1rem;"><?php echo htmlspecialchars($tracking_result['product_name']); ?></h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted);">Quantity: 1 • Size: UK 9</p>
                            </div>
                        </div>

                        <!-- LIVE TRACKING VISUALIZATION -->
                        <h3 style="font-size: 1.2rem; margin-bottom: 25px;"><i class="fas fa-location-arrow"></i> Tracking Details</h3>
                        
                        <div class="timeline">
                            <div class="timeline-item <?php echo in_array($tracking_result['status'], ['pending', 'processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4>Order Confirmed</h4>
                                    <p>Your order has been received and is being processed.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item <?php echo in_array($tracking_result['status'], ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4>Processing</h4>
                                    <p>Our warehouse team is preparing your package for shipping.</p>
                                </div>
                            </div>

                            <div class="timeline-item <?php echo in_array($tracking_result['status'], ['shipped', 'delivered']) ? 'active' : ''; ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4>In Transit</h4>
                                    <p>Your package is on its way. Last seen at Mumbai Logistics Hub.</p>
                                </div>
                            </div>

                            <div class="timeline-item <?php echo ($tracking_result['status'] == 'delivered') ? 'active' : ''; ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4>Delivered</h4>
                                    <p>Successfully delivered to your doorstep.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Location Pin Visual -->
                        <div style="margin-top: 30px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 20px; display: flex; align-items: center; gap: 15px; border: 1px dashed rgba(255,255,255,0.2);">
                            <div style="width: 40px; height: 40px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 15px rgba(16,185,129,0.3);">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Current Location</p>
                                <p style="font-weight: 600;">Mumbai Distribution Center, Maharashtra</p>
                            </div>
                            <button style="margin-left: auto; background: transparent; border: 1px solid rgba(255,255,255,0.2); color: white; padding: 6px 12px; border-radius: 50px; font-size: 0.8rem; cursor: pointer;">
                                View on Map
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="help-note">
                        <i class="fas fa-info-circle"></i> You can find your tracking number in your order confirmation email
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timelines Card -->
        <div class="secondary-card">
            <h2><i class="fas fa-clock"></i> Delivery Timelines</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">We strive to get your footwear to you as quickly as possible. Here are our standard delivery timelines:</p>
            
            <ul class="timeline-list">
                <li><i class="fas fa-check"></i> <strong>Metro Cities:</strong> 2-3 business days</li>
                <li><i class="fas fa-check"></i> <strong>Tier 2 Cities:</strong> 3-5 business days</li>
                <li><i class="fas fa-check"></i> <strong>Other Locations:</strong> 5-7 business days</li>
            </ul>
        </div>
    </div>

    <script>
        document.getElementById('trackingForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('trackBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Tracking...';
            btn.style.opacity = '0.8';
            // Not setting pointer-events: none to avoid click interruption
        });
    </script>
</body>
</html>
