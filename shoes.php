<?php
session_start();
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WalkOn - Premium Shoes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-bg: #ffffff;
            --secondary-bg: #282828;
            --accent-pink: #f51167;
            --text-main: #111111;
            --text-light: #ffffff;
            --text-muted: #888888;
            --border-light: #e1e1e1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: var(--primary-bg);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Top Bar */
        .top-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 5%;
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            color: #000;
        }

        .search-container {
            flex: 1;
            max-width: 500px;
            margin: 0 40px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 20px;
            border-radius: 50px;
            border: 1px solid #e1e1e1;
            background: #f8f8f8;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: #b0b0b0;
            background: #ffffff;
        }

        .search-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 16px;
            color: #777;
            cursor: pointer;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .action-link {
            text-decoration: none;
            color: #444;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }

        .action-link:hover {
            color: var(--accent-pink);
        }

        .cart-icon {
            position: relative;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: var(--accent-pink);
            color: white;
            font-size: 10px;
            font-weight: bold;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        /* Nav Bar */
        .main-nav {
            background-color: var(--secondary-bg);
            padding: 0 5%;
        }

        .nav-list {
            list-style: none;
            display: flex;
            align-items: center;
        }

        .nav-list li {
            position: relative;
        }

        .nav-list a {
            display: block;
            padding: 18px 24px;
            color: #cccccc;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .nav-list a:hover,
        .nav-list a.active {
            color: #ffffff;
        }

        .nav-list .new-badge {
            position: absolute;
            top: 5px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent-pink);
            color: white;
            font-size: 8px;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* Hero Section */
        .hero {
            position: relative;
            height: 80vh;
            min-height: 500px;
            background: linear-gradient(135deg, #1f2937, #111827);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hero-visual {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 40px;
        }
        
        .hero-visual img {
            max-width: 90%;
            max-height: 80vh;
            object-fit: contain;
            filter: drop-shadow(0 40px 40px rgba(0,0,0,0.6));
            transform: rotate(-15deg);
            animation: float-shoe 6s ease-in-out infinite;
        }
        
        @keyframes float-shoe {
            0%, 100% { transform: translateY(0) rotate(-15deg); }
            50% { transform: translateY(-20px) rotate(-12deg); }
        }

        /* Dark overlay for better text readability */
        .hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.1) 100%);
        }

        .hero-content {
            position: relative;
            z-index: 10;
            padding: 0 8%;
            max-width: 800px;
        }

        .hero-subtitle {
            font-size: 14px;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
        }

        .hero-title {
            font-size: 4.5rem;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .hero-desc {
            color: #dddddd;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 40px;
            max-width: 500px;
        }

        .hero-actions {
            display: flex;
            gap: 20px;
        }

        .btn {
            padding: 15px 35px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-outline {
            background: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
        }

        .btn-outline:hover {
            background: #ffffff;
            color: #000000;
        }

        .btn-solid {
            background: #ffffff;
            color: #000000;
            border: 2px solid #ffffff;
        }

        .btn-solid:hover {
            background: transparent;
            color: #ffffff;
        }

        /* Pink Badge */
        .hero-badge {
            position: absolute;
            right: 15%;
            top: 50%;
            transform: translateY(-50%);
            background: var(--accent-pink);
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 10;
            text-align: center;
            box-shadow: 0 10px 30px rgba(245, 17, 103, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(245, 17, 103, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(245, 17, 103, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 17, 103, 0); }
        }

        .badge-text-small {
            font-size: 14px;
            font-weight: 600;
        }

        .badge-price {
            font-size: 40px;
            font-weight: 800;
            line-height: 1;
            margin: 5px 0;
        }
        
        .badge-text-bottom {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Controls */
        .slider-controls {
            position: absolute;
            bottom: 30px;
            left: 8%;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 10;
        }

        .control-btn {
            background: none;
            border: 2px solid rgba(255,255,255,0.3);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .control-btn:hover {
            border-color: white;
        }
        
        .slider-dots {
            display: flex;
            gap: 8px;
        }
        
        .dot {
            width: 8px; height: 8px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            cursor: pointer;
        }
        .dot.active {
            background: white;
        }

        .slider-counter {
            position: absolute;
            bottom: 40px;
            right: 8%;
            color: white;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 2px;
            z-index: 10;
        }

        @media (max-width: 991px) {
            .hero-title { font-size: 3rem; }
            .hero-badge { right: 5%; width: 120px; height: 120px; }
            .badge-price { font-size: 30px; }
            .nav-list { display: none; } /* Simplified mobile handling */
        }
        @media (max-width: 768px) {
            .hero-badge { display: none; }
            .search-container { display: none; }
            .hero-actions { flex-direction: column; }
        }
    </style>
</head>
<body>

    <!-- Top Header -->
    <div class="top-header">
        <a href="index.php" class="logo">WALKON</a>
        
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search on walkon ...">
            <button class="search-btn"><i class="fas fa-search"></i></button>
        </div>

        <div class="user-actions">
            <a href="login.php" class="action-link">
                <i class="far fa-user"></i>
                Sign In or Create Account
            </a>
            <a href="cart.php" class="action-link cart-icon">
                <i class="fas fa-shopping-bag"></i>
                Shopping Cart
                <span class="cart-badge">0</span>
            </a>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="main-nav">
        <ul class="nav-list">
            <li><a href="index.php">Home</a></li>
            <li><a href="shop.php?gender=Women">Women</a></li>
            <li><a href="shop.php?gender=Men">Men</a></li>
            <li>
                <a href="#">Accessories</a>
            </li>
            <li>
                <span class="new-badge">New</span>
                <a href="shoes.php" class="active">Shoes</a>
            </li>
            <li><a href="#">Pages</a></li>
            <li><a href="#">Blog</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-subtitle">New Arrivals</span>
            <h1 class="hero-title">Premium<br>Sneakers</h1>
            <p class="hero-desc">
                Step up your game with our latest collection of premium sneakers. Designed for comfort and crafted for style, these shoes are perfect for any occasion. Find your perfect fit today.
            </p>
            <div class="hero-actions">
                <a href="shop.php" class="btn btn-outline">DISCOVER</a>
                <a href="cart.php" class="btn btn-solid">ADD TO CART</a>
            </div>
        </div>

        <!-- Right Side Shoe Image -->
        <div class="hero-visual">
             <img src="assets/hero_shoe.png" alt="Premium Sneakers">
             
             <!-- Pink Feature Badge -->
             <div class="hero-badge">
                 <div class="badge-text-small">from</div>
                 <div class="badge-price">$129</div>
                 <div class="badge-text-bottom">SHOP NOW</div>
             </div>
        </div>

        <!-- Bottom Controls -->
        <div class="slider-controls">
            <button class="control-btn"><i class="fas fa-chevron-left"></i></button>
            <div class="slider-dots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <button class="control-btn"><i class="fas fa-chevron-right"></i></button>
        </div>

        <div class="slider-counter">
            1 / 3
        </div>
    </section>

</body>
</html>
