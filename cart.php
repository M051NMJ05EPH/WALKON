<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;

// Handle Empty Cart / Not Logged In
$cart_items = [];
$subtotal = 0;
$total_count = 0;

if ($user_id) {
    // Fetch Cart Items
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, pb.id as product_id, pb.name, pp.price, pm.url as image
        FROM cart c
        JOIN product_base pb ON c.product_id = pb.id
        JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_media pm ON pb.id = pm.product_id AND pm.is_primary = 1
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_count += $item['quantity'];
    }
}

// Fetch Recently Viewed Items
$recently_viewed = [];
if (isset($_SESSION['recently_viewed']) && !empty($_SESSION['recently_viewed'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['recently_viewed']), '?'));
    $stmt_rv = $pdo->prepare("
        SELECT pb.id, pb.name, pp.price, pm.url as image
        FROM product_base pb
        JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_media pm ON pb.id = pm.product_id AND pm.is_primary = 1
        WHERE pb.id IN ($placeholders)
        ORDER BY FIELD(pb.id, $placeholders)
        LIMIT 4
    ");
    // Duplicate values for the IN and FIELD clauses
    $rv_params = array_merge($_SESSION['recently_viewed'], $_SESSION['recently_viewed']);
    $stmt_rv->execute($rv_params);
    $recently_viewed = $stmt_rv->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - WALKON Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root { 
            --primary: #2563eb;
            --primary-green: #10b981;
            --primary-glow: rgba(37, 99, 235, 0.2);
            --bg: #f0f6ff;
            --card-bg: rgba(255, 255, 255, 0.85);
            --glass-bg: rgba(239, 246, 255, 0.7);
            --border: #c7dcff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --navy: #0f172a;
            --font-heading: 'Playfair Display', serif;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        
        body { 
            background:
                radial-gradient(ellipse at 0% 0%, rgba(37, 99, 235, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 100% 0%, rgba(96, 165, 250, 0.18) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 100%, rgba(37, 99, 235, 0.08) 0%, transparent 60%),
                linear-gradient(160deg, #e0eeff 0%, #f0f6ff 40%, #ffffff 70%, #e8f3ff 100%);
            color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column;
        }

        /* Back Button */
        .back-btn-container {
            max-width: 1300px;
            margin: 100px auto 0;
            padding: 0 40px;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
            background: rgba(255,255,255,0.7);
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }
        .back-btn:hover {
            color: var(--primary);
            transform: translateX(-5px);
            border-color: var(--primary);
        }

        /* Navbar */
        .navbar { 
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #1d4ed8 100%);
            backdrop-filter: blur(20px);
            padding: 1.25rem 5%; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 4px 24px rgba(37, 99, 235, 0.3);
        }
        .logo { font-size: 1.5rem; font-weight: 800; color: white; text-decoration:none; display: flex; align-items: center; gap: 0.5rem; }
        .logo span { color: #34d399; }

        .container {
            max-width: 1300px;
            margin: 120px auto 60px;
            padding: 0 40px;
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 40px;
            width: 100%;
        }

        @media (max-width: 1024px) {
            .container { grid-template-columns: 1fr; }
        }

        /* Cart Main */
        .cart-main {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border-radius: 28px;
            border: 1px solid var(--border);
            padding: 40px;
            box-shadow: 0 8px 32px rgba(37, 99, 235, 0.08);
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 30px;
        }

        .cart-header h1 {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            color: var(--navy);
        }

        .cart-header .price-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr 120px;
            gap: 25px;
            padding: 25px 0;
            border-bottom: 1px solid var(--border);
            transition: 0.3s;
        }

        .cart-item:last-child { border-bottom: none; }
        .cart-item:hover { background: rgba(239,246,255,0.5); border-radius: 16px; padding-left: 12px; padding-right: 12px; }

        .item-img {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #e0eeff, #f0f6ff);
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border: 1px solid var(--border);
        }

        .item-img img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 6px 12px rgba(37,99,235,0.1));
        }

        .item-info h3 {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--navy);
        }

        .item-meta {
            color: var(--primary-green);
            font-size: 0.85rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            background: rgba(239,246,255,0.8);
            border-radius: 10px;
            padding: 5px;
            border: 1.5px solid var(--border);
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: transparent;
            color: var(--primary);
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: 700;
        }

        .qty-btn:hover { background: #dbeafe; color: #1d4ed8; }

        .qty-input {
            width: 40px;
            text-align: center;
            background: transparent;
            border: none;
            color: var(--navy);
            font-weight: 800;
            font-size: 1rem;
        }

        .delete-btn {
            color: #ef4444;
            background: transparent;
            border: none;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .delete-btn:hover { background: rgba(239, 68, 68, 0.08); }

        .item-price {
            text-align: right;
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--primary);
        }

        /* Summary Sidebar */
        .summary-sidebar {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .summary-card {
            background: linear-gradient(160deg, #ffffff 0%, #eef5ff 100%);
            backdrop-filter: blur(16px);
            border-radius: 28px;
            border: 1px solid var(--border);
            padding: 30px;
            position: sticky;
            top: 120px;
            box-shadow: 0 8px 32px rgba(37, 99, 235, 0.1);
        }

        .subtotal-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 25px;
            color: var(--navy);
        }

        .checkout-btn {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            text-align: center;
            padding: 18px;
            border-radius: 14px;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
            transition: 0.3s;
            margin-bottom: 20px;
        }

        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        }

        /* Recently Viewed Section */
        .rv-section {
            background: linear-gradient(160deg, #ffffff 0%, #eef5ff 100%);
            border-radius: 24px;
            border: 1px solid var(--border);
            padding: 25px;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.06);
        }

        .rv-section h4 {
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .rv-grid {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .rv-item {
            display: flex;
            gap: 15px;
            align-items: center;
            text-decoration: none;
            transition: 0.3s;
            padding: 8px;
            border-radius: 12px;
        }

        .rv-item:hover { transform: translateX(5px); background: rgba(219,234,254,0.4); }

        .rv-img {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #e0eeff, #f0f6ff);
            border-radius: 10px;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
        }

        .rv-img img { max-width: 100%; max-height: 100%; object-fit: contain; }

        .rv-info h5 {
            font-size: 0.9rem;
            color: var(--navy);
            font-weight: 700;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
        }

        .rv-info .price {
            font-size: 0.85rem;
            color: var(--primary);
            font-weight: 800;
        }

        /* Empty State */
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-img {
            width: 250px;
            margin-bottom: 30px;
            opacity: 0.7;
        }

        .empty-cart h2 {
            font-family: var(--font-heading);
            font-size: 2.2rem;
            margin-bottom: 15px;
            color: var(--navy);
        }

        .empty-cart p {
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        .empty-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn-outline {
            padding: 14px 25px;
            border: 1.5px solid var(--border);
            border-radius: 50px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
            background: rgba(239,246,255,0.7);
        }

        .btn-outline:hover { background: #dbeafe; border-color: var(--primary); }

        footer {
            margin-top: auto;
            border-top: 1px solid var(--border);
            padding: 40px 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
            background: rgba(255,255,255,0.5);
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">
            <img src="assets/shoe_logo_green.png" alt="Logo" style="height: 40px;">
            WALK<span>ON</span>
        </a>
    </nav>

    <div class="back-btn-container">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="container">
        <!-- Main Cart Area -->
        <div class="cart-main">
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <img src="https://m.media-amazon.com/images/G/31/cart/empty/kettle-desaturated._CB424644214_.svg" class="empty-img" alt="Empty Cart">
                    <h2>Your WALKON Cart is empty</h2>
                    <p>Unlock premium footwear today. Shop our latest luxury collections.</p>
                    <div class="empty-actions">
                        <?php if (!$user_id): ?>
                            <a href="login.php" class="checkout-btn" style="width: auto; padding: 14px 40px;">Sign in to your account</a>
                            <a href="register.php" class="btn-outline">Sign up now</a>
                        <?php else: ?>
                            <a href="shop.php" class="checkout-btn" style="width: auto; padding: 14px 40px;">Shop today's deals</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-header">
                    <h1>Shopping Cart</h1>
                    <span class="price-label">Price</span>
                </div>

                <div class="cart-items-list">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-product-id="<?= $item['product_id'] ?>">
                            <div class="item-img">
                                <img src="<?= htmlspecialchars($item['image'] ?: 'https://via.placeholder.com/200') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="item-info">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="item-meta">In Stock | Eligible for FREE Shipping</div>
                                <div class="item-actions">
                                    <div class="quantity-control">
                                        <button class="qty-btn" onclick="updateQty(<?= $item['product_id'] ?>, -1)"><i class="fas fa-minus"></i></button>
                                        <input type="text" class="qty-input" value="<?= $item['quantity'] ?>" readonly>
                                        <button class="qty-btn" onclick="updateQty(<?= $item['product_id'] ?>, 1)"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <button class="delete-btn" onclick="removeItem(<?= $item['product_id'] ?>)">Delete</button>
                                </div>
                            </div>
                            <div class="item-price">
                                ₹<?= number_format($item['price']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="text-align: right; padding-top: 30px; font-size: 1.2rem; font-weight: 500;">
                    Subtotal (<?= $total_count ?> items): <strong style="color: var(--primary); font-size: 1.5rem;">₹<?= number_format($subtotal) ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="summary-sidebar">
            <?php if (!empty($cart_items)): ?>
                <div class="summary-card">
                    <div class="subtotal-row">
                        <span>Subtotal</span>
                        <span>₹<?= number_format($subtotal) ?></span>
                    </div>
                    <button class="checkout-btn" id="payProceedBtn" onclick="proceedPayment(this)" style="width: 100%; border: none; cursor: pointer;">Proceed to Checkout</button>
                    <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center;">100% Secure Transaction</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($recently_viewed)): ?>
                <div class="rv-section">
                    <h4>Recently viewed</h4>
                    <div class="rv-grid">
                        <?php foreach ($recently_viewed as $rv): ?>
                            <a href="product_detail.php?id=<?= $rv['id'] ?>" class="rv-item">
                                <div class="rv-img">
                                    <img src="<?= htmlspecialchars($rv['image'] ?: 'https://via.placeholder.com/100') ?>" alt="<?= htmlspecialchars($rv['name']) ?>">
                                </div>
                                <div class="rv-info">
                                    <h5><?= htmlspecialchars($rv['name']) ?></h5>
                                    <div class="price">₹<?= number_format($rv['price']) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container" style="margin: 0 auto; display: block; max-width: 800px; padding: 0 20px;">
            <p>The price and availability of items at WALKON.com are subject to change. The shopping cart is a temporary place to store a list of your items and reflects each item's most recent price.</p>
            <p style="margin-top: 20px;">© 2026 WALKON Footwear India. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function updateQty(productId, delta) {
            const row = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
            const input = row.querySelector('.qty-input');
            let newQty = parseInt(input.value) + delta;
            
            if (newQty < 1) return;

            fetch('api/update_cart_quantity.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: newQty })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Simple reload to update all totals
                }
            });
        }

        function removeItem(productId) {
            if (!confirm('Are you sure you want to remove this item?')) return;

            fetch('api/remove_from_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        // ===== RAZORPAY PAYMENT LOGIC =====
        function proceedPayment(btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            const totalAmount = Math.round(<?= floatval($subtotal) ?>);
            const amountInPaise = totalAmount * 100;

            fetch('api/create_razorpay_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: totalAmount })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.order_id) {
                    var options = {
                        "key": "rzp_test_SJT6Nr8fIlTpbw", // USER PROVIDED TEST KEY
                        "amount": amountInPaise, 
                        "currency": "INR",
                        "name": "WALKON Premium",
                        "description": "Footwear Purchase",
                        "image": "assets/shoe_logo_green.png",
                        "order_id": data.order_id,
                        "handler": function (response){
                            // Verify payment in backend
                            fetch('api/verify_razorpay_payment.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_signature: response.razorpay_signature
                                })
                            })
                            .then(res => res.json())
                            .then(verifyData => {
                                if(verifyData.success) {
                                    alert("Payment Successful!");
                                    window.location.href = 'my_orders.php';
                                } else {
                                    alert("Verification Failed: " + verifyData.message);
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                }
                            }).catch(err => {
                                alert("Verification Server Error");
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                            });
                        },
                        "prefill": {
                            "name": "<?php echo isset($_SESSION['user_id']) ? 'User' : 'Guest'; ?>",
                            "email": "customer@walkon.com",
                            "contact": "9000000000"
                        },
                        "theme": {
                            "color": "#2563eb"
                        },
                        "modal": {
                            "ondismiss": function(){
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                            }
                        }
                    };
                    var rzp1 = new Razorpay(options);
                    rzp1.on('payment.failed', function (response){
                        alert("Payment Failed: " + response.error.description);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
                    rzp1.open();
                } else {
                    alert("Could not initialize Razorpay: " + (data.message || 'Unknown Error'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }).catch(err => {
                console.error(err);
                alert("Network error, could not initialize payment.");
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
