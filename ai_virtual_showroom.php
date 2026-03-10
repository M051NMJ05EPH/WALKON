<?php
session_start();
include 'config.php';

// Fetch first product separately for initial display
$first_product = null;
try {
    $stmt = $pdo->query("SELECT pb.id, pb.name, pp.price, b.name as brand_name,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
        (SELECT GROUP_CONCAT(url SEPARATOR '||') FROM product_media pm WHERE pm.product_id = pb.id AND type='image') as all_images
        FROM product_base pb
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_specs spec ON pb.id = spec.product_id
        LEFT JOIN brands b ON spec.brand_id = b.id
        WHERE pb.status = 'published'
        ORDER BY pb.id ASC");
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($all_products)) $first_product = $all_products[0];
} catch (Exception $e) {
    $all_products = [];
}

$default_img = $first_product['primary_image'] ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80';
$default_name = $first_product['name'] ?? 'AI-Generated Concept V.01';
$default_brand = ($first_product['brand_name'] ?? 'WalkOn') . ' | BK-AI-' . ($first_product['id'] ?? '01');
$default_price = '₹' . number_format($first_product['price'] ?? 18499);
$default_all_images = isset($first_product['all_images']) ? explode('||', $first_product['all_images']) : [$default_img];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Virtual Showroom | WalkOn Next-Gen</title>
    <meta name="description" content="Experience WalkOn shoes in immersive 3D and Augmented Reality powered by neural AI.">
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
            --ar: #f97316;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); overflow-x: hidden; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; }

        .glow-sphere { position: fixed; width: 600px; height: 600px; border-radius: 50%; z-index: -1; pointer-events: none; }
        .sphere-1 { top: -200px; right: -200px; background: radial-gradient(circle, rgba(16,185,129,0.15) 0%, transparent 70%); }
        .sphere-2 { bottom: -200px; left: -200px; background: radial-gradient(circle, rgba(37,99,235,0.1) 0%, transparent 70%); }

        header { padding: 28px 0; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo span { font-size: 1.5rem; font-weight: 800; color: #fff; letter-spacing: -1px; }

        .btn { padding: 11px 22px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; border: none; font-family: 'Outfit', sans-serif; font-size: 0.85rem; }
        .btn-primary { background: var(--primary); color: #000; box-shadow: 0 4px 15px rgba(16,185,129,0.3); }
        .btn-outline { border: 1px solid rgba(255,255,255,0.15); color: #fff; background: transparent; }
        .btn-ar { background: linear-gradient(135deg, #f97316, #ef4444); color: #fff; box-shadow: 0 4px 20px rgba(249,115,22,0.4); animation: arPulse 2s infinite; }
        .btn:hover { transform: translateY(-2px); }
        @keyframes arPulse { 0%,100%{ box-shadow: 0 4px 20px rgba(249,115,22,0.35); } 50%{ box-shadow: 0 4px 40px rgba(249,115,22,0.7); } }

        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 28px; margin-top: 40px; }
        .main-card { background: var(--card); border-radius: 30px; border: 1px solid rgba(255,255,255,0.06); padding: 36px; position: relative; overflow: hidden; }

        /* ===== 3D IMAGE VIEWER ===== */
        .v3d-container {
            height: 420px;
            background: radial-gradient(ellipse at 40% 60%, #0c2238 0%, #05070a 70%);
            border-radius: 20px; margin-top: 28px; border: 1px solid rgba(16,185,129,0.18);
            position: relative; overflow: hidden; cursor: grab; user-select: none;
        }
        .v3d-container:active { cursor: grabbing; }

        /* Holographic grid lines */
        .holo-grid {
            position: absolute; inset: 0; pointer-events: none; z-index: 1;
            background-image:
                linear-gradient(rgba(16,185,129,0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16,185,129,0.035) 1px, transparent 1px);
            background-size: 44px 44px;
            transition: opacity 0.4s;
        }

        /* Glowing floor reflection */
        .v3d-floor {
            position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
            width: 70%; height: 90px;
            background: radial-gradient(ellipse, rgba(16,185,129,0.2) 0%, transparent 70%);
            pointer-events: none; z-index: 1;
        }

        /* Shoe image stage */
        .shoe-stage {
            position: absolute; inset: 0; z-index: 2;
            display: flex; align-items: center; justify-content: center;
            perspective: 800px;
        }
        #shoeImg {
            max-width: 320px; max-height: 300px; width: auto; height: auto;
            filter: drop-shadow(0 30px 60px rgba(0,0,0,0.9)) drop-shadow(0 0 30px rgba(16,185,129,0.15));
            transition: filter 0.4s, opacity 0.35s;
            pointer-events: none;
            transform: rotate(-5deg);
        }
        .shoe-reflection {
            position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%) scaleY(-0.3);
            max-width: 320px; width: 100%;
            opacity: 0.18;
            filter: blur(4px);
            pointer-events: none;
        }

        /* Floating particles */
        .particles-layer { position: absolute; inset: 0; z-index: 1; pointer-events: none; overflow: hidden; }
        .particle {
            position: absolute; border-radius: 50%;
            background: var(--primary); opacity: 0;
            animation: floatUp var(--dur, 6s) var(--delay, 0s) infinite ease-in-out;
        }
        @keyframes floatUp {
            0% { transform: translateY(100%) translateX(var(--dx,0px)); opacity: 0; }
            20% { opacity: 0.6; }
            80% { opacity: 0.3; }
            100% { transform: translateY(-120px) translateX(calc(var(--dx,0px) * -1)); opacity: 0; }
        }

        /* Corner scan lines */
        .scan-line {
            position: absolute; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, rgba(16,185,129,0.6), transparent);
            animation: scanMove 3s linear infinite; pointer-events: none; z-index: 3;
        }
        @keyframes scanMove { 0% { top: -2px; } 100% { top: 100%; } }

        /* Wireframe CSS Matrix */
        .shoe-3d-wrap {
            transform-style: preserve-3d;
            transition: transform 0.08s linear;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .shoe-3d-wrap::after {
            content: ''; position: absolute; inset: 0; z-index: 5; pointer-events: none;
            background-image: linear-gradient(rgba(16,185,129,0.35) 1px, transparent 1px), linear-gradient(90deg, rgba(16,185,129,0.35) 1px, transparent 1px);
            background-size: 20px 20px; background-position: center;
            opacity: 0; transition: opacity 0.3s;
            mix-blend-mode: screen;
            -webkit-mask-image: var(--mask-img); mask-image: var(--mask-img);
            -webkit-mask-size: contain; mask-size: contain;
            -webkit-mask-position: center; mask-position: center;
            -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat;
        }
        .wireframe-on .shoe-3d-wrap::after { opacity: 1; }

        .ai-label {
            position: absolute; top: 16px; left: 16px; z-index: 10;
            background: rgba(16,185,129,0.12); color: var(--primary);
            padding: 5px 13px; border-radius: 50px; font-size: 0.7rem; font-weight: 800;
            border: 1px solid rgba(16,185,129,0.4); text-transform: uppercase;
            backdrop-filter: blur(8px);
        }
        .ar-badge {
            position: absolute; top: 16px; right: 16px; z-index: 10;
            background: rgba(249,115,22,0.12); color: var(--ar);
            padding: 5px 13px; border-radius: 50px; font-size: 0.7rem; font-weight: 800;
            border: 1px solid rgba(249,115,22,0.35); backdrop-filter: blur(8px);
            display: flex; align-items: center; gap: 6px;
        }
        .ar-dot { width: 7px; height: 7px; background: var(--ar); border-radius: 50%; animation: blink 1.2s infinite; }
        @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.2;} }

        /* X-ray overlay */
        #xrayOverlays { position: absolute; inset: 0; pointer-events: none; display: none; z-index: 9; }
        .xray-tag { position: absolute; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; }
        .xray-tag.left { border-left: 1px dashed currentColor; padding-left: 8px; }
        .xray-tag.right { border-right: 1px dashed currentColor; padding-right: 8px; text-align: right; }

        /* Viewport controls */
        .viewport-controls {
            position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%);
            display: flex; gap: 8px; z-index: 10; white-space: nowrap;
        }
        .vc-btn {
            background: rgba(0,0,0,0.6); color: #fff; border: 1px solid rgba(255,255,255,0.12);
            padding: 7px 13px; border-radius: 10px; font-weight: 700; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px; font-family: 'Outfit', sans-serif;
            font-size: 0.72rem; transition: 0.25s; backdrop-filter: blur(12px);
        }
        .vc-btn.active, .vc-btn:hover { border-color: var(--primary); background: rgba(16,185,129,0.15); color: var(--primary); }
        .vc-btn.ar-vc { border-color: rgba(249,115,22,0.4); color: var(--ar); }
        .vc-btn.ar-vc:hover, .vc-btn.ar-vc.active { background: rgba(249,115,22,0.15); }

        /* Thumbnails for Colors/Angles */
        .color-thumbs { display: flex; gap: 12px; margin-top: 20px; overflow-x: auto; padding-bottom: 8px; }
        .color-thumbs::-webkit-scrollbar { height: 6px; }
        .color-thumbs::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .thumb-btn { 
            width: 65px; height: 65px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.03); cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; justify-content: center; overflow: hidden;
            flex-shrink: 0;
        }
        .thumb-btn img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }
        .thumb-btn.active { border-color: var(--primary); background: rgba(16,185,129,0.1); }
        .thumb-btn:hover { transform: translateY(-3px); border-color: rgba(16,185,129,0.5); }

        /* 360 hint ring */
        .hint-360 {
            position: absolute; bottom: 60px; left: 50%; transform: translateX(-50%);
            z-index: 10; color: rgba(255,255,255,0.3); font-size: 0.65rem; font-weight: 700;
            display: flex; align-items: center; gap: 6px; pointer-events: none;
        }
        .hint-360 i { animation: spinSlow 3s linear infinite; }
        @keyframes spinSlow { to { transform: rotate(360deg); } }

        /* Stats */
        .stats-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-top: 28px; }
        .stat-card { background: rgba(255,255,255,0.025); padding: 18px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.06); }
        .stat-label { font-size: 0.65rem; color: var(--muted); text-transform: uppercase; font-weight: 800; margin-bottom: 4px; }
        .stat-value { font-size: 1.15rem; font-weight: 700; color: #fff; }

        /* AI Decisions */
        .ai-decisions { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 28px; }
        .decision-card { padding: 14px; border-radius: 14px; }
        .decision-card.green { background: rgba(16,185,129,0.05); border-left: 3px solid var(--primary); }
        .decision-card.blue { background: rgba(37,99,235,0.05); border-left: 3px solid var(--secondary); }
        .decision-card h4 { font-size: 0.8rem; margin-bottom: 5px; }
        .decision-card.green h4 { color: var(--primary); }
        .decision-card.blue h4 { color: var(--secondary); }
        .decision-card p { font-size: 0.76rem; color: rgba(255,255,255,0.65); }

        /* Parametric */
        .param-controls { margin-top: 28px; background: rgba(255,255,255,0.02); padding: 26px; border-radius: 18px; border: 1px solid rgba(255,255,255,0.05); }
        .param-controls h3 { font-size: 0.95rem; display: flex; align-items: center; gap: 8px; margin-bottom: 18px; }
        .param-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; }
        .param-item span { font-size: 0.65rem; color: var(--muted); text-transform: uppercase; font-weight: 700; }
        .param-item input[type=range] { width: 100%; margin-top: 8px; accent-color: var(--primary); }

        /* Sidebar */
        .sidebar { display: flex; flex-direction: column; gap: 24px; }
        .widget { background: var(--card); border-radius: 22px; padding: 24px; border: 1px solid rgba(255,255,255,0.06); }
        .widget h3 { font-size: 0.95rem; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; color: #fff; }
        .widget h3 i { color: var(--primary); }

        .timeline { list-style: none; }
        .timeline-item { position: relative; padding-left: 18px; padding-bottom: 16px; border-left: 1px solid rgba(255,255,255,0.08); cursor: pointer; transition: 0.2s; border-radius: 8px; }
        .timeline-item:last-child { padding-bottom: 0; border-left-color: transparent; }
        .timeline-item::before { content: ''; position: absolute; left: -5px; top: 4px; width: 8px; height: 8px; background: var(--primary); border-radius: 50%; }
        .timeline-item:hover { background: rgba(16,185,129,0.05); padding-left: 22px; }
        .timeline-item span { font-size: 0.65rem; color: var(--muted); }
        .timeline-item p { font-size: 0.82rem; font-weight: 700; color: #fff; }
        .timeline-item .tprice { font-size: 0.72rem; color: var(--primary); }

        .predict-bar { height: 7px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow: hidden; margin-top: 10px; }
        .predict-fill { height: 100%; background: linear-gradient(90deg, #10b981, #2563eb); width: 0%; transition: 1.2s ease-out; }

        /* ===== AR MODAL ===== */
        #arModal {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(20px);
            align-items: center; justify-content: center; flex-direction: column;
        }
        #arModal.open { display: flex; }
        .ar-modal-inner { background: var(--card); border-radius: 30px; padding: 46px; max-width: 520px; width: 90%; border: 1px solid rgba(249,115,22,0.3); text-align: center; position: relative; }
        .ar-close { position: fixed; top: 20px; right: 24px; background: none; border: none; color: rgba(255,255,255,0.5); font-size: 1.5rem; cursor: pointer; z-index: 10; transition: color 0.2s; }
        .ar-close:hover { color: #fff; }
        .ar-icon-big { font-size: 3.5rem; color: var(--ar); margin-bottom: 16px; }
        .ar-modal-inner h2 { font-size: 1.7rem; font-weight: 800; margin-bottom: 10px; }
        .ar-modal-inner p { color: var(--muted); font-size: 0.88rem; line-height: 1.6; margin-bottom: 22px; }
        .ar-steps { display: flex; flex-direction: column; gap: 12px; text-align: left; margin-bottom: 26px; }
        .ar-step { display: flex; align-items: center; gap: 14px; background: rgba(249,115,22,0.05); padding: 13px; border-radius: 12px; border: 1px solid rgba(249,115,22,0.12); }
        .ar-step-num { width: 28px; height: 28px; border-radius: 50%; background: var(--ar); color: #000; font-weight: 800; font-size: 0.82rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .ar-step p { color: rgba(255,255,255,0.8); font-size: 0.8rem; margin: 0; }

        /* AR live view */
        #arViewport {
            display: none; width: 92vw; max-width: 760px; border-radius: 20px;
            overflow: hidden; position: relative; background: #000;
        }
        #arVideo { width: 100%; height: 420px; object-fit: cover; display: block; }
        #arCanvas { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }
        .ar-live-badge { position: absolute; top: 14px; left: 14px; background: rgba(249,115,22,0.9); color: #fff; padding: 4px 12px; border-radius: 50px; font-size: 0.68rem; font-weight: 800; z-index: 5; display: flex; align-items: center; gap: 6px; }
        .ar-live-dot { width: 6px; height: 6px; background: #fff; border-radius: 50%; animation: blink 1s infinite; }
        .ar-placement-hint { position: absolute; bottom: 60px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.7); color: #fff; padding: 8px 20px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; backdrop-filter: blur(8px); white-space: nowrap; z-index: 5; }
        .ar-overlay-shoe { position: absolute; z-index: 4; pointer-events: none; transition: all 0.2s; }
        .ar-overlay-shoe img { max-width: 280px; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.7)) drop-shadow(0 0 20px rgba(16,185,129,0.4)); animation: arFloat 3s ease-in-out infinite; }
        @keyframes arFloat { 0%,100%{ transform: translateY(0) rotate(-5deg); } 50%{ transform: translateY(-12px) rotate(-3deg); } }
        .ar-controls { position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 5; }

        footer { padding: 36px 0; text-align: center; border-top: 1px solid rgba(255,255,255,0.05); margin-top: 70px; color: var(--muted); font-size: 0.88rem; }

        @media(max-width: 900px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .stats-row { grid-template-columns: repeat(2,1fr); }
            .param-grid { grid-template-columns: 1fr 1fr; }
            .ai-decisions { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="glow-sphere sphere-1"></div>
    <div class="glow-sphere sphere-2"></div>

    <!-- ===== AR MODAL ===== -->
    <div id="arModal">
        <button class="ar-close" onclick="closeAR()"><i class="fas fa-times"></i></button>

        <!-- Step 1: Instructions -->
        <div class="ar-modal-inner" id="arInstructions">
            <div class="ar-icon-big"><i class="fas fa-vr-cardboard"></i></div>
            <h2>Try On in Augmented Reality</h2>
            <p>Place the shoe in your real environment using your camera. Tap the screen to position it anywhere.</p>
            <div class="ar-steps">
                <div class="ar-step"><div class="ar-step-num">1</div><p>Allow camera access when prompted by your browser</p></div>
                <div class="ar-step"><div class="ar-step-num">2</div><p>Point camera at a flat surface — floor, table, or ground</p></div>
                <div class="ar-step"><div class="ar-step-num">3</div><p>Tap anywhere on screen to place the shoe in your space</p></div>
            </div>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                <button class="btn btn-ar" id="launchArBtn" onclick="launchAR()"><i class="fas fa-camera"></i> Launch AR Camera</button>
                <button class="btn btn-outline" onclick="closeAR()">Cancel</button>
            </div>
        </div>

        <!-- Step 2: Live AR view -->
        <div id="arViewport">
            <video id="arVideo" autoplay playsinline muted></video>
            <canvas id="arCanvas"></canvas>
            <div class="ar-live-badge"><div class="ar-live-dot"></div> AR LIVE</div>
            <div class="ar-placement-hint" id="arHint">📷 Point camera at floor — tap to place shoe</div>
            <div class="ar-overlay-shoe" id="arShoeOverlay" style="display:none;">
                <img id="arShoeImg" src="" alt="shoe">
            </div>
            <div class="ar-controls">
                <button class="vc-btn ar-vc" onclick="captureAR()"><i class="fas fa-camera"></i> Screenshot</button>
                <button class="vc-btn ar-vc" onclick="resetARPlacement()"><i class="fas fa-redo"></i> Reposition</button>
                <button class="vc-btn" onclick="closeAR()"><i class="fas fa-stop"></i> Stop AR</button>
            </div>
        </div>
    </div>

    <div class="container">
        <header>
            <a href="shop.php" class="logo">
                <img src="assets/shoe_logo_green.png" alt="WalkOn" style="height:38px;width:auto;" onerror="this.style.display='none'">
                <span>WALK<span style="color:var(--primary);">ON</span> AI</span>
            </a>
            <div style="display:flex;gap:12px;align-items:center;">
                <a href="javascript:history.back()" class="btn btn-outline" style="padding: 10px 16px; border-radius: 50px; font-weight: 700;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button onclick="openAR()" class="btn btn-ar"><i class="fas fa-vr-cardboard"></i> Try in AR</button>
                <button onclick="regenerateAI()" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Regenerate</button>
                <a href="shop.php" class="btn btn-outline"><i class="fas fa-shopping-bag"></i> Shop</a>
            </div>
        </header>

        <div style="margin-top:36px;">
            <h1 style="font-size:2.8rem;font-weight:800;letter-spacing:-2px;">AI Virtual Showroom</h1>
            <p style="color:var(--muted);font-size:1.05rem;margin-top:8px;">Experience footwear in <span style="color:var(--primary);font-weight:700;">real-time 3D</span> &amp; <span style="color:var(--ar);font-weight:700;">Augmented Reality</span> &mdash; powered by neural AI.</p>
        </div>

        <div class="dashboard-grid">
            <!-- MAIN CARD -->
            <div class="main-card">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h2 id="productTitle" style="font-size:1.45rem;"><?= htmlspecialchars($default_name) ?></h2>
                        <span id="productBrand" style="color:var(--muted);font-size:0.78rem;"><?= htmlspecialchars($default_brand) ?></span>
                    </div>
                    <div style="text-align:right;">
                        <div id="productPrice" style="font-size:1.8rem;font-weight:800;color:var(--primary);"><?= $default_price ?></div>
                        <span style="color:var(--muted);font-size:0.65rem;text-transform:uppercase;">Predicted Market Value</span>
                    </div>
                </div>

                <!-- 3D IMAGE VIEWPORT -->
                <div class="v3d-container" id="v3dViewport">
                    <div class="ai-label"><i class="fas fa-microchip"></i> Real-time Neural View</div>
                    <div class="ar-badge"><div class="ar-dot"></div> AR Ready</div>
                    <div class="holo-grid"></div>
                    <div class="v3d-floor"></div>
                    <div class="scan-line"></div>
                    <div class="particles-layer" id="particlesLayer"></div>

                    <!-- Shoe display -->
                    <div class="shoe-stage" id="shoeStage">
                        <div class="shoe-3d-wrap" id="shoe3dWrap">
                            <img id="shoeImg"
                                src="<?= htmlspecialchars($default_img) ?>"
                                alt="Shoe 3D View"
                                onerror="this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80'">
                        </div>
                    </div>

                    <!-- X-Ray Overlays -->
                    <div id="xrayOverlays">
                        <div class="xray-tag left" style="top:30%;left:18%;color:var(--primary);"><span style="font-weight:800;">[ CARBON-PLATE ]</span><br>Max Energy Return</div>
                        <div class="xray-tag right" style="bottom:28%;right:16%;color:var(--secondary);"><span style="font-weight:800;">[ AI-GEL CORE ]</span><br>Impact Absorption</div>
                        <div class="xray-tag left" style="top:18%;right:22%;color:#f97316;"><span style="font-weight:800;">[ NEURAL MESH ]</span><br>Adaptive Upper</div>
                    </div>

                    <!-- 360 drag hint -->
                    <div class="hint-360" id="hint360">
                        <i class="fas fa-sync-alt"></i> Drag to rotate &nbsp;|&nbsp; Scroll to zoom
                    </div>

                    <!-- Controls -->
                    <div class="viewport-controls">
                        <button class="vc-btn" id="rotateBtn" onclick="toggleAutoRotate()">
                            <i class="fas fa-sync-alt" id="rotateIcon"></i> Auto Rotate
                        </button>
                        <button class="vc-btn" id="xrayBtn" onclick="toggleXRay()">
                            <i class="fas fa-microscope"></i> X-Ray
                        </button>
                        <button class="vc-btn" id="wireframeBtn" onclick="toggleWireframe()">
                            <i class="fas fa-border-all"></i> Wireframe
                        </button>
                        <button class="vc-btn" id="zoomInBtn" onclick="zoomShoe(1.1)">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button class="vc-btn" id="zoomOutBtn" onclick="zoomShoe(0.9)">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <button class="vc-btn ar-vc" onclick="openAR()">
                            <i class="fas fa-vr-cardboard"></i> AR View
                        </button>
                        <select id="envSelect" onchange="changeEnv(this.value)" style="background:rgba(0,0,0,0.65);color:#fff;border:1px solid rgba(255,255,255,0.12);padding:7px 12px;border-radius:10px;font-weight:700;cursor:pointer;outline:none;font-family:'Outfit',sans-serif;font-size:0.72rem;backdrop-filter:blur(8px);">
                            <option value="lab">🔬 Lab Studio</option>
                            <option value="urban">🌆 Urban Night</option>
                            <option value="midnight">🌑 Deep Abyss</option>
                            <option value="sunrise">🌅 Sunrise</option>
                        </select>
                    </div>
                </div>

                <!-- Color / Angle Thumbnails -->
                <div class="color-thumbs" id="colorThumbs">
                    <?php 
                    // Auto-generate AI Color variants if there's only 1 image
                    $render_images = $default_all_images;
                    $is_artificial = count($default_all_images) <= 1;
                    $hues = [0, 110, 230, 310]; // Base, Green, Blue, Purple
                    $loops = $is_artificial ? 4 : count($render_images);
                    
                    for($i = 0; $i < $loops; $i++): 
                        $img_url = $is_artificial ? $render_images[0] : $render_images[$i];
                        $filter_css = $is_artificial && $i > 0 ? "filter: hue-rotate({$hues[$i]}deg) saturate(1.5);" : "";
                        $data_hue = $is_artificial ? $hues[$i] : 0;
                    ?>
                        <div class="thumb-btn <?= $i === 0 ? 'active' : '' ?>" onclick="changeShoeImage('<?= htmlspecialchars($img_url) ?>', this, <?= $data_hue ?>)">
                            <img src="<?= htmlspecialchars($img_url) ?>" alt="Color Angle" style="<?= $filter_css ?>">
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-card"><div class="stat-label">Style Score</div><div class="stat-value">98.4 / 100</div></div>
                    <div class="stat-card"><div class="stat-label">Neural Match</div><div class="stat-value" id="radarMatch">87.7%</div></div>
                    <div class="stat-card"><div class="stat-label">Durability</div><div class="stat-value">Rank S</div></div>
                    <div class="stat-card"><div class="stat-label">Eco Score</div><div class="stat-value">A++</div></div>
                </div>

                <!-- AI Decisions -->
                <div class="ai-decisions">
                    <div class="decision-card green">
                        <h4>Adaptive Sole Tech</h4>
                        <p>AI predicted a 12% increase in heel pressure for your gait pattern. Design adjusted.</p>
                    </div>
                    <div class="decision-card blue">
                        <h4>Thermal Optimization</h4>
                        <p>Knit density increased in toe box to maintain temperature based on local climate data.</p>
                    </div>
                </div>

                <!-- Parametric Controls -->
                <div class="param-controls">
                    <h3><i class="fas fa-sliders-h" style="color:var(--primary);"></i> Parametric Customization</h3>
                    <div class="param-grid">
                        <div class="param-item"><span>Zoom Level</span><input type="range" min="50" max="150" value="100" id="sliderZoom" oninput="zoomShoe(this.value/100, true)"></div>
                        <div class="param-item"><span>Rotation</span><input type="range" min="-180" max="180" value="0" id="sliderRot" oninput="setShoeRotation(this.value)"></div>
                        <div class="param-item"><span>Brightness</span><input type="range" min="60" max="140" value="100" id="sliderBright" oninput="setShoeFilter(this.value)"></div>
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="sidebar">
                <!-- AI Fit Lab -->
                <div class="widget">
                    <h3><i class="fas fa-brain"></i> AI Fit Lab</h3>
                    <p style="font-size:0.82rem;color:var(--muted);margin-bottom:16px;">Upload a photo of your foot to generate a precise 3D digital twin.</p>
                    <button class="btn btn-outline" onclick="start3DScan(this)" style="width:100%;justify-content:center;font-size:0.82rem;border-radius:12px;">
                        <i class="fas fa-upload"></i> Initialize 3D Scan
                    </button>
                    <div id="scanStatus" style="margin-top:14px;padding:10px;background:rgba(249,115,22,0.1);border-radius:10px;color:#f97316;font-size:0.68rem;font-weight:700;">
                        <i class="fas fa-info-circle"></i> Accuracy: +/- 0.2mm
                    </div>
                    <div style="margin-top:12px;">
                        <button class="btn btn-ar" onclick="openAR()" style="width:100%;justify-content:center;font-size:0.82rem;border-radius:12px;">
                            <i class="fas fa-vr-cardboard"></i> Try On in AR
                        </button>
                    </div>
                </div>

                <!-- Neural Collection -->
                <div class="widget">
                    <h3><i class="fas fa-satellite-dish"></i> Neural Collection</h3>
                    <div style="max-height:380px;overflow-y:auto;padding-right:6px;">
                        <?php foreach($all_products as $product): ?>
                        <div class="timeline-item" onclick="selectProduct(<?= htmlspecialchars(json_encode($product)) ?>)">
                            <span>ID: BK-AI-<?= $product['id'] ?></span>
                            <p><?= htmlspecialchars($product['name']) ?></p>
                            <p class="tprice">₹<?= number_format($product['price']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Prediction -->
                <div class="widget">
                    <h3><i class="fas fa-chart-line"></i> Price Prediction</h3>
                    <div style="display:flex;justify-content:space-between;font-size:0.8rem;">
                        <span>Current Trend</span>
                        <span style="color:var(--primary);">+4.2% Growth</span>
                    </div>
                    <div class="predict-bar"><div class="predict-fill" id="pBar"></div></div>
                    <p style="font-size:0.72rem;color:var(--muted);margin-top:10px;">Market saturation predicted in 14 days. Recommend purchase within 72 hours.</p>
                </div>

                <!-- Investment -->
                <div class="widget" style="background:linear-gradient(135deg,#064e3b,#0a0e17);border-color:rgba(16,185,129,0.3);">
                    <h3><i class="fas fa-shopping-cart"></i> Investment</h3>
                    <div style="margin-bottom:18px;">
                        <div id="investPrice" style="font-size:1.5rem;font-weight:800;"><?= $default_price ?></div>
                        <span style="font-size:0.68rem;color:var(--primary);">Optimized Price &middot; No hidden fees</span>
                    </div>
                    <button class="btn btn-primary" onclick="executeSmartBuy(this)" style="width:100%;height:46px;font-size:0.95rem;border-radius:12px;">
                        Execute Smart Buy <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            &copy; 2026 WalkOn Intelligence Systems &middot; Elevating Footwear through Neural Innovation &middot; 3D &amp; AR Powered
        </div>
    </footer>

    <?php include 'includes/chatbot.php'; ?>

    <script>
    // ============================================================
    //  PARTICLES
    // ============================================================
    (function spawnParticles() {
        const layer = document.getElementById('particlesLayer');
        for (let i = 0; i < 28; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            const size = Math.random() * 4 + 2;
            p.style.cssText = `
                width:${size}px; height:${size}px;
                left:${Math.random()*100}%;
                bottom:${Math.random()*30}%;
                --dur:${5+Math.random()*6}s;
                --delay:${Math.random()*6}s;
                --dx:${(Math.random()-0.5)*60}px;
                background:${Math.random()>0.5?'#10b981':'#2563eb'};
            `;
            layer.appendChild(p);
        }
    })();

    // ============================================================
    //  3D IMAGE VIEWER (CSS 3D + Drag)
    // ============================================================
    const viewport  = document.getElementById('v3dViewport');
    const wrap      = document.getElementById('shoe3dWrap');
    const shoeImg   = document.getElementById('shoeImg');

    let rotX = 0, rotY = -5, scale = 1;
    let isDragging = false, lastX = 0, lastY = 0;
    let autoRotating = false, rotInterval = null;

    function applyTransform() {
        wrap.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg) scale(${scale})`;
        shoeImg.style.transform = `rotate(0deg)`;
    }
    applyTransform();

    viewport.addEventListener('mousedown', e => { isDragging = true; lastX = e.clientX; lastY = e.clientY; viewport.style.boxShadow = 'inset 0 0 60px rgba(16,185,129,0.15)'; document.getElementById('hint360').style.opacity='0'; });
    window.addEventListener('mouseup', () => { isDragging = false; viewport.style.boxShadow = ''; });
    window.addEventListener('mousemove', e => {
        if (!isDragging) return;
        const dx = e.clientX - lastX, dy = e.clientY - lastY;
        rotY += dx * 0.4;
        rotX -= dy * 0.2;
        rotX = Math.max(-30, Math.min(30, rotX));
        lastX = e.clientX; lastY = e.clientY;
        applyTransform();
    });

    // Touch support
    viewport.addEventListener('touchstart', e => { isDragging = true; lastX = e.touches[0].clientX; lastY = e.touches[0].clientY; });
    window.addEventListener('touchend', () => isDragging = false);
    window.addEventListener('touchmove', e => {
        if (!isDragging) return;
        const dx = e.touches[0].clientX - lastX, dy = e.touches[0].clientY - lastY;
        rotY += dx * 0.35; rotX -= dy * 0.18;
        rotX = Math.max(-30, Math.min(30, rotX));
        lastX = e.touches[0].clientX; lastY = e.touches[0].clientY;
        applyTransform();
    });

    // Scroll to zoom
    viewport.addEventListener('wheel', e => { e.preventDefault(); scale = Math.max(0.6, Math.min(2.2, scale - e.deltaY*0.001)); applyTransform(); }, { passive: false });

    function toggleAutoRotate() {
        autoRotating = !autoRotating;
        const btn = document.getElementById('rotateBtn');
        const icon = document.getElementById('rotateIcon');
        btn.classList.toggle('active', autoRotating);
        if (autoRotating) {
            icon.classList.add('fa-spin');
            rotInterval = setInterval(() => { if (!isDragging) { rotY += 0.8; applyTransform(); } }, 30);
        } else {
            icon.classList.remove('fa-spin');
            clearInterval(rotInterval);
        }
    }

    function zoomShoe(factor, absolute = false) {
        if (absolute) scale = factor;
        else scale = Math.max(0.5, Math.min(2.2, scale * factor));
        applyTransform();
    }

    function setShoeRotation(deg) {
        rotY = parseFloat(deg);
        applyTransform();
    }

    function setShoeFilter(brightness) {
        shoeImg.style.filter = `drop-shadow(0 30px 60px rgba(0,0,0,0.9)) drop-shadow(0 0 30px rgba(16,185,129,0.15)) brightness(${brightness/100})`;
    }

    // ============================================================
    //  VIEW EFFECTS (X-RAY & WIREFRAME)
    // ============================================================
    let xrayOn = false;
    let wireframeOn = false;

    function resetFilters() {
        xrayOn = false; wireframeOn = false;
        document.getElementById('xrayBtn').classList.remove('active');
        document.getElementById('wireframeBtn').classList.remove('active');
        document.getElementById('xrayOverlays').style.display = 'none';
        
        let baseFilt = 'drop-shadow(0 30px 60px rgba(0,0,0,0.9)) drop-shadow(0 0 30px rgba(16,185,129,0.15))';
        if(typeof currentHueFilter !== 'undefined' && currentHueFilter > 0) {
            baseFilt += ` hue-rotate(${currentHueFilter}deg) saturate(1.2)`;
        }
        shoeImg.style.filter = baseFilt;
        document.getElementById('shoeStage').classList.remove('wireframe-on');
    }

    function toggleXRay() {
        if (!xrayOn) resetFilters();
        xrayOn = !xrayOn;
        const btn = document.getElementById('xrayBtn');
        btn.classList.toggle('active', xrayOn);
        document.getElementById('xrayOverlays').style.display = xrayOn ? 'block' : 'none';
        
        if (xrayOn) {
            shoeImg.style.filter = 'drop-shadow(0 0 30px rgba(16,185,129,0.8)) opacity(0.6) grayscale(1) brightness(1.8)';
        } else {
            resetFilters();
        }
    }

    function toggleWireframe() {
        if (!wireframeOn) resetFilters();
        wireframeOn = !wireframeOn;
        const btn = document.getElementById('wireframeBtn');
        btn.classList.toggle('active', wireframeOn);
        
        if (wireframeOn) {
            // Apply green tech-vision visual filter
            shoeImg.style.filter = 'drop-shadow(0 0 20px rgba(16,185,129,0.9)) sepia(1) hue-rotate(110deg) saturate(4) brightness(0.9) contrast(2)';
            // Sync mask image
            wrap.style.setProperty('--mask-img', `url("${shoeImg.src}")`);
            document.getElementById('shoeStage').classList.add('wireframe-on');
        } else {
            resetFilters();
        }
    }

    // ============================================================
    //  ENVIRONMENT
    // ============================================================
    const envBgs = {
        lab:      'radial-gradient(ellipse at 40% 60%, #0c2238 0%, #05070a 70%)',
        urban:    'radial-gradient(ellipse at 40% 60%, #140d2e 0%, #020617 70%)',
        midnight: 'radial-gradient(ellipse at 40% 60%, #080810 0%, #000000 70%)',
        sunrise:  'radial-gradient(ellipse at 40% 60%, #1a0e00 0%, #050200 70%)',
    };
    const envShadow = {
        lab:      'rgba(16,185,129,0.15)',
        urban:    'rgba(99,37,235,0.2)',
        midnight: 'rgba(100,100,200,0.1)',
        sunrise:  'rgba(249,115,22,0.2)',
    };
    function changeEnv(mode) {
        viewport.style.background = envBgs[mode] || envBgs.lab;
        const s = envShadow[mode] || envShadow.lab;
        shoeImg.style.filter = `drop-shadow(0 30px 60px rgba(0,0,0,0.9)) drop-shadow(0 0 30px ${s})`;
        // Flash
        viewport.style.opacity = '0.5';
        setTimeout(() => viewport.style.opacity = '1', 200);
    }

    // ============================================================
    //  PRODUCT SELECTION & THUMBNAILS
    // ============================================================
    let currentHueFilter = 0;

    function changeShoeImage(imgUrl, thumbElement, hueOffset = 0) {
        if(!imgUrl) return;
        resetFilters();
        shoeImg.style.opacity = '0';
        shoeImg.style.filter = 'blur(10px) brightness(1.5)';
        currentHueFilter = hueOffset;
        
        setTimeout(() => {
            shoeImg.src = imgUrl;
            document.getElementById('arShoeImg').src = shoeImg.src;
            if(wireframeOn) wrap.style.setProperty('--mask-img', `url("${shoeImg.src}")`);
            
            // Apply hue to AR shoe too
            document.getElementById('arShoeImg').style.filter = hueOffset ? `hue-rotate(${hueOffset}deg) saturate(1.5)` : '';
            
            shoeImg.onload = () => {
                shoeImg.style.opacity = '1';
                let baseFilter = 'drop-shadow(0 30px 60px rgba(0,0,0,0.9)) drop-shadow(0 0 30px rgba(16,185,129,0.15))';
                if(hueOffset > 0) baseFilter += ` hue-rotate(${hueOffset}deg) saturate(1.2)`;
                shoeImg.style.filter = baseFilter;
            };
        }, 200);

        if(thumbElement) {
            document.querySelectorAll('.thumb-btn').forEach(btn => btn.classList.remove('active'));
            thumbElement.classList.add('active');
        }
    }

    function selectProduct(p) {
        // Transition out
        resetFilters();
        shoeImg.style.opacity = '0';
        shoeImg.style.filter = 'blur(15px) brightness(1.5)';
        rotY = -5; rotX = 0; scale = 1;

        setTimeout(() => {
            const fallback = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80';
            const mainImg = (p.primary_image && p.primary_image.trim()) ? p.primary_image : fallback;
            shoeImg.src = mainImg;
            document.getElementById('productTitle').innerText = p.name;
            document.getElementById('productBrand').innerText = (p.brand_name || 'WalkOn') + ' | BK-AI-' + p.id;
            const ps = '₹' + parseInt(p.price).toLocaleString();
            document.getElementById('productPrice').innerText = ps;
            document.getElementById('investPrice').innerText = ps;
            document.getElementById('arShoeImg').src = shoeImg.src;

            // Generate Thumbnails
            const thumbContainer = document.getElementById('colorThumbs');
            thumbContainer.innerHTML = '';
            let allImages = p.all_images ? p.all_images.split('||') : [];
            allImages = allImages.filter(img => img.trim().length > 0);
            if(!allImages.includes(mainImg)) allImages.unshift(mainImg); // ensure main is first
            
            // Auto generate AI color ways if missing
            const isArtificial = allImages.length <= 1;
            const hues = [0, 110, 230, 310];
            const loops = isArtificial ? 4 : allImages.length;
            
            for(let i=0; i<loops; i++) {
                const imgUrl = isArtificial ? allImages[0] : allImages[i];
                if(!imgUrl) continue;
                
                const activeClass = i === 0 ? 'active' : '';
                const dataHue = isArtificial ? hues[i] : 0;
                const filterCss = isArtificial && i > 0 ? `filter: hue-rotate(${hues[i]}deg) saturate(1.5);` : '';
                
                thumbContainer.innerHTML += `
                    <div class="thumb-btn ${activeClass}" onclick="changeShoeImage('${imgUrl.replace(/'/g,"\\'").replace(/"/g,'&quot;')}', this, ${dataHue})">
                        <img src="${imgUrl}" alt="Shoe Color" style="${filterCss}">
                    </div>
                `;
            }

            shoeImg.onload = () => {
                shoeImg.style.opacity = '1';
                shoeImg.style.filter = 'drop-shadow(0 30px 60px rgba(0,0,0,0.9)) drop-shadow(0 0 30px rgba(16,185,129,0.15))';
                applyTransform();
            };
            document.getElementById('radarMatch').innerText = (84 + Math.random()*12).toFixed(1) + '%';
            document.getElementById('pBar').style.width = (50 + Math.random()*40) + '%';
        }, 320);

        // Highlight ring
        viewport.style.boxShadow = '0 0 0 2px var(--primary)';
        setTimeout(() => viewport.style.boxShadow = '', 800);
    }

    // ============================================================
    //  REGENERATE
    // ============================================================
    const shoeUrls = [
        'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&q=80',
    ];
    let shoeIdx = 0;
    function regenerateAI() {
        resetFilters();
        shoeImg.style.opacity = '0';
        shoeImg.style.filter = 'blur(12px) brightness(2)';
        setTimeout(() => {
            shoeIdx = (shoeIdx + 1) % shoeUrls.length;
            shoeImg.src = shoeUrls[shoeIdx];
            document.getElementById('arShoeImg').src = shoeImg.src;
            shoeImg.onload = () => {
                shoeImg.style.opacity = '1';
                shoeImg.style.filter = 'drop-shadow(0 30px 60px rgba(0,0,0,0.9)) drop-shadow(0 0 30px rgba(16,185,129,0.15))';
            };
            document.getElementById('radarMatch').innerText = (80 + Math.random()*15).toFixed(1) + '%';
            document.getElementById('pBar').style.width = (45 + Math.random()*50) + '%';
        }, 400);
    }

    // ============================================================
    //  AR EXPERIENCE
    // ============================================================
    let arStream = null;
    let arPlaced = false;
    let arShoeX = 50, arShoeY = 60;

    function openAR() {
        document.getElementById('arModal').classList.add('open');
        document.getElementById('arInstructions').style.display = 'block';
        document.getElementById('arViewport').style.display = 'none';
        // Pre-load current shoe into AR image
        document.getElementById('arShoeImg').src = shoeImg.src;
    }

    function closeAR() {
        document.getElementById('arModal').classList.remove('open');
        if (arStream) { arStream.getTracks().forEach(t => t.stop()); arStream = null; }
        document.getElementById('arViewport').style.display = 'none';
        document.getElementById('arInstructions').style.display = 'block';
        arPlaced = false;
    }

    async function launchAR() {
        const btn = document.getElementById('launchArBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting Camera...';
        btn.disabled = true;

        try {
            arStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false });
            const video = document.getElementById('arVideo');
            video.srcObject = arStream;
            await video.play();

            document.getElementById('arInstructions').style.display = 'none';
            const arVP = document.getElementById('arViewport');
            arVP.style.display = 'block';

            // Reset placement
            arPlaced = false;
            arShoeX = 50; arShoeY = 60;
            document.getElementById('arShoeOverlay').style.display = 'none';
            document.getElementById('arHint').style.display = 'block';

            // Tap to place
            arVP.addEventListener('click', handleARTap, { once: false });

        } catch (err) {
            // Camera denied – show demo mode
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-camera"></i> Launch AR Camera';
            document.getElementById('arInstructions').innerHTML = `
                <div class="ar-icon-big"><i class="fas fa-exclamation-triangle" style="color:var(--ar);"></i></div>
                <h2>Camera Not Available</h2>
                <p style="color:var(--muted);">Camera access was denied or not supported. You can still preview the AR placement below.</p>
                <div style="margin-top:20px;">
                    <button class="btn btn-ar" onclick="launchARDemo()"><i class="fas fa-play"></i> View AR Demo</button>
                    <button class="btn btn-outline" onclick="closeAR()" style="margin-left:10px;">Cancel</button>
                </div>
            `;
        }
    }

    function handleARTap(e) {
        const arVP = document.getElementById('arViewport');
        const rect = arVP.getBoundingClientRect();
        arShoeX = ((e.clientX - rect.left) / rect.width) * 100;
        arShoeY = ((e.clientY - rect.top) / rect.height) * 100;
        placeARShoe();
    }

    function placeARShoe() {
        const overlay = document.getElementById('arShoeOverlay');
        overlay.style.display = 'block';
        overlay.style.left = arShoeX + '%';
        overlay.style.top  = arShoeY + '%';
        overlay.style.transform = 'translate(-50%, -50%)';
        document.getElementById('arHint').style.display = 'none';
        arPlaced = true;
    }

    function resetARPlacement() {
        document.getElementById('arShoeOverlay').style.display = 'none';
        document.getElementById('arHint').style.display = 'block';
        arPlaced = false;
    }

    function launchARDemo() {
        document.getElementById('arInstructions').style.display = 'none';
        const arVP = document.getElementById('arViewport');
        document.getElementById('arVideo').style.background = 'linear-gradient(135deg,#0a1f0f,#061420)';
        arVP.style.display = 'block';
        arShoeX = 50; arShoeY = 55;
        placeARShoe();
    }

    function captureAR() {
        const arVP = document.getElementById('arViewport');
        const canvas = document.getElementById('arCanvas');
        canvas.width = arVP.offsetWidth;
        canvas.height = arVP.offsetHeight;
        const ctx = canvas.getContext('2d');
        const video = document.getElementById('arVideo');
        if (video.srcObject) ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const link = document.createElement('a');
        link.download = 'walkon-ar-tryon.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    // ============================================================
    //  OTHER CONTROLS
    // ============================================================
    function start3DScan(btn) {
        const status = document.getElementById('scanStatus');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Accessing Lidar...';
        status.style.background = 'rgba(37,99,235,0.1)';
        status.style.color = 'var(--secondary)';
        status.innerHTML = '<i class="fas fa-sync fa-spin"></i> Processing depth map...';
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-check"></i> 3D Scan Complete';
            status.style.background = 'rgba(16,185,129,0.1)';
            status.style.color = 'var(--primary)';
            status.innerHTML = '<i class="fas fa-user-check"></i> Digital Twin Generated: UK 9.5 (Match 99%)';
        }, 2500);
    }

    function executeSmartBuy(btn) {
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
        btn.style.background = '#eab308';
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-check"></i> Investment Confirmed';
            btn.style.background = 'var(--primary)';
        }, 2000);
    }

    // Boot
    setTimeout(() => document.getElementById('pBar').style.width = '67%', 500);
    setTimeout(() => document.getElementById('hint360').style.opacity = '0', 4000);
    </script>
</body>
</html>
