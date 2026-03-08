<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns & Exchanges - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 50%, #4facfe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            padding: 40px 20px;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 50px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        .header {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-hero {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .icon-hero i {
            font-size: 4rem;
            color: white;
        }

        .header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 20px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 400;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(30px);
            border-radius: 30px;
            padding: 50px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content-card h2 {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .content-card h2 i {
            font-size: 1.5rem;
        }

        .content-card p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.95);
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .content-card ul {
            list-style: none;
            padding: 0;
        }

        .content-card ul li {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 15px;
            padding-left: 35px;
            position: relative;
            line-height: 1.6;
        }

        .content-card ul li::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: white;
            background: rgba(255, 255, 255, 0.3);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .policy-highlight {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            margin: 30px 0;
            text-align: center;
        }

        .policy-highlight h3 {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 10px;
        }

        .policy-highlight p {
            font-size: 1.2rem;
            margin: 0;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .step-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            text-align: center;
            transition: all 0.3s ease;
        }

        .step-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.3);
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: white;
            color: #f5576c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0 auto 20px;
        }

        .step-card h4 {
            font-size: 1.2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }

        .step-card p {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
        }

        .btn-primary {
            display: inline-block;
            padding: 18px 40px;
            background: white;
            color: #f5576c;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }

            .content-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="header">
            <div class="icon-hero">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <h1>Returns & Exchanges</h1>
            <p>Our hassle-free "Walk Healthy" policy ensures your satisfaction</p>
        </div>

        <div class="content-card">
            <div class="policy-highlight">
                <h3>30-Day Return Policy</h3>
                <p>Not happy with your purchase? Return it within 30 days for a full refund or exchange!</p>
            </div>
        </div>

        <div class="content-card">
            <h2><i class="fas fa-undo"></i> How to Return Your Order</h2>
            <p>Returning your WALKON footwear is simple and straightforward. Follow these easy steps:</p>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4>Initiate Return</h4>
                    <p>Go to "My Orders" and select "Return Item"</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h4>Select Reason</h4>
                    <p>Choose why you're returning the product</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h4>Schedule Pickup</h4>
                    <p>Choose a convenient pickup date and time</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h4>Get Refund</h4>
                    <p>Receive refund within 5-7 business days</p>
                </div>
            </div>

            <center>
                <a href="my_orders.php" class="btn-primary">
                    <i class="fas fa-box"></i> View My Orders
                </a>
            </center>
        </div>

        <div class="content-card">
            <h2><i class="fas fa-sync-alt"></i> Exchange Policy</h2>
            <p>Want a different size or color? We've got you covered with our flexible exchange policy:</p>
            <ul>
                <li>Free exchanges for size and color variations within 30 days</li>
                <li>Exchange pickup scheduled at your convenience</li>
                <li>New product shipped immediately after pickup confirmation</li>
                <li>No additional shipping charges for exchanges</li>
                <li>Original packaging not required for exchanges</li>
            </ul>
        </div>

        <div class="content-card">
            <h2><i class="fas fa-check-circle"></i> Return Eligibility</h2>
            <p>To ensure a smooth return process, please make sure your item meets these criteria:</p>
            <ul>
                <li>Product must be unused and in original condition</li>
                <li>All tags and labels should be intact</li>
                <li>Return initiated within 30 days of delivery</li>
                <li>Product should not be damaged or altered</li>
                <li>Original invoice/receipt must be included</li>
            </ul>
        </div>

        <div class="content-card">
            <h2><i class="fas fa-money-bill-wave"></i> Refund Process</h2>
            <p>Once we receive your returned item, here's what happens:</p>
            <ul>
                <li><strong>Quality Check:</strong> We inspect the product within 24-48 hours</li>
                <li><strong>Refund Initiation:</strong> Approved refunds are processed immediately</li>
                <li><strong>Credit Timeline:</strong> Amount credited to your account in 5-7 business days</li>
                <li><strong>Refund Method:</strong> Refunded to original payment method or WALKON wallet</li>
            </ul>
        </div>

        <div class="content-card">
            <h2><i class="fas fa-ban"></i> Non-Returnable Items</h2>
            <p>For hygiene and quality reasons, the following items cannot be returned:</p>
            <ul>
                <li>Socks and inner soles</li>
                <li>Products marked as "Final Sale" or "Non-Returnable"</li>
                <li>Customized or personalized footwear</li>
                <li>Items damaged due to misuse or negligence</li>
            </ul>
        </div>

        <div class="content-card">
            <h2><i class="fas fa-question-circle"></i> Return FAQs</h2>
            <p><strong>Q: Will I be charged for return pickup?</strong></p>
            <p>No! All return pickups are completely free of charge. We believe in hassle-free returns.</p>
            
            <p style="margin-top: 20px;"><strong>Q: Can I return sale items?</strong></p>
            <p>Yes, sale items can be returned within 30 days unless specifically marked as "Final Sale".</p>
            
            <p style="margin-top: 20px;"><strong>Q: What if my return is damaged during pickup?</strong></p>
            <p>Our delivery partners handle all returns with care. In case of any damage during pickup, we'll process your refund without question.</p>

            <p style="margin-top: 20px;"><strong>Q: Can I exchange for a different product?</strong></p>
            <p>Currently, exchanges are available only for size and color variations of the same product. For different products, please return and place a new order.</p>
        </div>
    </div>
</body>
</html>
