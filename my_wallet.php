<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$seller_id = $_SESSION['seller_id'] ?? null;

// Fetch seller_id if missing from session
if (!$seller_id) {
    try {
        $stmt_s = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $stmt_s->execute([$email]);
        $seller = $stmt_s->fetch();
        $seller_id = $seller ? $seller['id'] : -1;
        $_SESSION['seller_id'] = $seller_id;
    } catch (PDOException $e) {
        $seller_id = -1;
    }
}

// Initialize default wallet state
$wallet = ['id' => 0, 'balance' => 0.00];
$transactions = [];
$stats = ['total_credited' => 0, 'total_commission' => 0];

if ($seller_id != -1) {
    try {
        // Fetch Wallet Info
        $stmt_wallet = $pdo->prepare("SELECT * FROM wallets WHERE seller_id = ?");
        $stmt_wallet->execute([$seller_id]);
        $wallet_data = $stmt_wallet->fetch();

        if (!$wallet_data) {
            // Auto-create if not exists
            $pdo->prepare("INSERT IGNORE INTO wallets (seller_id, balance) VALUES (?, 0.00)")->execute([$seller_id]);
            $stmt_wallet->execute([$seller_id]);
            $wallet_data = $stmt_wallet->fetch();
        }

        if ($wallet_data) {
            $wallet = $wallet_data;
            
            // Fetch Transactions
            $stmt_tx = $pdo->prepare("SELECT * FROM wallet_transactions WHERE wallet_id = ? ORDER BY created_at DESC");
            $stmt_tx->execute([$wallet['id']]);
            $transactions = $stmt_tx->fetchAll(PDO::FETCH_ASSOC);

            // Fetch Stats for the seller
            $stmt_stats = $pdo->prepare("
                SELECT 
                    SUM(amount) as total_credited,
                    SUM(commission_deducted) as total_commission
                FROM wallet_transactions 
                WHERE wallet_id = ? AND type = 'credit'
            ");
            $stmt_stats->execute([$wallet['id']]);
            $stats_data = $stmt_stats->fetch();
            if ($stats_data) {
                $stats = $stats_data;
            }
        }
    } catch (PDOException $e) {
        // Log error if needed
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet | WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;       /* Royal Blue */
            --bg: #ffffff;
            --surface: rgba(255, 255, 255, 0.8);
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text-main: #1e293b;     /* Deep Navy */
            --text-muted: #64748b;
            --accent: #10b981;        /* Emerald Green */
            --sky-light: #f0f9ff;
            --sky-mid: #e0f2fe;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { 
            background: radial-gradient(circle at 10% 20%, var(--sky-mid) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, var(--sky-light) 0%, transparent 40%),
                        var(--bg);
            color: var(--text-main); min-height: 100vh; padding: 40px; 
        }

        .container { max-width: 1000px; margin: 0 auto; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; margin-bottom: 30px; transition: 0.3s; font-weight: 500; }
        .back-link:hover { color: var(--primary); transform: translateX(-5px); }

        .wallet-header {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 24px;
            border: 1px solid var(--border);
            text-align: left;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(37, 99, 235, 0.08); border-color: var(--primary); }
        .stat-label { font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 10px; letter-spacing: 1px; }
        .stat-value { font-size: 2rem; font-weight: 800; color: var(--text-main); }

        .main-card {
            background: var(--card-bg);
            border-radius: 32px;
            border: 1px solid var(--border);
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.03);
            background-image: linear-gradient(135deg, rgba(37, 99, 235, 0.02) 0%, transparent 100%);
        }

        .tx-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tx-table th { text-align: left; padding: 15px; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        .tx-table td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.95rem; color: var(--text-main); }

        .type-pill { padding: 4px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .type-credit { background: var(--sky-light); color: var(--primary); }
        .type-debit { background: rgba(239, 68, 68, 0.05); color: #ef4444; }
        .type-payout { background: rgba(37, 99, 235, 0.05); color: var(--primary); }

        .btn-payout {
            background: var(--text-main);
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            float: right;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .btn-payout:hover { background: var(--primary); transform: translateY(-3px); box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2); }
    </style>
</head>
<body>

    <div class="container">
        <a href="store_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Dashboard</a>

        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:40px;">
            <div>
                <h1 style="font-size: 3rem; margin-bottom: 5px; font-family: 'Playfair Display', serif; background: linear-gradient(to bottom, var(--text-main), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">My Wallet</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Transparency in earnings and platform commissions.</p>
            </div>
            <button class="btn-payout"><i class="fas fa-hand-holding-usd"></i> Request Payout</button>
        </div>

        <div class="wallet-header">
            <div class="stat-card" style="border-left: 4px solid var(--primary);">
                <div class="stat-label">Current Balance</div>
                <div class="stat-value" style="color: var(--primary);">₹<?= number_format($wallet['balance'], 2) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Earned</div>
                <div class="stat-value">₹<?= number_format($stats['total_credited'] ?? 0, 2) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Platform Commission (10%)</div>
                <div class="stat-value" style="color: #ef4444;">₹<?= number_format($stats['total_commission'] ?? 0, 2) ?></div>
            </div>
        </div>

        <div class="main-card">
            <h3 style="margin-bottom: 25px;"><i class="fas fa-history" style="color:var(--primary)"></i> Recent Transactions</h3>
            <?php if (empty($transactions)): ?>
                <div style="text-align:center; color:var(--text-dim); padding:40px;">No transactions yet. Complete sales to see your balance grow!</div>
            <?php else: ?>
                <table class="tx-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Commission</th>
                            <th>Net Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td style="color: var(--text-muted);"><?= date('M d, Y', strtotime($tx['created_at'])) ?></td>
                                <td style="font-weight:600; color: var(--text-main);"><?= htmlspecialchars($tx['description']) ?></td>
                                <td>
                                    <span class="type-pill type-<?= $tx['type'] ?>">
                                        <?= $tx['type'] ?>
                                    </span>
                                </td>
                                <td style="color: #ef4444; font-weight:600;">
                                    <?= $tx['commission_deducted'] > 0 ? '-₹'.number_format($tx['commission_deducted'], 2) : '--' ?>
                                </td>
                                <td style="font-weight:800; color: <?= $tx['type'] === 'credit' ? 'var(--primary)' : 'var(--text-main)' ?>;">
                                    <?= ($tx['type'] === 'credit' ? '+' : '-') ?>₹<?= number_format($tx['amount'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
