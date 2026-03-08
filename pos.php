<?php
// pos.php - Modern POS Terminal for WALKON
session_start();
include 'config.php';

// Auth & Role Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'store_owner', 'store'])) {
    header("Location: dashboard.php");
    exit();
}

// Fetch Approved Products
try {
    $stmt = $pdo->query("
        SELECT pb.id, pb.name, pp.price, b.name as brand_name,
               (SELECT url FROM product_media WHERE product_id = pb.id ORDER BY is_primary DESC LIMIT 1) as image_url
        FROM product_base pb
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_specs spec ON pb.id = spec.product_id
        LEFT JOIN brands b ON spec.brand_id = b.id
        WHERE pb.approval_status = 'approved'
        ORDER BY pb.id DESC
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WalkOn POS | Premium Terminal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --bg-dark: #0a0f1a;
            --panel-bg: rgba(255, 255, 255, 0.04);
            --panel-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-orange: #f59e0b;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { background: var(--bg-dark); color: var(--text-main); height: 100vh; display: flex; overflow: hidden; }

        /* Layout */
        .pos-shell { display: flex; width: 100%; height: 100%; gap: 1px; background: var(--panel-border); }

        /* Navigation Rail */
        .nav-rail {
            width: 80px; background: #05070a; display: flex; flex-direction: column;
            align-items: center; padding: 25px 0; border-right: 1px solid var(--panel-border);
        }
        .nav-item {
            width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center;
            justify-content: center; color: var(--text-muted); cursor: pointer; transition: 0.3s;
            margin-bottom: 20px; font-size: 1.2rem;
        }
        .nav-item:hover { background: var(--panel-bg); color: #fff; }
        .nav-item.active { background: var(--primary); color: #000; box-shadow: 0 0 15px var(--primary-glow); }
        .nav-logo { margin-bottom: 40px; }
        .nav-logo img { width: 40px; }

        /* Product Explorer Section */
        .explorer-section { flex: 1; display: flex; flex-direction: column; padding: 25px; overflow: hidden; background: #0a0f1a; }
        .explorer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 20px; }
        .search-box {
            flex: 1; position: relative; max-width: 500px;
        }
        .search-box input {
            width: 100%; padding: 14px 20px 14px 50px; border-radius: 14px;
            background: var(--panel-bg); border: 1px solid var(--panel-border);
            color: #fff; font-size: 1rem; outline: none; transition: 0.3s;
        }
        .search-box input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
        .search-box i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

        .category-scroll { display: flex; gap: 12px; margin-bottom: 25px; overflow-x: auto; padding-bottom: 5px; }
        .category-chip {
            padding: 8px 18px; border-radius: 50px; background: var(--panel-bg); border: 1px solid var(--panel-border);
            color: var(--text-muted); font-size: 0.82rem; font-weight: 700; cursor: pointer; white-space: nowrap; transition: 0.3s;
        }
        .category-chip:hover { border-color: var(--primary); color: var(--primary); }
        .category-chip.active { background: var(--primary); color: #000; border-color: var(--primary); }

        .product-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px; overflow-y: auto; padding-right: 5px;
        }
        /* Custom scrollbar */
        .product-grid::-webkit-scrollbar { width: 4px; }
        .product-grid::-webkit-scrollbar-thumb { background: var(--panel-border); border-radius: 10px; }

        .prod-card {
            background: var(--panel-bg); border: 1px solid var(--panel-border); border-radius: 20px;
            padding: 15px; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .prod-card:hover { border-color: var(--primary); transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .prod-img {
            width: 100%; aspect-ratio: 1; background: rgba(0,0,0,0.3); border-radius: 14px;
            margin-bottom: 12px; display: flex; align-items: center; justify-content: center; padding: 10px;
        }
        .prod-img img { max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 5px 15px rgba(0,0,0,0.4)); }
        .prod-brand { font-size: 0.65rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }
        .prod-name { font-size: 0.88rem; font-weight: 700; height: 2.6rem; overflow: hidden; margin-bottom: 8px; line-height: 1.3; }
        .prod-footer { display: flex; justify-content: space-between; align-items: center; }
        .prod-price { font-weight: 900; font-size: 1.1rem; color: #fff; }
        .add-badge { 
            width: 28px; height: 28px; border-radius: 8px; background: rgba(255,255,255,0.05); 
            display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1rem;
        }
        .prod-card:hover .add-badge { background: var(--primary); color: #000; }

        /* Cart Console Section */
        .cart-console {
            width: 420px; background: #070a13; display: flex; flex-direction: column;
            padding: 25px; border-left: 1px solid var(--panel-border);
        }
        .cart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .cart-header h2 { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; }
        .cart-header h2 i { color: var(--primary); }
        .clear-cart { color: #ef4444; font-size: 0.8rem; font-weight: 700; cursor: pointer; opacity: 0.7; transition: 0.2s; }
        .clear-cart:hover { opacity: 1; text-decoration: underline; }

        .items-area { flex: 1; overflow-y: auto; margin-bottom: 25px; }
        .cart-row {
            display: flex; gap: 15px; margin-bottom: 20px; background: rgba(255,255,255,0.02);
            padding: 12px; border-radius: 14px; border: 1px solid transparent; transition: 0.2s;
        }
        .cart-row:hover { border-color: var(--panel-border); background: rgba(255,255,255,0.04); }
        .cart-row-img { width: 50px; height: 50px; border-radius: 8px; background: #000; padding: 5px; flex-shrink: 0; }
        .cart-row-img img { width: 100%; height: 100%; object-fit: contain; }
        .cart-row-info { flex: 1; }
        .cart-row-name { font-size: 0.82rem; font-weight: 700; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .cart-row-price { font-size: 0.9rem; font-weight: 800; color: #fff; }
        .qty-ctrl { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
        .qty-btn { 
            width: 24px; height: 24px; border-radius: 6px; background: rgba(255,255,255,0.08);
            border: none; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
            font-size: 0.7rem; transition: 0.2s;
        }
        .qty-btn:hover { background: var(--primary); color: #000; }
        .qty-val { font-size: 0.85rem; font-weight: 800; min-width: 20px; text-align: center; }

        .bill-pane {
            background: var(--panel-bg); border: 1px solid var(--panel-border);
            border-radius: 20px; padding: 22px;
        }
        .bill-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.85rem; color: var(--text-muted); }
        .bill-row.total { 
            border-top: 2.5px dashed var(--panel-border); margin-top: 15px; padding-top: 15px;
            font-size: 1.4rem; font-weight: 900; color: #fff;
        }
        .payment-opt { display: flex; gap: 8px; margin-top: 20px; }
        .pay-chip {
            flex: 1; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.04);
            border: 1px solid var(--panel-border); color: #fff; cursor: pointer; text-align: center;
            font-weight: 700; font-size: 0.75rem; transition: 0.2s;
        }
        .pay-chip i { display: block; margin-bottom: 6px; font-size: 1.1rem; }
        .pay-chip.active { border-color: var(--primary); background: rgba(16,185,129,0.08); color: var(--primary); }

        .checkout-btn {
            width: 100%; margin-top: 25px; padding: 20px; border-radius: 16px;
            background: var(--primary); color: #000; border: none; font-weight: 900;
            font-size: 1.1rem; cursor: pointer; box-shadow: 0 10px 25px var(--primary-glow);
            transition: 0.3s;
        }
        .checkout-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 35px var(--primary-glow); }

        /* Toast UI (Reused system) */
        .toast-container { position: fixed; top: 40px; right: 40px; z-index: 10000; display: flex; flex-direction: column; gap: 10px; }
        .toast {
            min-width: 300px; padding: 16px 20px; border-radius: 16px; background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); color: #fff;
            display: flex; align-items: center; gap: 12px; transform: translateX(120%); transition: 0.4s;
        }
        .toast.show { transform: translateX(0); }
        .toast i { color: var(--primary); }

        /* Receipt Modal */
        #receiptModal .modal-content {
            background: #fff; color: #000; padding: 40px; max-width: 400px;
            border-radius: 12px; font-family: 'Courier New', monospace;
        }
        .receipt-header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 20px; margin-bottom: 20px; }
        .receipt-line { display: flex; justify-content: space-between; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="pos-shell">
        <!-- Navigation Rail -->
        <nav class="nav-rail">
            <div class="nav-logo">
                <img src="assets/shoe_logo_green.png" alt="W">
            </div>
            <div class="nav-item active" title="New Sale"><i class="fas fa-plus"></i></div>
            <div class="nav-item" title="Transactions"><i class="fas fa-history"></i></div>
            <div class="nav-item" title="Inventory"><i class="fas fa-boxes"></i></div>
            <div class="nav-item" title="Settings"><i class="fas fa-cog"></i></div>
            <div class="nav-item" style="margin-top:auto;" title="Dashboard" onclick="location.href='dashboard.php'">
                <i class="fas fa-sign-out-alt"></i>
            </div>
        </nav>

        <!-- Product Explorer -->
        <main class="explorer-section">
            <div class="explorer-header" style="background:#0a0f1a;">
                <h1 style="font-weight: 900; letter-spacing: -1px; font-size: 1.8rem;">Terminal.01</h1>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="posSearch" placeholder="Search product name or scan code..." oninput="searchProducts(this.value)">
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="font-size:0.8rem; color:var(--text-muted);"><?= date('D, M d, Y') ?></span>
                    <button class="add-badge" style="width:auto; padding:0 12px; height:40px; cursor:default;">
                        <i class="fas fa-user-circle" style="margin-right:8px;"></i> <?= htmlspecialchars($_SESSION['role']) ?>
                    </button>
                </div>
            </div>

            <div class="category-scroll">
                <div class="category-chip active">All Categories</div>
                <div class="category-chip">Sneakers</div>
                <div class="category-chip">Formal</div>
                <div class="category-chip">Casual</div>
                <div class="category-chip">Accessories</div>
                <div class="category-chip">Cleaning Kits</div>
            </div>

            <div class="product-grid" id="posProdGrid">
                <?php foreach($products as $p): ?>
                <div class="prod-card" data-name="<?= strtolower($p['name']) ?>" onclick='addToCart(<?= json_encode($p) ?>)'>
                    <div class="prod-img">
                        <img src="<?= htmlspecialchars($p['image_url'] ?: 'assets/shoe_placeholder.png') ?>" alt="P">
                    </div>
                    <div class="prod-brand"><?= htmlspecialchars($p['brand_name'] ?: 'WALKON') ?></div>
                    <div class="prod-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="prod-footer">
                        <div class="prod-price">₹<?= number_format($p['price']) ?></div>
                        <div class="add-badge"><i class="fas fa-cart-plus"></i></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>

        <!-- Cart Console -->
        <aside class="cart-console">
            <div class="cart-header">
                <h2><i class="fas fa-receipt"></i> Current Bill</h2>
                <span class="clear-cart" onclick="clearCart()">Clear Cart</span>
            </div>

            <div class="items-area" id="cartList">
                <div style="text-align:center; margin-top:50px; color:var(--text-muted);">
                    <i class="fas fa-shopping-basket" style="font-size:3rem; opacity:0.1; margin-bottom:15px;"></i>
                    <p>Cart is empty.<br>Select products to start.</p>
                </div>
            </div>

            <div class="bill-pane">
                <div class="bill-row">
                    <span>Subtotal</span>
                    <span id="subTotal">₹0.00</span>
                </div>
                <div class="bill-row">
                    <span>Tax (GST 18%)</span>
                    <span id="taxTotal">₹0.00</span>
                </div>
                <div class="bill-row">
                    <span>Discount</span>
                    <span id="discountTotal" style="color:var(--primary);">₹0.00</span>
                </div>
                <div class="bill-row total">
                    <span>Total</span>
                    <span id="grandTotal">₹0.00</span>
                </div>

                <div class="payment-opt">
                    <div class="pay-chip active" onclick="selectPay(this)">
                        <i class="fas fa-fist-raised"></i> Cash
                    </div>
                    <div class="pay-chip" onclick="selectPay(this)">
                        <i class="fas fa-credit-card"></i> Card
                    </div>
                    <div class="pay-chip" onclick="selectPay(this)">
                        <i class="fas fa-qrcode"></i> UPI
                    </div>
                </div>

                <button class="checkout-btn" onclick="checkout()">
                    Complete Transaction
                </button>
            </div>
        </aside>
    </div>

    <!-- System Generated Toast -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Receipt Generator (Hidden) -->
    <div id="receiptModal" class="modal-overlay" style="background:rgba(0,0,0,0.95);">
        <div class="modal-content" id="receiptPrintArea">
            <div class="receipt-header">
                <h3 style="font-weight:900;">WALKON SHOES</h3>
                <p style="font-size:0.8rem; margin-top:5px;">Terminal One - Retail Hub</p>
                <p style="font-size:0.7rem;"><?= date('d-m-Y H:i') ?></p>
            </div>
            <div id="receiptItems"></div>
            <div style="border-top:1px solid #eee; padding-top:15px; margin-top:15px;">
                <div class="receipt-line"><strong>TOTAL</strong> <strong id="recTotal">₹0</strong></div>
            </div>
            <button onclick="closeModal('receiptModal')" class="checkout-btn" style="background:#000; color:#fff; border-radius:0; margin-top:20px;">DONE</button>
        </div>
    </div>

    <script>
        let cart = [];
        let currentTax = 0.18;

        function showToast(msg) {
            const t = document.createElement('div');
            t.className = 'toast';
            t.innerHTML = `<i class="fas fa-check-circle"></i> <span>${msg}</span>`;
            document.getElementById('toastContainer').appendChild(t);
            setTimeout(() => t.classList.add('show'), 100);
            setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, 3000);
        }

        function addToCart(product) {
            const existing = cart.find(i => i.id === product.id);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({ ...product, qty: 1 });
            }
            renderCart();
            showToast('Added to cart');
        }

        function updateQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if (item) {
                item.qty += delta;
                if (item.qty < 1) cart = cart.filter(i => i.id !== id);
                renderCart();
            }
        }

        function clearCart() { cart = []; renderCart(); }

        function renderCart() {
            const list = document.getElementById('cartList');
            if (cart.length === 0) {
                list.innerHTML = `<div style="text-align:center; margin-top:50px; color:var(--text-muted);"><i class="fas fa-shopping-basket" style="font-size:3rem; opacity:0.1; margin-bottom:15px;"></i><p>Cart is empty.<br>Select products to start.</p></div>`;
                updateTotals(0);
                return;
            }

            list.innerHTML = cart.map(item => `
                <div class="cart-row">
                    <div class="cart-row-img"><img src="${item.image_url || 'assets/shoe_placeholder.png'}"></div>
                    <div class="cart-row-info">
                        <div class="cart-row-name">${item.name}</div>
                        <div class="cart-row-price">₹${(item.price * item.qty).toLocaleString()}</div>
                        <div class="qty-ctrl">
                            <button class="qty-btn" onclick="updateQty(${item.id}, -1)"><i class="fas fa-minus"></i></button>
                            <span class="qty-val">${item.qty}</span>
                            <button class="qty-btn" onclick="updateQty(${item.id}, 1)"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            `).join('');

            const sub = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            updateTotals(sub);
        }

        function updateTotals(sub) {
            const tax = sub * currentTax;
            const total = sub + tax;
            document.getElementById('subTotal').textContent = '₹' + sub.toLocaleString();
            document.getElementById('taxTotal').textContent = '₹' + tax.toLocaleString();
            document.getElementById('grandTotal').textContent = '₹' + total.toLocaleString();
        }

        function selectPay(el) {
            document.querySelectorAll('.pay-chip').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
        }

        function searchProducts(query) {
            const q = query.toLowerCase();
            document.querySelectorAll('.prod-card').forEach(card => {
                const name = card.getAttribute('data-name');
                card.style.display = name.includes(q) ? '' : 'none';
            });
        }

        function checkout() {
            if (cart.length === 0) return;
            
            const btn = document.querySelector('.checkout-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            const totalStr = document.getElementById('grandTotal').textContent.replace(/[₹,]/g, '');
            const total = parseFloat(totalStr);
            const payMethod = document.querySelector('.pay-chip.active').textContent.trim();

            const saleData = {
                items: cart,
                total: total,
                payment_method: payMethod
            };

            fetch('api/process_pos_sale.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(saleData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Build Receipt
                    document.getElementById('recTotal').textContent = '₹' + total.toLocaleString();
                    document.getElementById('receiptItems').innerHTML = cart.map(item => `
                        <div class="receipt-line">
                            <span>${item.name} x${item.qty}</span>
                            <span>₹${(item.price * item.qty).toLocaleString()}</span>
                        </div>
                    `).join('');

                    openModal('receiptModal');
                    showToast('Transaction Recorded & Stock Updated!');
                    clearCart();
                } else {
                    showToast('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Connection error. Try again.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    </script>
</body>
</html>

