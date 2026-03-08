<?php
session_start();
include 'config.php';

// Authentication & Role Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['store_owner', 'entrepreneur'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['seller_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_brand'])) {
    $brand_id = $_POST['brand_id'];
    $cert_url = $_POST['certificate_url'];

    try {
        // Check if already requested
        $check = $pdo->prepare("SELECT id FROM brand_approvals WHERE seller_id = ? AND brand_id = ?");
        $check->execute([$seller_id, $brand_id]);
        
        if ($check->fetch()) {
            $message = "<div class='alert alert-error'>Authorization for this brand is already pending or processed.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO brand_approvals (brand_id, seller_id, certificate_url, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$brand_id, $seller_id, $cert_url]);
            $message = "<div class='alert alert-success'>Request submitted successfully! Admin will review your documents.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fetch all available brands
$brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch current requests
$my_requests = $pdo->prepare("
    SELECT ba.*, b.name as brand_name 
    FROM brand_approvals ba 
    JOIN brands b ON ba.brand_id = b.id 
    WHERE ba.seller_id = ?
");
$my_requests->execute([$seller_id]);
$requests = $my_requests->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Authorization | WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --bg: #030712;
            --card-bg: #111827;
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { background: var(--bg); color: var(--text-main); min-height: 100vh; padding: 40px; }

        .container { max-width: 900px; margin: 0 auto; }
        .header { margin-bottom: 40px; }
        .header h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .header p { color: var(--text-dim); }

        .auth-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 30px;
            border: 1px solid var(--border);
            margin-bottom: 40px;
        }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-dim); }
        select, input {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border);
            color: #fff;
            outline: none;
            font-size: 1rem;
        }
        select:focus, input:focus { border-color: var(--primary); }

        .btn-submit {
            background: var(--primary);
            color: #000;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            transition: 0.3s;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); }

        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: rgba(16, 185, 129, 0.1); color: var(--primary); border: 1px solid var(--primary); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }

        .request-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .request-table th { text-align: left; padding: 15px; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        .request-table td { padding: 15px; border-bottom: 1px solid var(--border); }

        .status-pill {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .status-approved { background: rgba(16, 185, 129, 0.1); color: var(--primary); }
        .status-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--text-dim); text-decoration: none; margin-bottom: 20px; transition: 0.3s; }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body>

    <div class="container">
        <a href="store_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Dashboard</a>
        
        <header class="header">
            <h1>Brand Authorization</h1>
            <p>Sellers on WALKON must be authorized to list products from specific brands. Submit your credentials below.</p>
        </header>

        <?= $message ?>

        <div class="auth-card">
            <h3 style="margin-bottom: 20px;">New Authorization Request</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Select Brand</label>
                    <select name="brand_id" required>
                        <option value="">-- Choose Brand --</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Document / Certificate URL</label>
                    <input type="url" name="certificate_url" placeholder="https://example.com/certificate.pdf" required>
                    <p style="font-size:0.75rem; color:var(--text-dim); mt-1">Upload your brand authorization document to a cloud drive and paste the link here.</p>
                </div>
                <button type="submit" name="request_brand" class="btn-submit">Submit Request</button>
            </form>
        </div>

        <div class="auth-card">
            <h3 style="margin-bottom: 20px;">My Requests Status</h3>
            <?php if (empty($requests)): ?>
                <p style="color: var(--text-dim); text-align: center; padding: 20px;">No requests found.</p>
            <?php else: ?>
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Status</th>
                            <th>Submitted On</th>
                            <th>Admin Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td style="font-weight: 700;"><?= htmlspecialchars($r['brand_name']) ?></td>
                                <td>
                                    <span class="status-pill status-<?= $r['status'] ?>">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-dim); font-size: 0.9rem;"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                <td style="font-size: 0.85rem; color: var(--text-dim);"><?= htmlspecialchars($r['admin_feedback'] ?: '--') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
