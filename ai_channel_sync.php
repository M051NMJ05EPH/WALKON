<?php
session_start();
include 'config.php';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT pb.name, pb.description, ps.sku, pp.price, b.name as brand_name,
            (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as image_url
            FROM product_base pb
            LEFT JOIN product_skus ps ON pb.id = ps.product_id
            LEFT JOIN product_prices pp ON pb.id = pp.product_id
            LEFT JOIN product_specs spec ON pb.id = spec.product_id
            LEFT JOIN brands b ON spec.brand_id = b.id
            WHERE pb.id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $product = null;
    }
} else {
    $product = null;
}

if (!$product) {
    // Default mock data if missing
    $product = [
        'name' => 'Premium Urban Sneaker',
        'description' => 'A great shoe for daily wear.',
        'sku' => 'WALK-001-BLK',
        'price' => 12900,
        'brand_name' => 'WalkOn',
        'image_url' => 'assets/hero_shoe.png'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Multi-Channel Sync | WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --bg-dark: #0f172a;
            --bg-panel: #1e293b;
            --border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit',sans-serif; }
        
        body { 
            background-color: var(--bg-dark); 
            color: var(--text-main); 
            min-height: 100vh;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(16, 185, 129, 0.05), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(56, 189, 248, 0.05), transparent 25%);
        }

        .header {
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0; z-index: 100;
        }

        .logo { font-size: 24px; font-weight: 800; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .logo i { color: var(--primary); }

        .back-btn {
            color: var(--text-muted); text-decoration: none; font-weight: 600;
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        .back-btn:hover { color: #fff; }

        .container {
            max-width: 1200px; margin: 40px auto; padding: 0 20px;
            display: grid; grid-template-columns: 350px 1fr; gap: 30px;
        }

        /* Product Panel */
        .product-panel {
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 30px;
            height: fit-content;
        }

        .product-image {
            width: 100%; height: 250px; object-fit: contain;
            background: #111827; border-radius: 12px; margin-bottom: 20px;
            padding: 20px; border: 1px solid rgba(255,255,255,0.05);
        }

        .product-brand { color: var(--primary); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
        .product-title { font-size: 1.4rem; font-weight: 800; margin-bottom: 10px; line-height: 1.3; }
        .product-meta { display: flex; justify-content: space-between; font-size: 0.9rem; color: var(--text-muted); padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }

        /* AI Copilot Panel */
        .ai-panel {
            display: flex; flex-direction: column; gap: 20px;
        }

        .ai-card {
            background: linear-gradient(145deg, #1e293b, #151e2e);
            border: 1px solid var(--border);
            border-radius: 20px; padding: 30px;
            position: relative; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .ai-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
            background: var(--primary);
        }

        .ai-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .ai-title { font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .ai-title i { color: var(--primary); animation: pulse 2s infinite; }

        @keyframes pulse {
            0% { text-shadow: 0 0 0 var(--primary-glow); }
            50% { text-shadow: 0 0 15px var(--primary-glow); }
            100% { text-shadow: 0 0 0 var(--primary-glow); }
        }

        .ai-badge {
            background: rgba(16, 185, 129, 0.1); color: var(--primary);
            padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* Generated Content View */
        .generated-box {
            background: #0f172a; border-radius: 12px; padding: 20px; border: 1px solid #1e293b;
            margin-bottom: 15px;
        }
        
        .gen-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 8px; display: block; }
        .gen-content { font-size: 0.95rem; line-height: 1.6; color: #e2e8f0; }

        /* Actions */
        .btn-action {
            background: #334155; color: white; border: none; padding: 10px 20px;
            border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s;
            display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem;
        }
        .btn-action:hover { background: #475569; }

        .btn-primary {
            background: var(--primary); color: #000; border: none; padding: 12px 24px;
            border-radius: 8px; font-weight: 800; cursor: pointer; transition: 0.2s;
            display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;
            font-size: 1rem;
        }
        .btn-primary:hover { background: #34d399; transform: translateY(-2px); box-shadow: 0 5px 15px var(--primary-glow); }

        .status-list { list-style: none; margin-top: 20px; }
        .status-list li {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 0.95rem;
        }
        .status-list li:last-child { border-bottom: none; }
        .channel-name { display: flex; align-items: center; gap: 10px; font-weight: 600; }
        
        .toggle-switch {
            position: relative; width: 44px; height: 24px;
            background: #334155; border-radius: 50px; cursor: pointer; transition: 0.3s;
        }
        .toggle-switch.active { background: var(--primary); }
        .toggle-switch::after {
            content: ''; position: absolute; top: 2px; left: 2px;
            width: 20px; height: 20px; background: white; border-radius: 50%;
            transition: 0.3s;
        }
        .toggle-switch.active::after { left: 22px; }

        .loading-shimmer {
            background: linear-gradient(90deg, #1e293b 0%, #334155 50%, #1e293b 100%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite linear;
            border-radius: 4px;
        }

        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }

    </style>
</head>
<body>

    <div class="header">
        <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Product</a>
        <div class="logo"><i class="fas fa-brain"></i> WALKON AI Copilot</div>
        <div></div>
    </div>

    <div class="container">
        <!-- Product Details -->
        <div class="product-panel">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Product" class="product-image">
            <div class="product-brand"><?= htmlspecialchars($product['brand_name']) ?></div>
            <h2 class="product-title"><?= htmlspecialchars($product['name']) ?></h2>
            
            <div class="product-meta">
                <span>SKU: <?= htmlspecialchars($product['sku']) ?></span>
                <span style="color: #fff; font-weight: 700;">₹<?= number_format($product['price']) ?></span>
            </div>

            <div style="margin-bottom: 20px;">
                 <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 10px;">Select Channels to Sync</span>
                 <ul class="status-list" style="margin-top: 0;">
                    <li>
                        <div class="channel-name"><i class="fab fa-amazon" style="color: #f97316;"></i> Amazon</div>
                        <div class="toggle-switch active" onclick="toggleChannel(1, this)"></div>
                    </li>
                    <li>
                        <div class="channel-name"><i class="fab fa-shopify" style="color: #84cc16;"></i> Shopify</div>
                        <div class="toggle-switch active" onclick="toggleChannel(2, this)"></div>
                    </li>
                    <li>
                        <div class="channel-name"><i class="fab fa-ebay" style="color: #eab308;"></i> eBay</div>
                        <div class="toggle-switch" onclick="toggleChannel(3, this)"></div>
                    </li>
                 </ul>
            </div>

            <button class="btn-primary" onclick="initiateSync(this)"><i class="fas fa-bolt"></i> Execute Global Sync</button>
        </div>

        <!-- AI Functionalities -->
        <div class="ai-panel">
            
            <!-- AI Listing Optimizer -->
            <div class="ai-card">
                <div class="ai-header">
                    <div class="ai-title"><i class="fas fa-magic"></i> AI Listing Optimizer</div>
                    <span class="ai-badge">Auto-Generated</span>
                </div>
                
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">
                    Our AI parses Amazon & Shopify algorithms to generate highly converting SEO titles and attributes.
                </p>

                <div class="generated-box">
                    <span class="gen-label">Amazon Optimized Title</span>
                    <div class="gen-content" id="amzTitle" contenteditable="true">
                        <?= htmlspecialchars($product['brand_name']) ?> <?= htmlspecialchars($product['name']) ?> - Premium Orthopedic Sole, Breathable Outer - Men's/Women's
                    </div>
                </div>

                <div class="generated-box">
                    <span class="gen-label">Shopify SEO Description (Snippet)</span>
                    <div class="gen-content" id="shpDesc" contenteditable="true">
                        Step into comfort with the genuine <?= htmlspecialchars($product['name']) ?>. Engineered with advanced arch support and water-resistant materials, perfect for all-day urban wear.
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button class="btn-action" onclick="regenerateText(this)"><i class="fas fa-sync-alt"></i> Regenerate Variations</button>
                    <button class="btn-action" onclick="saveEdits(this)"><i class="fas fa-save"></i> Save Revisions</button>
                </div>
            </div>

            <!-- AI Price Intelligence -->
            <div class="ai-card">
                <div class="ai-header">
                    <div class="ai-title"><i class="fas fa-chart-line"></i> Dynamic AI Pricing</div>
                    <span class="ai-badge">Active Monitoring</span>
                </div>

                <div style="display: flex; align-items: center; gap: 30px; margin-bottom: 20px;">
                     <div>
                         <span class="gen-label">Current Base Price</span>
                         <div style="font-size: 1.8rem; font-weight: 800;">₹<?= number_format($product['price']) ?></div>
                     </div>
                     <div>
                         <span class="gen-label">AI Suggested Amazon Price</span>
                         <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary);">₹<?= number_format($product['price'] + 1500) ?></div>
                     </div>
                </div>

                <div class="generated-box" style="background: rgba(56, 189, 248, 0.05); border-color: rgba(56, 189, 248, 0.2);">
                    <div style="font-size: 0.9rem; color: #bae6fd; display: flex; gap: 10px; align-items: flex-start;">
                        <i class="fas fa-info-circle" style="margin-top: 3px;"></i>
                        <div>
                            <strong>Market Analysis:</strong> Competitors (Nike, Adidas equivalents) are priced 15% higher on Amazon. AI recommends increasing price by ₹1,500 to maximize profit while maintaining the Buy Box.
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 15px; margin-top: 15px;">
                    <label style="font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" checked style="accent-color: var(--primary); width: 16px; height: 16px;">
                        Enable Auto-Repricer within 10% bounds
                    </label>
                </div>
            </div>

            <!-- AI Image Enhancer -->
            <div class="ai-card">
                <div class="ai-header">
                    <div class="ai-title"><i class="fas fa-image"></i> Smart Image Processor</div>
                    <span class="ai-badge">Ready</span>
                </div>
                
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">
                    Automatically crops, brightens, and removes backgrounds to meet strict marketplace image guidelines (Amazon pure white background standard).
                </p>

                <div style="background: #111827; height: 60px; border-radius: 8px; border: 1px dashed var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 20px;">
                    <div style="display: flex; align-items: center; gap: 10px; font-weight: 600;">
                        <i class="fas fa-check-circle" style="color: var(--primary);"></i> Image 1 Processed (2000x2000px, bg-removed)
                    </div>
                    <button class="btn-action" onclick="viewRender()" style="padding: 6px 12px; font-size: 0.8rem;">View Render</button>
                </div>
            </div>

            <!-- Next Step Navigation -->
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 20px; padding: 30px; border: 1px solid rgba(16, 185, 129, 0.3); display: flex; align-items: center; justify-content: space-between; margin-top: 10px;">
                <div>
                    <h3 style="color: #fff; font-size: 1.1rem; margin-bottom: 5px;">Ready for the Next Step?</h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Move from Channel Sync to Global Distribution planning.</p>
                </div>
                <a href="ai_virtual_showroom.php" class="btn-primary" style="width: auto; padding: 12px 24px;">
                    Next: AI Virtual Showroom <i class="fas fa-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>

    <div id="renderModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); z-index:2000; align-items:center; justify-content:center; padding:40px;">
        <div style="background:var(--bg-panel); padding:30px; border-radius:30px; border:1px solid var(--border); max-width:800px; width:100%; position:relative;">
            <button onclick="closeModal()" style="position:absolute; top:20px; right:20px; background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;"><i class="fas fa-times"></i></button>
            <h3 style="margin-bottom:20px;"><i class="fas fa-image"></i> Processed Render</h3>
            <div style="background:#000; border-radius:15px; overflow:hidden; border:1px solid var(--border);">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" style="width:100%; height:auto; display:block;" id="modalImage">
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:20px; color:var(--text-muted); font-size:0.9rem;">
                <span>2000 x 2000px | Pure White (255,255,255)</span>
                <span>Format: WebP / PNG</span>
            </div>
            <div style="margin-top:20px; display:flex; gap:10px;">
                <button class="btn-primary" style="width:auto;"><i class="fas fa-download"></i> Download Export</button>
                <button class="btn-action" style="background:var(--primary); color:#000;"><i class="fas fa-cloud-upload-alt"></i> Push to Assets</button>
            </div>
        </div>
    </div>

    <script>
        function regenerateText(btn) {
            const icon = btn.querySelector('i');
            icon.classList.add('fa-spin');
            
            const amzTarget = document.getElementById('amzTitle');
            const shpTarget = document.getElementById('shpDesc');
            
            amzTarget.style.opacity = "0.5";
            shpTarget.style.opacity = "0.5";

            setTimeout(() => {
                icon.classList.remove('fa-spin');
                amzTarget.style.opacity = "1";
                shpTarget.style.opacity = "1";
                
                const variations = [
                    {
                        title: "<?= htmlspecialchars($product['brand_name']) ?> <?= htmlspecialchars($product['name']) ?> Sneakers - High Performance Running & Lifestyle Footwear",
                        desc: "Discover unparalleled comfort with the <?= htmlspecialchars($product['name']) ?>. Built with an orthopedic arch-support insole and a durable rubber outsole for all-terrain traction."
                    },
                    {
                        title: "WALKON <?= htmlspecialchars($product['name']) ?> | Premium Orthopedic Walking Shoe - Light & Breathable",
                        desc: "The ultimate <?= htmlspecialchars($product['name']) ?> combines medical-grade support with street-ready style. Features 4D knit mesh and memory-foam collar."
                    }
                ];
                
                const variant = variations[Math.floor(Math.random() * variations.length)];
                amzTarget.innerHTML = variant.title;
                shpTarget.innerHTML = variant.desc;
            }, 800);
        }

        async function saveEdits(btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const data = {
                product_id: <?= $product_id ?>,
                amazon_title: document.getElementById('amzTitle').innerText,
                shopify_desc: document.getElementById('shpDesc').innerText
            };

            try {
                const response = await fetch('api/save_listing_edits.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const res = await response.json();
                
                if (res.success) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Saved';
                    btn.style.background = 'var(--primary)';
                    btn.style.color = '#000';
                    setTimeout(() => {
                        btn.innerHTML = original;
                        btn.style.background = '';
                        btn.style.color = '';
                    }, 2000);
                } else {
                    alert(res.message || 'Error saving edits');
                    btn.innerHTML = original;
                }
            } catch (err) {
                console.error(err);
                btn.innerHTML = original;
            }
        }

        function viewRender() {
            document.getElementById('renderModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('renderModal').style.display = 'none';
        }

        async function toggleChannel(channelId, el) {
            el.classList.toggle('active');
            try {
                const response = await fetch('api/toggle_marketplace.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ marketplace_id: channelId })
                });
                const res = await response.json();
                if (!res.success) {
                    el.classList.toggle('active'); // revert
                    alert(res.message || 'Error toggling channel');
                }
            } catch (err) {
                console.error(err);
                el.classList.toggle('active'); // revert
            }
        }

        async function initiateSync(btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Syncing to Channels...';
            btn.style.background = '#eab308';
            btn.style.boxShadow = '0 5px 15px rgba(234, 179, 8, 0.4)';

            try {
                const response = await fetch('api/trigger_sync.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ marketplace_id: 1, channel_name: 'multi' })
                });
                const res = await response.json();

                if (res.success) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Sync Complete';
                    btn.style.background = 'var(--primary)';
                    btn.style.boxShadow = '0 5px 15px var(--primary-glow)';
                    
                    // Show a toast message if you have one, or just revert after delay
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.style.background = '';
                        btn.style.boxShadow = '';
                    }, 3000);
                } else {
                    alert(res.message || 'Sync failed');
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                }
            } catch (err) {
                console.error(err);
                btn.innerHTML = originalText;
                btn.style.background = '';
            }
        }
    </script>
</body>
</html>
