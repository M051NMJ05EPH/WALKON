<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$marketplace_id = $_GET['id'] ?? null;
if (!$marketplace_id) {
    header("Location: marketplaces.php");
    exit();
}

$seller_id = $_SESSION['seller_id'] ?? null;

try {
    // Fetch marketplace details
    $stmt = $pdo->prepare("SELECT * FROM marketplaces WHERE id = ?");
    $stmt->execute([$marketplace_id]);
    $marketplace = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$marketplace) {
        header("Location: marketplaces.php");
        exit();
    }

    // Check connection status
    $stmt = $pdo->prepare("SELECT status, last_sync FROM seller_marketplaces WHERE seller_id = ? AND marketplace_id = ?");
    $stmt->execute([$seller_id, $marketplace_id]);
    $connection = $stmt->fetch(PDO::FETCH_ASSOC);
    $isConnected = ($connection && $connection['status'] === 'connected');

    // Get sync stats
    $stmt = $pdo->prepare("SELECT COUNT(*) as synced_count FROM product_channels pc
                           JOIN product_base pb ON pc.product_id = pb.id
                           WHERE pc.channel_name = ? AND pb.seller_id = ?");
    $stmt->execute([$marketplace['name'], $seller_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($marketplace['name']) ?> Settings - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --bg: #05070A;
            --card-bg: rgba(21, 27, 43, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background: var(--bg);
            background-image: radial-gradient(circle at 10% 20%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
            color: var(--text-main);
            min-height: 100vh;
            padding: 2rem;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .channel-logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
        }

        .channel-logo img {
            max-width: 60px;
            max-height: 60px;
            filter: brightness(0) invert(1);
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            background: <?= $isConnected ? 'rgba(16, 185, 129, 0.1)' : 'rgba(100, 100, 100, 0.1)' ?>;
            color: <?= $isConnected ? 'var(--primary)' : 'var(--text-muted)' ?>;
            border: 1px solid <?= $isConnected ? 'rgba(16, 185, 129, 0.2)' : 'rgba(100, 100, 100, 0.2)' ?>;
        }

        .status-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            background: <?= $isConnected ? 'var(--primary)' : 'var(--text-muted)' ?>;
            border-radius: 50%;
            display: inline-block;
            animation: <?= $isConnected ? 'pulse 2s infinite' : 'none' ?>;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .btn-back {
            padding: 10px 20px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(-3px);
        }

        .grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
        }

        .card h2 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .stat-item {
            background: rgba(255,255,255,0.02);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
        }

        .form-input, .form-select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #fff;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255,255,255,0.05);
        }

        .btn-primary {
            padding: 12px 24px;
            background: var(--primary);
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .btn-sync {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, #059669 100%);
            color: #000;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-sync:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
        }

        .activity-log {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            padding: 1rem;
            background: rgba(255,255,255,0.02);
            border-radius: 10px;
            margin-bottom: 0.75rem;
            border-left: 3px solid var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item.warning {
            border-left-color: #f59e0b;
        }

        .activity-item.error {
            border-left-color: #ef4444;
        }

        .activity-text {
            font-size: 0.9rem;
        }

        .activity-time {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="header-left">
            <div class="channel-logo">
                <?php if($marketplace['logo_url']): ?>
                    <img src="<?= htmlspecialchars($marketplace['logo_url']) ?>" alt="<?= htmlspecialchars($marketplace['name']) ?>">
                <?php endif; ?>
            </div>
            <div>
                <h1><?= htmlspecialchars($marketplace['name']) ?></h1>
                <span class="status-badge">
                    <?= $isConnected ? 'Connected' : 'Disconnected' ?>
                </span>
            </div>
        </div>
        <a href="marketplaces.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Marketplaces
        </a>
    </div>

    <div class="grid">
        <div>
            <div class="card" style="margin-bottom: 2rem;">
                <h2><i class="fas fa-chart-line"></i> Channel Performance</h2>
                <div class="stat-grid">
                    <div class="stat-item">
                        <div class="stat-label">Synced Products</div>
                        <div class="stat-value"><?= number_format($stats['synced_count']) ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Last Sync</div>
                        <div class="stat-value" style="font-size: 1.2rem;">
                            <?= ($connection && isset($connection['last_sync']) && $connection['last_sync']) ? date('M d, H:i', strtotime($connection['last_sync'])) : 'Never' ?>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Sync Health</div>
                        <div class="stat-value" style="font-size: 1.5rem;">✓ Healthy</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Status</div>
                        <div class="stat-value" style="font-size: 1.2rem; color: <?= $isConnected ? 'var(--primary)' : '#9ca3af' ?>;">
                            <?= $isConnected ? 'Active' : 'Paused' ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2><i class="fas fa-cog"></i> Channel Configuration</h2>
                <form id="settingsForm">
                    <div class="form-group">
                        <label>Auto-Sync Frequency</label>
                        <select class="form-select" name="sync_frequency">
                            <option value="live">Live (Real-time)</option>
                            <option value="hourly">Every Hour</option>
                            <option value="daily" selected>Daily at 2 AM</option>
                            <option value="manual">Manual Only</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Price Adjustment (%)</label>
                        <input type="number" class="form-input" name="price_margin" value="0" placeholder="e.g., +10 for 10% markup">
                    </div>

                    <div class="form-group">
                        <label>Channel-Specific Description Override</label>
                        <textarea class="form-input" name="description_override" rows="4" placeholder="Leave blank to use default product descriptions"></textarea>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                </form>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom: 2rem;">
                <h2><i class="fas fa-sync-alt"></i> Quick Actions</h2>
                <button class="btn-sync" onclick="triggerSync()">
                    <i class="fas fa-refresh"></i> Sync Now
                </button>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem; text-align: center;">
                    Last sync: <?= ($connection && isset($connection['last_sync']) && $connection['last_sync']) ? date('M d, Y H:i', strtotime($connection['last_sync'])) : 'Never synced' ?>
                </p>
            </div>

            <div class="card">
                <h2><i class="fas fa-history"></i> Sync Activity</h2>
                <div class="activity-log">
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <p>No sync activity yet</p>
                        <p style="font-size: 0.85rem;">Click "Sync Now" to start tracking</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function triggerSync() {
    const btn = event.target.closest('.btn-sync');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
    btn.disabled = true;

    fetch('api/trigger_sync.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            marketplace_id: <?= $marketplace_id ?>,
            channel_name: '<?= htmlspecialchars($marketplace['name']) ?>'
        })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Synced Successfully';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                location.reload();
            }, 2000);
        } else {
            alert('Sync failed: ' + data.message);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    });
}

document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('api/save_channel_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            marketplace_id: <?= $marketplace_id ?>,
            ...data
        })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            alert('Settings saved successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    });
});
</script>

</body>
</html>
