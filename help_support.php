<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-purple: #a855f7;
            --primary-purple-dark: #9333ea;
            --bg-purple: #c084fc;
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
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .back-nav {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 40px;
            padding: 10px 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            border: 1px solid var(--glass-border);
            transition: 0.3s;
        }

        .back-nav:hover { background: rgba(255, 255, 255, 0.25); transform: translateX(-5px); }

        .header {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }

        .header p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Search Bar */
        .search-container {
            max-width: 650px;
            margin: 0 auto 60px;
        }

        .search-wrapper {
            position: relative;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 50px;
            border: 1px solid var(--glass-border);
            padding: 6px;
            display: flex;
            transition: 0.3s;
        }

        .search-wrapper:focus-within {
            background: rgba(255, 255, 255, 0.25);
            border-color: white;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
        }

        .search-wrapper input {
            flex: 1;
            background: transparent;
            border: none;
            padding: 15px 25px;
            font-size: 1.1rem;
            color: white;
            outline: none;
        }

        .search-wrapper input::placeholder { color: rgba(255, 255, 255, 0.6); }

        .search-btn {
            background: white;
            color: var(--primary-purple-dark);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: 0.3s;
        }

        .search-btn:hover { transform: scale(1.05); }

        /* Category Grid */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 80px;
        }

        .category-card {
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            border-radius: 30px;
            padding: 35px;
            border: 1px solid var(--glass-border);
            text-decoration: none;
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .category-card:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.25);
            border-color: white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .cat-icon {
            width: 60px;
            height: 60px;
            background: var(--glass-bg);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            border: 1px solid var(--glass-border);
        }

        .category-card h3 { font-size: 1.4rem; font-weight: 700; }
        .category-card p { font-size: 1rem; color: var(--text-muted); line-height: 1.5; }

        /* Contact Section (User Requested Designs) */
        .contact-section {
            display: flex;
            flex-direction: column;
            gap: 25px;
            margin-top: 50px;
        }

        .contact-card {
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            border-radius: 40px;
            padding: 60px 40px;
            border: 1px solid var(--glass-border);
            text-align: center;
            text-decoration: none;
            color: white;
            transition: 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .contact-card:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.02);
            border-color: white;
        }

        .contact-icon {
            font-size: 3.5rem;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }

        .contact-card h2 { font-size: 2.2rem; font-weight: 800; }
        .contact-card .contact-info { font-size: 1.8rem; font-weight: 700; margin: 5px 0; }
        .contact-card .contact-detail { font-size: 1.1rem; color: var(--text-muted); }

        @media (max-width: 768px) {
            .header h1 { font-size: 2.8rem; }
            .category-grid { grid-template-columns: 1fr; }
            .contact-card { padding: 40px 20px; }
            .contact-card h2 { font-size: 1.8rem; }
            .contact-card .contact-info { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-nav"><i class="fas fa-arrow-left"></i> Back</a>

        <div class="header">
            <h1>How Can We Help?</h1>
            <p>Your footwear journey matters to us. Our team of footwear experts is ready to assist you with any questions or concerns.</p>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <div class="search-wrapper">
                <input type="text" placeholder="Search for help articles, FAQs, or guides...">
                <button class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Help Categories Grid -->
        <div class="category-grid">
            <a href="help_tracking.php" class="category-card">
                <div class="cat-icon"><i class="fas fa-shipping-fast"></i></div>
                <h3>Order Tracking</h3>
                <p>Track your orders in real-time and manage delivery preferences.</p>
            </a>
            <a href="help_returns.php" class="category-card">
                <div class="cat-icon"><i class="fas fa-exchange-alt"></i></div>
                <h3>Returns & Exchanges</h3>
                <p>Learn about our 30-day "Walk Healthy" policy and easy returns.</p>
            </a>
            <a href="help_account.php" class="category-card">
                <div class="cat-icon"><i class="fas fa-user-shield"></i></div>
                <h3>Account Security</h3>
                <p>Manage passwords, 2FA, and keep your personal data safe.</p>
            </a>
            <a href="help_payment.php" class="category-card">
                <div class="cat-icon"><i class="fas fa-credit-card"></i></div>
                <h3>Payment Options</h3>
                <p>Secure payment methods, installments, and wallet integrations.</p>
            </a>
        </div>

        <!-- Premium Contact Cards (Exactly as per screenshot) -->
        <div class="contact-section">
            <a href="tel:+911234567890" class="contact-card">
                <i class="fas fa-phone contact-icon"></i>
                <h2>Call Us</h2>
                <p class="contact-info">+91 123 456 7890</p>
                <p class="contact-detail">Available 24/7</p>
            </a>

            <a href="mailto:support@walkon.com" class="contact-card">
                <i class="fas fa-envelope contact-icon"></i>
                <h2>Email Support</h2>
                <p class="contact-info">support@walkon.com</p>
                <p class="contact-detail">Response within 24 hours</p>
            </a>

            <div class="contact-card" style="cursor: default;">
                <i class="fas fa-comments contact-icon"></i>
                <h2>Live Chat</h2>
                <p class="contact-detail" style="max-width: 600px; font-size: 1.1rem; line-height: 1.6;">Click the chat icon in the bottom right corner to start a conversation with our team instantly!</p>
            </div>
        </div>
    </div>
</body>
</html>
