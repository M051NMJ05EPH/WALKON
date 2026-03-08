<?php
session_start();
include 'config.php';

// Fetch all 101 products from database
try {
    $stmt = $pdo->query("SELECT pb.id, pb.name, pp.price, b.name as brand_name,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM product_base pb
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_specs spec ON pb.id = spec.product_id
        LEFT JOIN brands b ON spec.brand_id = b.id
        WHERE pb.status = 'published'
        ORDER BY pb.id ASC");
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $all_products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Virtual Showroom | WalkOn Next-Gen</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --secondary: #2563eb;
            --bg: #05070a;
            --card: #0a0e17;
            --text: #ffffff;
            --muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
        }

        .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; }

        /* Background Effects */
        .glow-sphere {
            position: fixed; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%);
            border-radius: 50%; z-index: -1; pointer-events: none;
        }
        .sphere-1 { top: -200px; right: -200px; }
        .sphere-2 { bottom: -200px; left: -200px; background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%); }

        /* Header */
        header { padding: 40px 0; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo span { font-size: 1.5rem; font-weight: 800; color: #fff; letter-spacing: -1px; }

        .btn {
            padding: 12px 24px; border-radius: 50px; font-weight: 700; text-decoration: none;
            transition: 0.3s; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
            border: none;
        }
        .btn-primary { background: var(--primary); color: #000; box-shadow: 0 4px 15px rgba(16,185,129,0.3); }
        .btn-outline { border: 1px solid rgba(255,255,255,0.1); color: #fff; background: transparent; }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(16,185,129,0.4); }

        /* Main Grid */
        .dashboard-grid {
            display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 50px;
        }

        .main-card {
            background: var(--card); border-radius: 30px; border: 1px solid rgba(255,255,255,0.05);
            padding: 40px; position: relative; overflow: hidden;
        }

        /* 3D View Mock */
        .v3d-container {
            height: 400px; background: radial-gradient(circle at 50% 50%, #1e293b 0%, #0a0e17 100%);
            border-radius: 20px; display: flex; align-items: center; justify-content: center;
            margin-top: 30px; border: 1px solid rgba(255,255,255,0.05); cursor: move;
            position: relative;
        }
        .v3d-shoe { max-width: 300px; filter: drop-shadow(0 30px 50px rgba(0,0,0,0.8)); transform: rotate(-5deg); transition: 0.1s linear; }

        .ai-label {
            position: absolute; top: 20px; left: 20px;
            background: rgba(16,185,129,0.1); color: var(--primary);
            padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 800;
            border: 1px solid var(--primary); text-transform: uppercase;
        }

        /* Stats Row */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
        .stat-card {
            background: rgba(255,255,255,0.02); padding: 20px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);
        }
        .stat-label { font-size: 0.7rem; color: var(--muted); text-transform: uppercase; font-weight: 800; margin-bottom: 5px; }
        .stat-value { font-size: 1.2rem; font-weight: 700; color: #fff; }

        /* Sidebar Widgets */
        .sidebar { display: flex; flex-direction: column; gap: 30px; }
        .widget {
            background: var(--card); border-radius: 24px; padding: 25px; border: 1px solid rgba(255,255,255,0.05);
        }
        .widget h3 { font-size: 1rem; margin-bottom: 20px; color: var(--primary-light); display: flex; align-items: center; gap: 10px; }

        .timeline { list-style: none; }
        .timeline-item { position: relative; padding-left: 20px; padding-bottom: 20px; border-left: 1px solid rgba(255,255,255,0.1); }
        .timeline-item::before { content: ''; position: absolute; left: -5px; top: 0; width: 9px; height: 9px; background: var(--primary); border-radius: 50%; }
        .timeline-item span { font-size: 0.7rem; color: var(--muted); }
        .timeline-item p { font-size: 0.85rem; color: #fff; }

        .action-list { display: flex; flex-direction: column; gap: 12px; }
        .action-btn {
            background: rgba(255,255,255,0.03); color: #fff; text-decoration: none;
            padding: 15px; border-radius: 15px; display: flex; align-items: center; justify-content: space-between;
            font-size: 0.9rem; font-weight: 600; border: 1px solid transparent; transition: 0.3s;
        }
        .action-btn:hover { border-color: var(--primary); background: rgba(16,185,129,0.05); }

        /* AI Prediction Bar */
        .predict-bar { height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow: hidden; margin-top: 10px; }
        .predict-fill { height: 100%; background: linear-gradient(90deg, #10b981, #2563eb); width: 0%; transition: 1s ease-out; }


        footer { padding: 40px 0; text-align: center; border-top: 1px solid rgba(255,255,255,0.05); margin-top: 80px; color: var(--muted); font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="glow-sphere sphere-1"></div>
    <div class="glow-sphere sphere-2"></div>

    <div class="container">
        <header>
            <a href="shop.php" class="logo">
                <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height: 40px; width: auto;">
                <span>WALK<span style="color: var(--primary);">ON</span> AI</span>
            </a>
            <div style="display: flex; gap: 15px;">
                <a href="shop.php" class="btn btn-outline"><i class="fas fa-shopping-bag"></i> Back to Shop</a>
                <button onclick="regenerateAI()" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Regenerate Designs</button>
            </div>
        </header>

        <div style="margin-top: 40px;">
            <h1 style="font-size: 3rem; font-weight: 800; letter-spacing: -2px;">AI Virtual Showroom</h1>
            <p style="color: var(--muted); font-size: 1.1rem; margin-top: 10px;">Experience global footwear retail powered by neural design modeling.</p>
        </div>

        <div class="dashboard-grid">
            <div class="main-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 id="productTitle" style="font-size: 1.5rem;">AI-Generated Concept V.01</h2>
                        <span id="productBrand" style="color: var(--muted); font-size: 0.8rem;">Style ID: AI-SFX-2026-X1</span>
                    </div>
                    <div style="text-align: right;">
                        <div id="productPrice" style="font-size: 1.8rem; font-weight: 800; color: var(--primary);">₹18,499</div>
                        <span style="color: var(--muted); font-size: 0.7rem; text-transform: uppercase;">Predicted Market Value</span>
                    </div>
                </div>

                <div class="v3d-container" id="v3dViewport">
                    <div class="ai-label"><i class="fas fa-microchip"></i> Real-time Neural Rendering</div>
                    <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80" alt="Shoe" class="v3d-shoe" id="v3dImg">
                    <div style="position: absolute; bottom: 20px; color: rgba(255,255,255,0.4); font-size: 0.7rem; display: flex; gap: 15px; align-items: center; width: 95%; justify-content: center;">
                        <button onclick="toggleAutoRotate()" id="rotateBtn" style="background: rgba(16,185,129,0.1); color: var(--primary); border: 1px solid var(--primary); padding: 8px 16px; border-radius: 8px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s;">
                            <i class="fas fa-sync-alt" id="rotateIcon"></i> Auto Rotate
                        </button>
                        <button onclick="toggleXRay()" id="xrayBtn" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 8px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s;">
                            <i class="fas fa-microscope"></i> X-Ray Mode
                        </button>
                        <select onchange="changeEnv(this.value)" style="background: rgba(0,0,0,0.5); color: #fff; border: 1px solid rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; outline: none;">
                            <option value="lab">🔬 Lab Studio</option>
                            <option value="urban">🌆 Urban Night</option>
                            <option value="midnight">🌑 Deep Abyss</option>
                        </select>
                    </div>

                    <!-- X-Ray Overlays -->
                    <div id="xrayOverlays" style="position: absolute; inset: 0; pointer-events: none; display: none;">
                        <div style="position: absolute; top: 40%; left: 30%; color: var(--primary); font-size: 0.6rem; border-left: 1px dashed var(--primary); padding-left: 10px;">
                            <span style="font-weight: 800;">[ CARBON-PLATE ]</span><br>Max Energy Return
                        </div>
                        <div style="position: absolute; bottom: 30%; right: 25%; color: var(--secondary); font-size: 0.6rem; border-right: 1px dashed var(--secondary); padding-right: 10px; text-align: right;">
                            <span style="font-weight: 800;">[ AI-GEL CORE ]</span><br>Impact Absorption
                        </div>
                    </div>

                </div>

                <div class="stats-row" style="grid-template-columns: repeat(4, 1fr);">
                    <div class="stat-card">
                        <div class="stat-label">Style Score</div>
                        <div class="stat-value">98.4 / 100</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Neural Match</div>
                        <div class="stat-value" id="radarMatch">92.1%</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Durability</div>
                        <div class="stat-value">Rank S</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Eco Score</div>
                        <div class="stat-value">A++</div>
                    </div>
                </div>

                <div style="margin-top: 40px;">
                    <h3 style="margin-bottom: 20px; font-size: 1rem;">AI Design Decisions</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="padding: 15px; background: rgba(16,185,129,0.05); border-radius: 15px; border-left: 4px solid var(--primary);">
                            <h4 style="font-size: 0.85rem; color: var(--primary); margin-bottom: 5px;">Adaptive Sole Tech</h4>
                            <p style="font-size: 0.8rem; color: rgba(255,255,255,0.7);">AI predicted a 12% increase in heel pressure for your gait pattern. Design adjusted.</p>
                        </div>
                        <div style="padding: 15px; background: rgba(37, 99, 235, 0.05); border-radius: 15px; border-left: 4px solid var(--secondary);">
                            <h4 style="font-size: 0.85rem; color: var(--secondary); margin-bottom: 5px;">Thermal Optimization</h4>
                            <p style="font-size: 0.8rem; color: rgba(255,255,255,0.7);">Knit density increased in toe box to maintain temperature based on local climate data.</p>
                        </div>
                    </div>
                </div>

                <!-- Parametric Controls -->
                <div style="margin-top: 40px; background: rgba(255,255,255,0.02); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
                    <h3 style="margin-bottom: 20px; font-size: 1rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-sliders-h" style="color: var(--primary);"></i> Parametric Customization
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div>
                            <span style="font-size: 0.7rem; color: var(--muted); text-transform: uppercase;">Material Density</span>
                            <input type="range" min="1" max="100" value="85" style="width: 100%; accent-color: var(--primary); margin-top: 10px;">
                        </div>
                        <div>
                            <span style="font-size: 0.7rem; color: var(--muted); text-transform: uppercase;">Sole Rigidity</span>
                            <input type="range" min="1" max="100" value="42" style="width: 100%; accent-color: var(--secondary); margin-top: 10px;">
                        </div>
                        <div>
                            <span style="font-size: 0.7rem; color: var(--muted); text-transform: uppercase;">Aerodynamic Mesh</span>
                            <input type="range" min="1" max="100" value="91" style="width: 100%; accent-color: #f97316; margin-top: 10px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-brain"></i> AI Fit Lab</h3>
                    <p style="font-size: 0.85rem; color: var(--muted); margin-bottom: 20px;">Upload a photo of your foot to generate a precise 3D digital twin.</p>
                    <button class="btn btn-outline" onclick="start3DScan(this)" style="width: 100%; justify-content: center; font-size: 0.85rem;">
                        <i class="fas fa-upload"></i> Initialize 3D Scan
                    </button>
                    <div id="scanStatus" style="margin-top: 15px; padding: 10px; background: rgba(249, 115, 22, 0.1); border-radius: 10px; color: #f97316; font-size: 0.7rem; font-weight: 700;">
                        <i class="fas fa-info-circle"></i> Accuracy: +/- 0.2mm
                    </div>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-satellite-dish"></i> Neural Collection</h3>
                    <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;" class="neural-feed">
                        <?php foreach($all_products as $product): ?>
                            <div class="timeline-item" style="cursor: pointer; transition: 0.3s;" onclick="selectProduct(<?= htmlspecialchars(json_encode($product)) ?>)">
                                <span>ID: BK-AI-<?= $product['id'] ?></span>
                                <p style="font-weight: 700;"><?= htmlspecialchars($product['name']) ?></p>
                                <p style="font-size: 0.7rem; color: var(--primary);">₹<?= number_format($product['price']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-chart-line"></i> Price Prediction</h3>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem;">
                        <span>Current Trend</span>
                        <span style="color: var(--primary);">+4.2% Growth</span>
                    </div>
                    <div class="predict-bar"><div class="predict-fill" id="pBar"></div></div>
                    <p style="font-size: 0.75rem; color: var(--muted); margin-top: 10px;">Market saturation predicted in 14 days. Recommend purchase within 72 hours.</p>
                </div>

                <div class="widget" style="background: linear-gradient(135deg, #064e3b, #0a0e17); border-color: var(--primary);">
                    <h3><i class="fas fa-shopping-cart"></i> Investment</h3>
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 1.5rem; font-weight: 800;">₹18,499</div>
                        <span style="font-size: 0.7rem; color: var(--primary);">Optimized Price: No hidden fees</span>
                    </div>
                    <button class="btn-primary" onclick="executeSmartBuy(this)" style="width: 100%; height: 50px; font-size: 1rem;">
                        Execute Smart Buy <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            &copy; 2026 WalkOn Intelligence Systems. Elevating Footwear through Neural Innovation.
        </div>
    </footer>

    <?php include 'includes/chatbot.php'; ?>

    <script>
        let isAutoRotating = false;
        let rotateInterval;

        function toggleAutoRotate() {
            const btn = document.getElementById('rotateBtn');
            const icon = document.getElementById('rotateIcon');
            isAutoRotating = !isAutoRotating;

            if (isAutoRotating) {
                btn.style.background = 'var(--primary)';
                btn.style.color = '#000';
                icon.classList.add('fa-spin');
                startRotationLoop();
            } else {
                btn.style.background = 'rgba(16,185,129,0.1)';
                btn.style.color = 'var(--primary)';
                icon.classList.remove('fa-spin');
                stopRotationLoop();
            }
        }

        function startRotationLoop() {
            if (rotateInterval) clearInterval(rotateInterval);
            rotateInterval = setInterval(() => {
                if (!isDragging) {
                    currentRotation += 1;
                    shoe.style.transform = `rotate(${currentRotation}deg) scale(1.05)`;
                }
            }, 30);
        }

        function stopRotationLoop() {
            clearInterval(rotateInterval);
        }

        function toggleXRay() {
            const overlays = document.getElementById('xrayOverlays');
            const btn = document.getElementById('xrayBtn');
            if (overlays.style.display === 'none') {
                overlays.style.display = 'block';
                shoe.style.filter = 'drop-shadow(0 0 30px var(--primary)) opacity(0.6) grayscale(1) brightness(1.5)';
                btn.style.background = 'var(--primary)';
                btn.style.color = '#000';
            } else {
                overlays.style.display = 'none';
                shoe.style.filter = 'drop-shadow(0 30px 50px rgba(0,0,0,0.8))';
                btn.style.background = 'rgba(255,255,255,0.1)';
                btn.style.color = '#fff';
            }
        }

        function changeEnv(mode) {
            const v = document.getElementById('v3dViewport');
            if (mode === 'lab') v.style.background = 'radial-gradient(circle at 50% 50%, #1e293b 0%, #0a0e17 100%)';
            if (mode === 'urban') v.style.background = 'radial-gradient(circle at 50% 50%, #1e1b4b 0%, #020617 100%)';
            if (mode === 'midnight') v.style.background = 'radial-gradient(circle at 50% 50%, #171717 0%, #000000 100%)';
            
            // Pulse to signify environment change
            v.style.opacity = '0.5';
            setTimeout(() => v.style.opacity = '1', 200);
        }

        function start3DScan(btn) {
            const originalText = btn.innerHTML;
            const status = document.getElementById('scanStatus');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Accessing Mobile Lidar...';
            
            status.style.background = 'rgba(37, 99, 235, 0.1)';
            status.style.color = 'var(--secondary)';
            status.innerHTML = '<i class="fas fa-sync fa-spin"></i> Processing depth map...';
            
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-check"></i> 3D Scan Complete';
                status.style.background = 'rgba(16, 185, 129, 0.1)';
                status.style.color = 'var(--primary)';
                status.innerHTML = '<i class="fas fa-user-check"></i> Digital Twin Generated: UK 9.5 (Match 99%)';
                
                // Trigger a small effect on the shoe to signify it's "fitted"
                shoe.style.filter = 'drop-shadow(0 0 30px var(--primary)) brightness(1.2)';
                setTimeout(() => {
                    shoe.style.filter = 'drop-shadow(0 30px 50px rgba(0,0,0,0.8))';
                }, 1000);
            }, 2500);
        }

        function executeSmartBuy(btn) {
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing Neural Transaction...';
            btn.style.background = '#eab308';
            
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-check"></i> Investment Confirmed';
                btn.style.background = 'var(--primary)';
                
                // Show a success toast or alert (using the chatbot's alert system if available, else simple alert)
                if (window.showToast) {
                    showToast('Transaction Successful', 'Your AI-Generated Concept has been added to your vault.');
                } else {
                    alert('Order Placed! Your custom concept is now in production.');
                }
            }, 2000);
        }

        // Parametric Slider Interactions
        document.querySelectorAll('input[type="range"]').forEach(slider => {
            slider.addEventListener('input', () => {
                const val = slider.value;
                // Distort or change the shoe slightly to simulate customization
                shoe.style.transform = `rotate(${currentRotation}deg) scale(${1 + (val/500)})`;
                viewport.style.background = `radial-gradient(circle at 50% 50%, rgba(37, 99, 235, ${val/100}) 0%, #0a0e17 100%)`;
                
                // Update Neural Match
                const matchVal = (90 + (val/20)).toFixed(1);
                document.getElementById('radarMatch').innerText = `${matchVal}%`;
            });
        });

        // Simulated 3D Interaction
        const viewport = document.getElementById('v3dViewport');
        const shoe = document.getElementById('v3dImg');
        let isDragging = false;
        let startX = 0;
        let currentRotation = -5;

        viewport.onmousedown = (e) => { 
            isDragging = true; 
            startX = e.clientX; 
            viewport.style.boxShadow = 'inset 0 0 50px rgba(16, 185, 129, 0.2)';
        };
        window.onmouseup = () => { 
            isDragging = false; 
            viewport.style.boxShadow = '';
        };
        window.onmousemove = (e) => {
            if(!isDragging) return;
            const delta = e.clientX - startX;
            currentRotation += delta * 0.5;
            shoe.style.transform = `rotate(${currentRotation}deg) scale(1.05)`;
            startX = e.clientX;
        };

        // Simulated AI Regenerate
        function regenerateAI() {
            shoe.style.opacity = '0.3';
            shoe.style.filter = 'blur(10px) brightness(1.5)';
            setTimeout(() => {
                shoe.src = 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&q=80';
                shoe.style.opacity = '1';
                shoe.style.filter = 'drop-shadow(0 30px 50px rgba(0,0,0,0.8))';
                document.getElementById('pBar').style.width = '84%';
            }, 800);
        }

        // Select Product from Neural Collection
        function selectProduct(p) {
            const title = document.getElementById('productTitle');
            const brand = document.getElementById('productBrand');
            const price = document.getElementById('productPrice');
            const img = document.getElementById('v3dImg');
            
            // Effect
            img.style.opacity = '0.3';
            img.style.filter = 'blur(15px) brightness(1.5)';
            
            setTimeout(() => {
                title.innerText = p.name;
                brand.innerText = (p.brand_name || 'Generic') + ' | BK-AI-' + p.id;
                price.innerText = '₹' + parseInt(p.price).toLocaleString();
                img.src = p.primary_image || 'https://via.placeholder.com/800x600?text=No+Image';
                
                img.style.opacity = '1';
                img.style.filter = 'drop-shadow(0 30px 50px rgba(0,0,0,0.8))';
                
                // Randomize some stats for flavor
                document.getElementById('radarMatch').innerText = (85 + Math.random() * 10).toFixed(1) + '%';
                document.getElementById('pBar').style.width = (60 + Math.random() * 30) + '%';
            }, 400);
        }

        // Trigger on load
        setTimeout(() => {
            document.getElementById('pBar').style.width = '67%';
        }, 500);

        // Add holographic interaction
        viewport.onmouseenter = () => {
            viewport.style.background = 'radial-gradient(circle at 50% 50%, #1e40af 0%, #0a0e17 100%)';
        };
        viewport.onmouseleave = () => {
             viewport.style.background = 'radial-gradient(circle at 50% 50%, #1e293b 0%, #0a0e17 100%)';
        };
    </script>
</body>
</html>
