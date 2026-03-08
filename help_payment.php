<?php
include 'config.php';

// Fetch active payment methods from database
try {
    $stmt = $pdo->prepare("SELECT method_key, icon_class, title, description FROM payment_methods WHERE is_active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare metadata for JavaScript
    $jsMetadata = [];
    foreach ($paymentMethods as $method) {
        $jsMetadata[$method['method_key']] = [
            'icon' => $method['icon_class'],
            'title' => $method['title'],
            'detail' => $method['description']
        ];
    }
} catch (PDOException $e) {
    die("Error fetching payment methods: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-purple: #a855f7;
            --primary-purple-dark: #9333ea;
            --bg-purple: #c084fc;
            --glass-bg: rgba(255, 255, 255, 0.22);
            --glass-border: rgba(255, 255, 255, 0.35);
            --text-white: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.9);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }

        body {
            background-color: var(--bg-purple);
            color: var(--text-white);
            min-height: 100vh;
            padding: 40px 20px;
            overflow-x: hidden;
            position: relative;
        }

        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            position: relative;
            z-index: 10;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-weight: 700;
            margin-bottom: 40px;
            padding: 14px 28px;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 60px;
            border: 2px solid var(--glass-border);
            transition: 0.4s;
            cursor: pointer;
        }
        .back-btn:hover { background: rgba(255, 255, 255, 0.4); transform: translateX(-8px); }

        .header { text-align: center; margin-bottom: 60px; animation: slideUp 1s cubic-bezier(0.23, 1, 0.32, 1); }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .header h1 { font-size: 3.8rem; font-weight: 900; margin-bottom: 15px; letter-spacing: -2.5px; }
        .header p { font-size: 1.3rem; color: var(--text-muted); max-width: 750px; margin: 0 auto; line-height: 1.6; }

        .main-card {
            background: var(--glass-bg);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border-radius: 45px;
            padding: 55px;
            border: 2px solid var(--glass-border);
            margin-bottom: 35px;
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.2);
        }

        .card-title { font-size: 2.2rem; font-weight: 900; margin-bottom: 40px; position: relative; padding-left: 20px; }
        .card-title::before { content: ''; position: absolute; left: 0; top: 10%; height: 80%; width: 6px; background: white; border-radius: 10px; }

        .payment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 25px;
            position: relative;
            z-index: 20;
        }

        .payment-box {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid var(--glass-border);
            border-radius: 35px;
            padding: 45px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 210px;
            position: relative;
            z-index: 30;
            overflow: hidden;
        }

        .payment-box::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: 0.3s;
        }

        .payment-box:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-15px);
            border-color: #ffffff;
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }

        .payment-box:hover::after { opacity: 1; }
        .payment-box:active { transform: scale(0.92); }

        .payment-box i { font-size: 3.5rem; margin-bottom: 25px; color: white; pointer-events: none; }
        .payment-box h4 { font-size: 1.25rem; font-weight: 900; line-height: 1.3; pointer-events: none; }

        /* Secondary Information Cards */
        .info-card {
            background: var(--glass-bg);
            backdrop-filter: blur(40px);
            border-radius: 40px;
            padding: 45px;
            border: 2px solid var(--glass-border);
            margin-bottom: 35px;
        }

        .faq-item {
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 25px;
        }
        .faq-header {
            font-size: 1.25rem;
            font-weight: 900;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .faq-body {
            margin-top: 15px;
            color: var(--text-muted);
            display: none;
            font-size: 1.1rem;
            line-height: 1.8;
            animation: fadeIn 0.4s ease;
        }

        /* Modal Experience */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 1000000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-card {
            background: #ffffff;
            color: #000000;
            max-width: 580px;
            width: 100%;
            border-radius: 50px;
            padding: 55px;
            position: relative;
            transform: translateY(0);
            animation: modalLaunch 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 50px 100px rgba(0,0,0,0.5);
        }
        @keyframes modalLaunch { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .modal-close {
            position: absolute;
            top: 35px; right: 35px;
            background: #f3f5f7;
            border: none;
            width: 50px; height: 50px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            transition: 0.4s;
        }
        .modal-close:hover { background: #eef1f4; transform: rotate(90deg) scale(1.1); }

        .modal-icon-box {
            width: 110px; height: 110px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 35px;
            font-size: 4rem;
            color: var(--primary-purple);
        }

        .modal-action-btn {
            width: 100%;
            margin-top: 40px;
            padding: 20px;
            background: #a855f7;
            color: white;
            border: none;
            border-radius: 20px;
            font-weight: 900;
            cursor: pointer;
            font-size: 1.1rem;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(168, 85, 247, 0.3);
        }
        .modal-action-btn:hover { background: #9333ea; transform: translateY(-3px); box-shadow: 0 15px 30px rgba(168, 85, 247, 0.4); }

        @media (max-width: 768px) {
            .header h1 { font-size: 3rem; }
            .payment-grid { grid-template-columns: 1fr 1fr; }
            .main-card { padding: 40px 25px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>

        <div class="header">
            <h1>Payment Options</h1>
            <p>We provide the safest and most flexible payment methods to ensure your shopping experience is effortless.</p>
        </div>

        <div class="main-card">
            <h2 class="card-title">Accepted Payment Methods</h2>
            <div class="payment-grid" id="mainPaymentGrid">
                <?php foreach ($paymentMethods as $method): ?>
                <div class="payment-box" data-id="<?php echo htmlspecialchars($method['method_key']); ?>">
                    <i class="fas <?php echo htmlspecialchars($method['icon_class']); ?>"></i>
                    <h4><?php echo str_replace(' ', '<br>', htmlspecialchars($method['title'])); ?></h4>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: 50px; text-align: center;">
                <a href="checkout.php" class="back-btn" style="background: var(--primary-purple); border-color: white; padding: 20px 50px; font-size: 1.2rem; transform: none; display: inline-flex;">
                    Proceed to Secure Checkout <i class="fas fa-chevron-right" style="margin-left: 10px;"></i>
                </a>
            </div>
        </div>

        <div class="info-card">
            <h2 class="card-title"><i class="fas fa-info-circle"></i> Security FAQ</h2>
            <div class="faq-item">
                <div class="faq-header">Is my transaction secure? <i class="fas fa-plus"></i></div>
                <div class="faq-body">Absolutely. Every payment is processed through a bank-grade 256-bit SSL encrypted gateway. We never store your full card numbers.</div>
            </div>
            <div class="faq-item">
                <div class="faq-header">Can I pay using multiple methods? <i class="fas fa-plus"></i></div>
                <div class="faq-body">Currently, only one payment method can be used per order. You can, however, combine wallet balances with Gift Cards where applicable.</div>
            </div>
        </div>
    </div>

    <!-- Modal Overlay -->
    <div class="modal-overlay" id="paymentDetailsModal">
        <div class="modal-card">
            <button class="modal-close" id="btnExitModal"><i class="fas fa-times"></i></button>
            <div id="modalInjectionArea"></div>
        </div>
    </div>

    <script>
        // Data injected from backend
        const paymentMetadata = <?php echo json_encode($jsMetadata); ?>;

        const modalOverlay = document.getElementById('paymentDetailsModal');
        const modalContainer = document.getElementById('modalInjectionArea');

        // Logic for opening the modal
        function triggerDetails(id) {
            console.log("Triggering details for:", id);
            const info = paymentMetadata[id];
            if (!info) return;

            modalContainer.innerHTML = `
                <div class="modal-icon-box"><i class="fas ${info.icon}"></i></div>
                <h2 style="text-align:center;font-size:2.2rem;margin-bottom:20px;font-weight:900;letter-spacing:-1px">${info.title}</h2>
                <p style="text-align:center;color:#555;line-height:1.9;font-size:1.15rem">${info.detail}</p>
                <button class="modal-action-btn" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
            `;
            
            modalOverlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            console.log("Modal opened successfully");
        }

        function exitModal() {
            modalOverlay.style.display = 'none';
            document.body.style.overflow = 'auto';
            console.log("Modal closed successfully");
        }

        // Initialize grid listeners
        document.getElementById('mainPaymentGrid').addEventListener('click', function(event) {
            const targetBox = event.target.closest('.payment-box');
            if (targetBox) {
                const identifier = targetBox.getAttribute('data-id');
                triggerDetails(identifier);
            }
        });

        // Close logic for overlay and button
        document.getElementById('btnExitModal').addEventListener('click', exitModal);
        modalOverlay.addEventListener('click', (e) => { 
            if (e.target === modalOverlay) exitModal(); 
        });

        // FAQ Toggle Logic
        document.querySelectorAll('.faq-header').forEach(header => {
            header.onclick = function() {
                const body = this.nextElementSibling;
                const icon = this.querySelector('i');
                const isNowOpen = body.style.display === 'block';
                body.style.display = isNowOpen ? 'none' : 'block';
                icon.className = isNowOpen ? 'fas fa-plus' : 'fas fa-minus';
                console.log("FAQ Toggled:", isNowOpen ? "Closed" : "Opened");
            };
        });

        console.log("Payment page script initialized.");
    </script>
</body>
</html>
