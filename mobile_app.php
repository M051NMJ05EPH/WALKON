<?php
session_start();
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WalkOn Mobile | The Command Center of Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --bg: #ffffff;
            --sky-light: #f0f9ff;
            --sky-mid: #e0f2fe;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { background: var(--bg); color: var(--text-main); line-height: 1.6; overflow-x: hidden; }

        .navbar {
            padding: 20px 40px;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            position: fixed; top: 0; width: 100%; z-index: 1000;
            border-bottom: 1px solid var(--border);
        }
        .back-btn {
            color: var(--text-muted); text-decoration: none; font-weight: 600;
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        .back-btn:hover { color: var(--primary); transform: translateX(-5px); }

        .hero {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 120px 40px;
            background: radial-gradient(circle at 10% 20%, var(--sky-mid) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, var(--sky-light) 0%, transparent 40%);
        }

        .container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }

        .content h1 {
            font-family: 'Playfair Display', serif; font-size: 4.5rem; line-height: 1.1; margin-bottom: 2rem;
            background: linear-gradient(to bottom, var(--text-main), var(--primary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .feature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 3rem; }
        .feature-item {
            background: var(--white); padding: 20px; border-radius: 16px; border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .feature-item i { color: var(--primary); margin-bottom: 12px; font-size: 1.2rem; }
        .feature-item h3 { font-size: 1rem; margin-bottom: 5px; }
        .feature-item p { font-size: 0.85rem; color: var(--text-muted); }

        .app-status {
            padding: 30px; background: rgba(16, 185, 129, 0.05); border: 1px dashed var(--primary);
            border-radius: 20px; text-align: center;
        }
        .app-status h2 { color: var(--primary); margin-bottom: 1rem; }
        
        .mockup-side { position: relative; }
        .phone-frame {
            width: 320px; height: 650px; background: #000; border: 12px solid #1e293b;
            border-radius: 40px; margin: 0 auto; position: relative; overflow: hidden;
            box-shadow: 0 50px 100px -20px rgba(0,0,0,0.5);
        }
        .phone-screen {
            height: 100%; width: 100%; background: #020617;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 20px; text-align: center;
        }
        .phone-content i { font-size: 4rem; color: var(--primary); margin-bottom: 2rem; animation: pulse 2s infinite; }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 0.8; }
        }

        .glow {
            position: absolute; top: 50%; left: 50%; width: 100%; height: 100%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
            transform: translate(-50%, -50%); z-index: -1; filter: blur(50px);
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="start_selling.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Seller Hub</a>
        <div style="font-weight: 800; font-size: 1.2rem;">WALK<span style="color: var(--primary);">ON</span> Mobile</div>
    </nav>

    <section class="hero">
        <div class="container">
            <div class="content">
                <span style="color: var(--primary); font-weight: 700; letter-spacing: 3px; text-transform: uppercase; font-size: 0.8rem; display: block; margin-bottom: 1rem;">Beta Access Available</span>
                <h1>Universal Control, <br> Built for Mobile.</h1>
                <p style="font-size: 1.2rem; color: var(--text-muted); margin-bottom: 3rem;">We are precision-tuning the WalkOn app to provide the world's most responsive multi-channel management experience.</p>
                
                <div class="feature-grid">
                    <div class="feature-item">
                        <i class="fas fa-bolt"></i>
                        <h3>Instant Push</h3>
                        <p>Receive order alerts from Amazon & Shopify in <100ms.</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Live Analytics</h3>
                        <p>Track your global performance with interactive charts.</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-boxes"></i>
                        <h3>Stock Control</h3>
                        <p>Update inventory across all channels with a single tap.</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-comments"></i>
                        <h3>Unified Chat</h3>
                        <p>Reply to customers from all platforms in one inbox.</p>
                    </div>
                </div>

                <div class="app-status">
                    <h2>Coming Q2 2026</h2>
                    <p>Register as a seller today to be first in line for the early access program.</p>
                    <a href="register.php?intended_role=seller" style="display:inline-block; margin-top:20px; color: var(--primary); text-decoration:none; font-weight:700; border-bottom: 2px solid var(--primary);">Become a Founding Seller &rarr;</a>
                </div>
            </div>

            <div class="mockup-side">
                <div class="glow"></div>
                <div class="phone-frame">
                    <div class="phone-screen">
                        <div class="phone-content">
                            <i class="fas fa-microchip"></i>
                            <h3 style="margin-bottom: 10px;">WALKON<span>OS</span></h3>
                            <p style="font-size: 0.8rem; opacity: 0.6;">Optimizing Core Engine... 89%</p>
                            <div style="width: 150px; height: 4px; background: #1e293b; border-radius: 2px; margin: 20px auto; overflow: hidden;">
                                <div style="width: 89%; height: 100%; background: var(--primary);"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/chatbot.php'; ?>
</body>
</html>
