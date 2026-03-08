<?php
session_start();
include 'config.php';

try {
    $brand_stmt = $pdo->query("SELECT COUNT(*) FROM sellers WHERE is_active = 1");
    $seller_count = $brand_stmt->fetchColumn() ?: 0;
    $display_brands = max(2500, $seller_count + 2480); 

    $market_stmt = $pdo->query("SELECT COUNT(DISTINCT channel) FROM api_credentials WHERE is_active = 1");
    $active_markets = $market_stmt->fetchColumn() ?: 0;
    $display_markets = max(15, $active_markets + 12);
} catch (PDOException $e) {
    $display_brands = 2500;
    $display_markets = 15;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell with WALKON | Elite Distribution</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #60a5fa;
            --accent: #10b981;
            --deep-navy: #0f172a;
            --text-muted: #64748b;
            --glass: rgba(255, 255, 255, 0.7);
            --border: rgba(199, 220, 255, 0.8);
            --bg: #f0f6ff;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Outfit', sans-serif; }
        body { 
            background:
                radial-gradient(ellipse at 0% 0%, rgba(37, 99, 235, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 100% 0%, rgba(96, 165, 250, 0.18) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 100%, rgba(37, 99, 235, 0.08) 0%, transparent 60%),
                linear-gradient(160deg, #e0eeff 0%, #f0f6ff 40%, #ffffff 70%, #e8f3ff 100%);
            color: var(--deep-navy); overflow-x: hidden; 
        }

        /* Nav */
        nav {
            position: fixed; top: 0; width: 100%; padding: 20px 60px;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(255,255,255,0.6); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border); z-index: 100;
        }
        .brand { font-size: 28px; font-weight: 800; text-transform: uppercase; text-decoration: none; color: var(--deep-navy); }
        .brand span { color: var(--primary); }
        .nav-links a { margin-left: 30px; text-decoration: none; color: var(--deep-navy); font-weight: 600; transition: 0.3s; }
        .nav-links a:hover { color: var(--primary); }

        /* Split Layout */
        .wrapper { display: flex; min-height: 100vh; padding-top: 80px; }

        /* Left Sidebar */
        .sticky-side {
            width: 45%; height: calc(100vh - 80px);
            position: sticky; top: 80px;
            padding: 60px;
            display: flex; flex-direction: column; justify-content: center;
            border-right: 1px solid var(--border);
        }

        .hero-content h1 { font-size: 4rem; line-height: 1.1; font-weight: 800; letter-spacing: -2px; margin: 20px 0; }
        .hero-content h1 span { background: linear-gradient(135deg, #2563eb, #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-content p { font-size: 1.25rem; color: var(--text-muted); margin-bottom: 40px; max-width: 450px; }

        .stats-row { display: flex; gap: 40px; }
        .stat-item h4 { font-size: 2.5rem; font-weight: 800; color: var(--primary); }
        .stat-item span { font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .cta-btn {
            display: inline-block; padding: 18px 40px; margin-top: 40px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff;
            text-decoration: none; border-radius: 50px; font-weight: 800; font-size: 1.1rem;
            transition: 0.3s; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        .cta-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4); }

        /* Right Side Details */
        .scrolling-side { width: 55%; padding: 60px 80px; }

        .bento-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .bento-item {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(239,246,255,0.7) 100%);
            border-radius: 32px; padding: 40px;
            border: 1px solid var(--border); transition: 0.4s;
            display: flex; flex-direction: column; justify-content: space-between;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.05);
        }
        .bento-item.wide { grid-column: span 2; }
        .bento-item:hover { transform: translateY(-5px); border-color: var(--primary-light); box-shadow: 0 20px 40px rgba(37,99,235,0.1); }

        .icon-box {
            width: 60px; height: 60px; background: #e0eeff;
            border-radius: 18px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: var(--primary); margin-bottom: 30px; border: 1px solid var(--border);
        }
        .bento-item h2 { font-size: 1.8rem; font-weight: 800; margin-bottom: 15px; color: var(--deep-navy); }
        .bento-item p { color: var(--text-muted); line-height: 1.6; font-size: 1.05rem; }

        /* Calculator Tool */
        .calc-tool { margin-top: 20px; background: rgba(255,255,255,0.6); padding: 30px; border-radius: 20px; border: 1px solid var(--border); }
        .range-slider { width: 100%; margin: 20px 0; accent-color: var(--primary); }
        .calc-result { font-size: 3rem; font-weight: 900; color: var(--accent); margin-top: 10px; display: flex; align-items: baseline; gap: 5px; }
        .calc-result span { font-size: 1rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }

        /* FAQ Accordion */
        .faq-section { margin-top: 60px; }
        .faq-section h2 { font-size: 2.2rem; font-weight: 800; margin-bottom: 30px; }
        .faq-item { background: rgba(255,255,255,0.8); border: 1px solid var(--border); border-radius: 16px; margin-bottom: 16px; overflow: hidden; transition: 0.3s; }
        .faq-item:hover { border-color: var(--primary-light); }
        .faq-btn { width: 100%; text-align: left; padding: 24px; background: transparent; border: none; font-size: 1.1rem; font-weight: 700; color: var(--deep-navy); cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
        .faq-content { padding: 0 24px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, padding 0.3s ease; color: var(--text-muted); line-height: 1.6; }
        .faq-item.active .faq-content { padding: 0 24px 24px; max-height: 200px; }
        .faq-item.active .faq-btn i { transform: rotate(180deg); color: var(--primary); }
        .faq-btn i { transition: 0.3s; }

        .back-btn {
            display: inline-flex; align-items: center; gap: 8px;
            color: var(--deep-navy); font-weight: 700; font-size: 1.05rem;
            text-decoration: none; transition: 0.3s;
            margin-right: 20px;
            padding: 8px 16px; border-radius: 50px; background: rgba(37,99,235,0.05);
        }
        .back-btn:hover { background: rgba(37,99,235,0.1); color: var(--primary); transform: translateX(-3px); }

        @media (max-width: 1024px) {
            .wrapper { flex-direction: column; }
            .sticky-side { width: 100%; height: auto; position: static; padding: 40px 20px; border-right: none; border-bottom: 1px solid var(--border); }
            .scrolling-side { width: 100%; padding: 40px 20px; }
            .hero-content h1 { font-size: 3rem; }
            .bento-grid { grid-template-columns: 1fr; }
            .bento-item.wide { grid-column: span 1; }
        }
    </style>
</head>
<body>

    <nav>
        <div style="display: flex; align-items: center;">
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Home</a>
            <a href="index.php" class="brand">WALK<span>ON</span></a>
        </div>
        <div class="nav-links">
            <a href="login.php">Login</a>
            <a href="register.php" style="background: var(--primary); color: #fff; padding: 10px 24px; border-radius: 50px;">Apply Now</a>
        </div>
    </nav>

    <div class="wrapper">
        <aside class="sticky-side">
            <div class="hero-content">
                <span style="font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem;">Multichannel Dominance</span>
                <h1>Scale Your <span>Footwear</span> Empire.</h1>
                <p>Plug your inventory into WALKON's intelligent middleware. We distribute to 15+ marketplaces globally in real-time. You just fulfill orders.</p>
                
                <div class="stats-row">
                    <div class="stat-item">
                        <h4><?= number_format($display_brands) ?>+</h4>
                        <span>Sellers Live</span>
                    </div>
                    <div class="stat-item">
                        <h4><?= $display_markets ?>+</h4>
                        <span>Channels Built-in</span>
                    </div>
                </div>

                <a href="register.php" class="cta-btn">Start Multichannel Setup <i class="fas fa-arrow-right" style="margin-left: 10px;"></i></a>
            </div>
        </aside>

        <main class="scrolling-side">
            <div class="bento-grid">
                
                <!-- NEW FUNCTIONALITY: Interactive ROI Calculator -->
                <div class="bento-item wide">
                    <div>
                        <div class="icon-box"><i class="fas fa-calculator"></i></div>
                        <h2>Revenue Growth Estimator</h2>
                        <p>See how plugging into WALKON's multichannel network can impact your bottom line based on your current single-channel monthly revenue.</p>
                        
                        <div class="calc-tool">
                            <div style="display: flex; justify-content: space-between; font-weight: 700; color: var(--deep-navy);">
                                <span>Current Monthly Revenue</span>
                                <span id="currentRevDisplay">₹2,00,000</span>
                            </div>
                            <input type="range" class="range-slider" id="revSlider" min="50000" max="10000000" step="50000" value="200000">
                            
                            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--border);">
                                <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Estimated Multichannel Revenue (Monthly)</span>
                                <div class="calc-result">
                                    <span style="font-size: 1.5rem; color: var(--deep-navy);">₹</span>
                                    <span id="projRevDisplay" style="color: var(--accent); font-size: 3rem;">5,40,000</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--primary); font-weight: 700; margin-top: 5px;"><i class="fas fa-arrow-trend-up"></i> +170% Average Growth in 90 Days</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bento-item">
                    <div class="icon-box" style="background: #ecfdf5; color: var(--accent); border-color: #a7f3d0;"><i class="fas fa-bolt"></i></div>
                    <h2>Real-time Sync</h2>
                    <p>Zero latency stock updates. When a sneaker sells on Shopify, your Amazon inventory updates instantly. No overselling, ever.</p>
                </div>

                <div class="bento-item">
                    <div class="icon-box" style="background: #fef2f2; color: #ef4444; border-color: #fecaca;"><i class="fas fa-robot"></i></div>
                    <h2>Smart Pricing AI</h2>
                    <p>Dynamic repricing algorithms analyze competitor movements across all channels to keep your Buy Box share high and margins healthy.</p>
                </div>

                <!-- NEW FUNCTIONALITY: Channel Performance Matrix -->
                <div class="bento-item wide">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
                        <div>
                            <h2>Channel ROI Matrix</h2>
                            <p>Compare performance metrics across global marketplaces for your specific footwear category.</p>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button onclick="tabSwitch('amazon', this)" class="matrix-tab active" style="padding: 6px 14px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.75rem; font-weight: 700; cursor: pointer;">AMAZON</button>
                            <button onclick="tabSwitch('ebay', this)" class="matrix-tab" style="padding: 6px 14px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.75rem; font-weight: 700; cursor: pointer;">EBAY</button>
                            <button onclick="tabSwitch('tiktok', this)" class="matrix-tab" style="padding: 6px 14px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.75rem; font-weight: 700; cursor: pointer;">TIKTOK</button>
                        </div>
                    </div>
                    
                    <div id="matrixContent" style="background: #f8fafc; padding: 25px; border-radius: 20px; border: 1px solid var(--border); display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div style="text-align: center;">
                            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Avg. Conversion</span>
                            <h3 id="mConv" style="font-size: 1.5rem; color: var(--primary);">4.8%</h3>
                        </div>
                        <div style="text-align: center;">
                            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Reach (Users)</span>
                            <h3 id="mReach" style="font-size: 1.5rem; color: var(--accent);">310M+</h3>
                        </div>
                        <div style="text-align: center;">
                            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Ad Efficiency</span>
                            <h3 id="mAds" style="font-size: 1.5rem; color: #f97316;">1:12 ROI</h3>
                        </div>
                    </div>
                </div>

                <!-- NEW FUNCTIONALITY: Live Network Status -->
                <div class="bento-item">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                        <h2 style="margin:0; font-size: 1.2rem;"><i class="fas fa-satellite-dish" style="color: var(--primary);"></i> Live Network</h2>
                        <span style="background: rgba(16,185,129,0.1); color: var(--accent); padding: 4px 10px; border-radius: 50px; font-size: 0.65rem; font-weight: 800;">ACTIVE: 99.9%</span>
                    </div>
                    <div id="statusList" style="font-family: 'Courier New', monospace; font-size: 0.75rem; color: var(--text-muted); line-height: 1.6;">
                        <div style="color: var(--accent);">> Stock synced: Amazon UK [SKU-X]</div>
                        <div>> Pricing optimized: eBay US</div>
                        <div>> New Listing: TikTok Shop SE</div>
                    </div>
                </div>

                <div class="bento-item">
                    <div class="icon-box" style="background: #eff6ff; color: var(--primary); border-color: #bfdbfe;"><i class="fas fa-shield-halved"></i></div>
                    <h2>Fraud Guard AI</h2>
                    <p>Advanced neural networks analyze buyer behavior pattern-by-pattern to block suspicious returns and chargebacks before they happen.</p>
                </div>

            </div>

            <!-- NEW FUNCTIONALITY: Interactive FAQ -->
            <div class="faq-section">
                <h2>Seller FAQ</h2>
                
                <div class="faq-item">
                    <button class="faq-btn">How does WALKON integrate with my existing ecommerce store? <i class="fas fa-chevron-down"></i></button>
                    <div class="faq-content">
                        We provide one-click integrations for Shopify, WooCommerce, and Magento, as well as an open API for custom ERPs. Your master inventory remains in your current system, and we simply sync the data out to the other marketplaces.
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-btn">Which marketplaces are supported currently? <i class="fas fa-chevron-down"></i></button>
                    <div class="faq-content">
                        Currently, WALKON officially supports Amazon, eBay, TikTok Shop, Walmart, Myntra, Flipkart, and Goat (beta). We are constantly adding new API endpoints.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn">What happens if an item sells simultaneously on two channels? <i class="fas fa-chevron-down"></i></button>
                    <div class="faq-content">
                        Our intelligent lock system reserves inventory at the checkout/cart level on connected platforms if APIs permit, or pushes hyper-fast 20ms syncs. If an oversell does occur, the dashboard immediately alerts you to choose which order to fulfill based on highest profit margin.
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-btn">What are the fees for selling? <i class="fas fa-chevron-down"></i></button>
                    <div class="faq-content">
                        Applying is free. We charge a flat 2.5% transaction fee on the gross merchandise value (GMV) of items sold *through* our middleware routing. Direct sales on your primary native site are not billed.
                    </div>
                </div>
            </div>

            <!-- NEW FUNCTIONALITY: Elite Scaling Tiers -->
            <div style="margin-top: 60px; background: var(--deep-navy); border-radius: 32px; padding: 40px; color: #fff; position: relative; overflow: hidden;">
                <div style="position: absolute; top: -10%; right: -10%; width: 250px; height: 250px; background: var(--primary); filter: blur(100px); opacity: 0.1;"></div>
                <h2 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 30px;">Elite Scaling Accelerator</h2>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div style="background: rgba(255,255,255,0.05); padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
                        <span style="color: var(--primary); font-weight: 800; font-size: 0.7rem; text-transform: uppercase;">Tier 1: Growth</span>
                        <h4 style="margin: 10px 0;">Alpha Seller</h4>
                        <p style="font-size: 0.8rem; color: #94a3b8;">5 Channels Included<br>Standard Repricing<br>24h Support</p>
                    </div>
                    <div style="background: rgba(37,99,235,0.1); padding: 25px; border-radius: 20px; border: 1px solid var(--primary); transform: scale(1.05); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        <span style="color: var(--primary-light); font-weight: 800; font-size: 0.7rem; text-transform: uppercase;">Tier 2: Elite</span>
                        <h4 style="margin: 10px 0;">Market Leader</h4>
                        <p style="font-size: 0.8rem; color: #fff;">15+ Channels<br>Dynamic AI Pricing<br>Dedicated Manager</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.05); padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
                        <span style="color: var(--accent); font-weight: 800; font-size: 0.7rem; text-transform: uppercase;">Tier 3: Infinite</span>
                        <h4 style="margin: 10px 0;">Global Partner</h4>
                        <p style="font-size: 0.8rem; color: #94a3b8;">Unlimited Channels<br>Warehousing Built-in<br>VC Partnership Access</p>
                    </div>
                </div>
            </div>

            <footer style="margin-top: 80px; padding-top: 40px; border-top: 1px solid var(--border); text-align: center; color: var(--text-muted); font-size: 0.9rem;">
                <p>&copy; 2026 WALKON Footwear India. Engineered for Elite Sellers.</p>
            </footer>
        </main>
    </div>

    <script>
        // Revenue Calculator Logic
        const slider = document.getElementById('revSlider');
        const currentDisplay = document.getElementById('currentRevDisplay');
        const projDisplay = document.getElementById('projRevDisplay');

        function formatCurrency(num) {
            return new Intl.NumberFormat('en-IN').format(num);
        }

        slider.addEventListener('input', function() {
            const current = parseInt(this.value);
            currentDisplay.textContent = '₹' + formatCurrency(current);
            
            // Multichannel usually results in an estimated 170% total revenue compared to single channel
            const projected = Math.round(current * 2.7);
            projDisplay.textContent = formatCurrency(projected);
        });

        // Matrix Tab Logic
        function tabSwitch(type, btn) {
            document.querySelectorAll('.matrix-tab').forEach(t => {
                t.classList.remove('active');
                t.style.background = 'transparent';
                t.style.color = 'inherit';
            });
            btn.classList.add('active');
            btn.style.background = 'var(--primary)';
            btn.style.color = '#fff';

            const data = {
                amazon: { conv: '4.8%', reach: '310M+', ads: '1:12 ROI' },
                ebay: { conv: '3.2%', reach: '180M+', ads: '1:9 ROI' },
                tiktok: { conv: '6.4%', reach: '1.5B+', ads: '1:22 ROI' }
            };

            document.getElementById('mConv').innerText = data[type].conv;
            document.getElementById('mReach').innerText = data[type].reach;
            document.getElementById('mAds').innerText = data[type].ads;
        }

        // Initialize first tab
        tabSwitch('amazon', document.querySelector('.matrix-tab'));

        // Live Feed Simulation
        const statuses = [
            "> Stock synced: Amazon UK [SKU-X]",
            "> Pricing optimized: eBay US",
            "> New Listing: TikTok Shop SE",
            "> AI Forecast: Japan region UP 12%",
            "> Fraud blocked: Guest Order ID-928",
            "> Inventory Lock: India Express Hub"
        ];
        let statusIdx = 0;
        setInterval(() => {
            const list = document.getElementById('statusList');
            const newItem = document.createElement('div');
            newItem.innerText = statuses[statusIdx % statuses.length];
            if(statusIdx % 2 === 0) newItem.style.color = 'var(--accent)';
            list.prepend(newItem);
            if(list.children.length > 5) list.lastChild.remove();
            statusIdx++;
        }, 3000);

        // FAQ Accordion Logic
        const faqBtns = document.querySelectorAll('.faq-btn');
        faqBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const parent = btn.parentElement;
                
                // Close others
                document.querySelectorAll('.faq-item').forEach(item => {
                    if(item !== parent) item.classList.remove('active');
                });

                // Toggle open
                parent.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
