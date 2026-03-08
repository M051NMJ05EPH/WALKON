<?php
session_start();
include 'config.php';

// Fetch active payment methods from database
try {
    $stmt = $pdo->prepare("SELECT method_key, icon_class, title, description FROM payment_methods WHERE is_active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching payment methods: " . $e->getMessage());
}

// Sample order items for demo
$cartItems = [
    ['name' => 'WALKON Prime Oxford', 'price' => 2499, 'qty' => 1, 'img' => 'assets/shoe1.png'],
    ['name' => 'Premium Leather Belt', 'price' => 899, 'qty' => 1, 'img' => 'assets/belt.png']
];
$subtotal = array_reduce($cartItems, function($acc, $item) { return $acc + ($item['price'] * $item['qty']); }, 0);
$shipping = 0; // Free shipping for premium
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-purple: #a855f7;
            --primary-purple-dark: #9333ea;
            --bg-purple: #c084fc;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.4);
            --text-dark: #111827;
            --text-muted: #6b7280;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }

        body {
            background: linear-gradient(135deg, #f3e8ff 0%, #fae8ff 100%);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background: white;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo { font-size: 1.5rem; font-weight: 900; color: var(--text-dark); text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .logo span { color: var(--primary-purple); }

        .checkout-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
        }

        .section-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            border: 1px solid #f3f4f6;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--primary-purple);
        }

        /* Form Controls */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group.full { grid-column: span 2; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; }
        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border-radius: 15px;
            border: 2px solid #f3f4f6;
            outline: none;
            transition: 0.3s;
            background: #f9fafb;
            font-size: 1rem;
        }
        .form-group input:focus { border-color: var(--primary-purple); background: white; box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.1); }

        /* Payment Selection */
        .payment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }
        .payment-option {
            background: #f9fafb;
            border: 2px solid #f3f4f6;
            border-radius: 20px;
            padding: 20px 10px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .payment-option i { font-size: 1.8rem; color: var(--text-muted); transition: 0.3s; }
        .payment-option span { font-size: 0.9rem; font-weight: 700; color: var(--text-muted); }
        .payment-option:hover { transform: translateY(-3px); border-color: var(--primary-purple); }
        .payment-option.active {
            background: white;
            border-color: var(--primary-purple);
            box-shadow: 0 10px 20px rgba(168, 85, 247, 0.1);
        }
        .payment-option.active i { color: var(--primary-purple); }
        .payment-option.active span { color: var(--text-dark); }

        /* Order Summary */
        .summary-card { position: sticky; top: 120px; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 1rem; }
        .summary-total {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px dashed #f3f4f6;
            display: flex;
            justify-content: space-between;
            font-size: 1.5rem;
            font-weight: 900;
        }

        .checkout-btn {
            width: 100%;
            margin-top: 30px;
            padding: 20px;
            background: var(--primary-purple);
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 1.2rem;
            font-weight: 800;
            cursor: pointer;
            transition: 0.4s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 15px 30px rgba(168, 85, 247, 0.3);
        }
        .checkout-btn:hover { background: var(--primary-purple-dark); transform: translateY(-5px); box-shadow: 0 20px 40px rgba(168, 85, 247, 0.4); }
        .checkout-btn:disabled { background: #9ca3af; cursor: not-allowed; transform: none; box-shadow: none; }

        .loader {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Cart Items */
        .cart-item { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .cart-img { width: 60px; height: 60px; background: #f9fafb; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .cart-info h4 { font-weight: 700; margin-bottom: 2px; }
        .cart-info p { font-size: 0.85rem; color: var(--text-muted); }

        @media (max-width: 968px) {
            .checkout-container { grid-template-columns: 1fr; }
            .summary-card { position: static; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo"><span>WALK</span>ON</a>
        <div style="font-weight:700; color:var(--text-muted)"><i class="fas fa-lock"></i> Secure Checkout</div>
    </nav>

    <main class="checkout-container">
        <!-- Left Side: Form -->
        <form id="checkoutForm">
            <div class="section-card">
                <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Delivery Information</h2>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Full Name</label>
                        <input type="text" name="full_name" placeholder="E.g. John Doe" required>
                    </div>
                    <div class="form-group full">
                        <label>Shipping Address</label>
                        <input type="text" name="address" placeholder="Street, Apartment, Suite" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" placeholder="E.g. Mumbai" required>
                    </div>
                    <div class="form-group">
                        <label>PIN Code</label>
                        <input type="text" name="pincode" placeholder="6-digit code" required>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h2 class="section-title"><i class="fas fa-wallet"></i> Payment Method</h2>
                <div class="payment-grid">
                    <?php foreach ($paymentMethods as $method): ?>
                    <div class="payment-option" onclick="setPaymentMethod('<?php echo $method['method_key']; ?>', this)">
                        <i class="fas <?php echo $method['icon_class']; ?>"></i>
                        <span><?php echo $method['title']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="payment_method" id="selectedPaymentMethod" required>
                <input type="hidden" name="total_price" value="<?php echo $total; ?>">
            </div>
        </form>

        <!-- Right Side: Summary -->
        <aside class="summary-card">
            <div class="section-card">
                <h2 class="section-title">Order Summary</h2>
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-img"><i class="fas fa-shoe-prints" style="color:#d1d5db"></i></div>
                        <div class="cart-info">
                            <h4><?php echo $item['name']; ?></h4>
                            <p>Qty: <?php echo $item['qty']; ?> • Premium Quality</p>
                        </div>
                        <div style="margin-left:auto; font-weight:700">₹<?php echo $item['price']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top:20px">
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>₹<?php echo $subtotal; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping</span>
                        <span style="color:var(--primary-purple)">FREE</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>₹<?php echo $total; ?></span>
                    </div>
                </div>

                <button type="button" class="checkout-btn" id="submitBtn" onclick="processPayment()">
                    <span id="btnText">Place Your Order</span>
                    <div class="loader" id="btnLoader"></div>
                </button>
                <p style="text-align:center; font-size:0.8rem; margin-top:15px; color:var(--text-muted)">
                    By placing your order, you agree to our Terms of Use and Privacy Policy.
                </p>
            </div>
        </aside>
    </main>

    <script>
        function setPaymentMethod(method, element) {
            document.getElementById('selectedPaymentMethod').value = method;
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('active'));
            element.classList.add('active');
        }

        async function processPayment() {
            const form = document.getElementById('checkoutForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const paymentMethod = document.getElementById('selectedPaymentMethod').value;
            if (!paymentMethod) {
                alert('Please select a payment method.');
                return;
            }

            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('btnText');
            const loader = document.getElementById('btnLoader');

            // UI Feedback
            btn.disabled = true;
            text.style.display = 'none';
            loader.style.display = 'block';

            const formData = new FormData(form);

            try {
                const response = await fetch('api/process_payment.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    // Success state
                    btn.style.background = '#10b981';
                    text.innerHTML = '<i class="fas fa-check"></i> Success!';
                    text.style.display = 'block';
                    loader.style.display = 'none';
                    
                    setTimeout(() => {
                        // If UPI, redirect to dedicated QR page
                        if (paymentMethod === 'upi') {
                            window.location.href = `payment_qr.php?order_id=${result.order_id}`;
                        } else {
                            window.location.href = result.redirect;
                        }
                    }, 1500);
                } else {
                    alert(result.message);
                    resetButton();
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                resetButton();
            }
        }

        function resetButton() {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('btnText');
            const loader = document.getElementById('btnLoader');
            btn.disabled = false;
            text.style.display = 'block';
            loader.style.display = 'none';
        }
    </script>
</body>
</html>
