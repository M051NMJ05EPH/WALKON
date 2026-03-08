<?php
session_start();
include 'config.php';
include 'includes/auth_check.php';
require_once 'includes/activity_logger.php';

// Auth Check
if (!isset($_SESSION['user_id']) || !isSeller()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$logger = new ActivityLogger($pdo);

// Filters
$action_filter = $_GET['action'] ?? '';
$search = trim($_GET['search'] ?? '');

try {
    $sql = "SELECT ual.*, u.first_name, u.last_name, u.email 
            FROM user_activity_logs ual 
            JOIN users u ON ual.user_id = u.id 
            WHERE 1=1";
    
    $params = [];
    
    // Non-admins only see their own logs
    if ($role !== 'admin') {
        $sql .= " AND ual.user_id = ?";
        $params[] = $user_id;
    }

    if ($action_filter) {
        $sql .= " AND ual.action = ?";
        $params[] = $action_filter;
    }

    if ($search) {
        $sql .= " AND (ual.action LIKE ? OR ual.details LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY ual.created_at DESC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unique actions for filter
    $stmt_actions = $pdo->query("SELECT DISTINCT action FROM user_activity_logs ORDER BY action");
    $all_actions = $stmt_actions->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Error fetching logs: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Monitor - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --bg: #030712;
            --card-bg: #111827;
            --text-dark: #F3F4F6;
            --text-light: #9CA3AF;
            --border: rgba(255, 255, 255, 0.08);
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { background: var(--bg); color: var(--text-dark); min-height: 100vh; }

        .header {
            background: #0B0F19;
            padding: 40px 20px;
            text-align: center;
            border-bottom: 1px solid var(--border);
            position: relative;
        }
        .back-nav {
            position: absolute; top: 45px; left: 40px;
            display: flex; align-items: center; gap: 10px;
            color: var(--text-light); text-decoration: none; font-weight: 600;
            transition: 0.3s;
        }
        .back-nav:hover { color: white; transform: translateX(-5px); }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 40px; }

        .search-section {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            padding: 25px;
            border-radius: 24px;
            border: 1px solid var(--border);
            margin-bottom: 30px;
            display: flex; gap: 15px; flex-wrap: wrap; align-items: center;
        }
        .input-group { flex: 1; position: relative; min-width: 300px; }
        .input-group i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
        .search-input { 
            width: 100%; padding: 12px 12px 12px 50px; 
            background: #0B0F19; border: 1px solid var(--border); 
            border-radius: 12px; color: white; 
        }

        .filter-select {
            padding: 12px 20px; background: #0B0F19; border: 1px solid var(--border);
            border-radius: 12px; color: var(--text-light); cursor: pointer;
        }

        .btn-search {
            padding: 12px 25px; background: var(--primary); color: white;
            border: none; border-radius: 12px; font-weight: 700; cursor: pointer;
        }

        .logs-container {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .logs-table { width: 100%; border-collapse: collapse; text-align: left; }
        .logs-table th { 
            background: rgba(255,255,255,0.03); padding: 20px; 
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;
            color: var(--text-light); border-bottom: 1px solid var(--border);
        }
        .logs-table td { padding: 20px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
        .logs-table tr:hover { background: rgba(255,255,255,0.02); }

        .action-pill {
            display: inline-block; padding: 4px 10px; border-radius: 6px;
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
            background: rgba(59, 130, 246, 0.1); color: #60a5fa;
        }
        .action-login { background: rgba(16, 185, 129, 0.1); color: #34d399; }
        .action-stock { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
        .action-order { background: rgba(139, 92, 246, 0.1); color: #a78bfa; }

        .details-text { color: var(--text-light); font-size: 0.85rem; max-width: 500px; }
        .timestamp { color: var(--text-light); font-size: 0.8rem; }

        .empty-state { text-align: center; padding: 80px 20px; }
        .empty-state i { font-size: 3rem; color: var(--border); margin-bottom: 15px; }
    </style>
</head>
<body>

    <header class="header">
        <a href="dashboard.php" class="back-nav"><i class="fas fa-arrow-left"></i> Dashboard</a>
        <h1 style="font-size: 2rem; font-weight: 800;">Activity <span style="color: var(--primary);">Monitor</span></h1>
        <p style="color: var(--text-light); margin-top: 5px;">Security logs and system event tracking</p>
    </header>

    <div class="container">
        <div class="search-section">
            <form action="" method="GET" style="display: flex; gap: 15px; width: 100%; flex-wrap: wrap;">
                <div class="input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search by activity details..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <select name="action" class="filter-select">
                    <option value="">All Actions</option>
                    <?php foreach($all_actions as $act): ?>
                        <option value="<?php echo $act; ?>" <?php echo $action_filter == $act ? 'selected' : ''; ?>>
                            <?php echo ucwords(str_replace('_', ' ', $act)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-search">Filter Logs</button>
                <?php if ($search || $action_filter): ?>
                    <a href="activity_logs.php" style="color: #ef4444; text-decoration: none; font-weight: 600; padding: 12px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="logs-container">
            <?php if (count($logs) > 0): ?>
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <?php if ($role === 'admin'): ?><th>User</th><?php endif; ?>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): 
                            $action_class = '';
                            if (strpos($log['action'], 'login') !== false) $action_class = 'action-login';
                            elseif (strpos($log['action'], 'product') !== false) $action_class = 'action-stock';
                            elseif (strpos($log['action'], 'order') !== false) $action_class = 'action-order';
                        ?>
                            <tr>
                                <td class="timestamp"><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                <?php if ($role === 'admin'): ?>
                                    <td>
                                        <div style="font-weight: 600; color: white;"><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-light);"><?php echo htmlspecialchars($log['email']); ?></div>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <span class="action-pill <?php echo $action_class; ?>">
                                        <?php echo str_replace('_', ' ', $log['action']); ?>
                                    </span>
                                </td>
                                <td class="details-text"><?php echo htmlspecialchars($log['details']); ?></td>
                                <td style="color: var(--text-light); font-family: monospace; font-size: 0.8rem;"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h3>No activities recorded</h3>
                    <p style="color: var(--text-light);">Perform some actions in your store to see them appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
