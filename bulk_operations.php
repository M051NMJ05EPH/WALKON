<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Get the actual seller_id for this user
$stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
$stmt_seller->execute([$email]);
$seller = $stmt_seller->fetch();
$seller_id = $seller ? $seller['id'] : -1;

$message = "";
$msg_type = ""; // success or error

// ---------------------------------------------------------
// HANDLE BULK ACTIONS
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $selected_ids = isset($_POST['selected_ids']) ? $_POST['selected_ids'] : [];
    $action = $_POST['action_type'];
    $value = $_POST['action_value'] ?? '';

    if (empty($selected_ids)) {
        $message = "No products selected.";
        $msg_type = "error";
    } else {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $params = $selected_ids;
        $params[] = $seller_id; 

        try {
            $pdo->beginTransaction();
            $affected = 0;

            switch ($action) {
                case 'delete':
                    // In a normalized schema, we delete from product_base
                    // assuming constraints handle CASCADE for prices, stock, etc.
                    $sql = "DELETE FROM product_base WHERE id IN ($placeholders) AND seller_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $affected = $stmt->rowCount();
                    $message = "Selected products deleted successfully.";
                    $msg_type = "success";
                    break;

                case 'status_active':
                    $sql = "UPDATE product_base SET status = 'published' WHERE id IN ($placeholders) AND seller_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $affected = $stmt->rowCount();
                    $message = "Status updated to Published.";
                    $msg_type = "success";
                    break;

                case 'status_draft':
                    $sql = "UPDATE product_base SET status = 'draft' WHERE id IN ($placeholders) AND seller_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $affected = $stmt->rowCount();
                    $message = "Status updated to Draft.";
                    $msg_type = "success";
                    break;

                case 'price_percentage':
                    $percent = floatval($value);
                    $multiplier = 1 + ($percent / 100);
                    // Targeted at product_prices
                    $sql = "UPDATE product_prices pp 
                            JOIN product_base pb ON pp.product_id = pb.id 
                            SET pp.price = pp.price * ? 
                            WHERE pb.id IN ($placeholders) AND pb.seller_id = ?";
                    array_unshift($params, $multiplier); 
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $affected = $stmt->rowCount();
                    $message = "Prices updated by $percent%.";
                    $msg_type = "success";
                    break;
                
                case 'set_price':
                    $new_price = floatval($value);
                    $sql = "UPDATE product_prices pp 
                            JOIN product_base pb ON pp.product_id = pb.id 
                            SET pp.price = ? 
                            WHERE pb.id IN ($placeholders) AND pb.seller_id = ?";
                    array_unshift($params, $new_price);
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $affected = $stmt->rowCount();
                    $message = "Prices set to ₹" . number_format($new_price, 2);
                    $msg_type = "success";
                    break;

                case 'set_stock':
                    $new_qty = intval($value);
                    $sql = "UPDATE product_stock ps 
                            JOIN product_base pb ON ps.product_id = pb.id 
                            SET ps.quantity = ? 
                            WHERE pb.id IN ($placeholders) AND pb.seller_id = ?";
                    array_unshift($params, $new_qty);
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $affected = $stmt->rowCount();
                    $message = "Stock quantity updated.";
                    $msg_type = "success";
                    break;
            }

            // Log the operation
            if ($affected > 0) {
                $log_stmt = $pdo->prepare("INSERT INTO bulk_operations_log (seller_id, action_type, affected_count, action_value) VALUES (?, ?, ?, ?)");
                $log_stmt->execute([$seller_id, $action, $affected, $value]);
            }

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error performing action: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}

// ---------------------------------------------------------
// 2. FETCH PRODUCTS (Normalized Schema)
// ---------------------------------------------------------
$stmt = $pdo->prepare("
    SELECT pb.id, pb.name as product_name, ps.sku, pp.price, pst.quantity, pb.status,
           (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as image_url
    FROM product_base pb
    LEFT JOIN product_prices pp ON pb.id = pp.product_id
    LEFT JOIN product_skus ps ON pb.id = ps.product_id
    LEFT JOIN product_stock pst ON pb.id = pst.product_id
    WHERE pb.seller_id = ?
    ORDER BY pb.created_at DESC
");
$stmt->execute([$seller_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Operations - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #28a745; --bg: #f8f9fa; --text: #333; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg); color: var(--text); padding: 40px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px; }
        .header h1 { font-size: 28px; display:flex; align-items:center; gap:10px; }
        .back-link { color: #666; text-decoration: none; font-weight: 500; }
        
        .card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        
        /* Action Bar */
        .action-bar { 
            background: #f1f3f5; padding: 20px; border-radius: 12px; margin-bottom: 20px; 
            display: flex; gap: 15px; align-items: center; flex-wrap: wrap;
        }
        .action-select { padding: 10px; border-radius: 8px; border: 1px solid #ddd; min-width: 200px; }
        .action-input { padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 150px; display: none; }
        .btn-apply { background: var(--primary); color: white; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-apply:hover { background: #218838; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 15px; color: #666; font-weight: 600; border-bottom: 2px solid #eee; background: #fafafa; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        .check-col { width: 40px; text-align: center; }
        
        .product-info { display: flex; align-items: center; gap: 15px; }
        .product-img { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; background: #eee; }
        
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-published { background: #d4edda; color: #155724; }
        .badge-draft { background: #fff3cd; color: #856404; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-layer-group" style="color:var(--primary);"></i> Bulk Operations</h1>
        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>

    <?php if($message): ?>
        <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="card">
            <div class="action-bar">
                <strong style="color:#555;">Bulk Actions:</strong>
                <select name="action_type" class="action-select" id="actionSelect" onchange="toggleInput()" required>
                    <option value="">Select Action...</option>
                    <option value="status_active">Set Status: Published</option>
                    <option value="status_draft">Set Status: Draft</option>
                    <option value="set_price">Price: Set Exact Value</option>
                    <option value="price_percentage">Price: Change by % (e.g. 10 or -10)</option>
                    <option value="set_stock">Stock: Set Quantity</option>
                    <option value="delete" style="color:red; font-weight:bold;">Delete Selected</option>
                </select>
                
                <input type="number" step="0.01" name="action_value" id="actionInput" class="action-input" placeholder="Value">
                
                <button type="submit" name="bulk_action" class="btn-apply" onclick="return confirmAction()">Apply to Selected</button>
            </div>
            
            <?php if (count($products) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th class="check-col"><input type="checkbox" onclick="toggleAll(this)"></th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Current Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): 
                             $img = $p['image_url'] ? $p['image_url'] : 'https://via.placeholder.com/40';
                        ?>
                        <tr>
                            <td class="check-col">
                                <input type="checkbox" name="selected_ids[]" value="<?php echo $p['id']; ?>" class="row-checkbox">
                            </td>
                            <td>
                                <div class="product-info">
                                    <img src="<?php echo htmlspecialchars($img); ?>" class="product-img" alt="img">
                                    <strong><?php echo htmlspecialchars($p['product_name']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($p['sku']); ?></td>
                            <td>₹<?php echo number_format($p['price'], 2); ?></td>
                            <td><?php echo $p['quantity']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($p['status']); ?>">
                                    <?php echo htmlspecialchars($p['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                 <div style="text-align:center; padding:40px; color:#888;">
                    No products found. <a href="add_listing.php">Add a product first</a>.
                </div>
            <?php endif; ?>
        </div>
    </form>

    <!-- Recent Activity Section -->
    <?php
    $log_stmt = $pdo->prepare("SELECT * FROM bulk_operations_log WHERE seller_id = ? ORDER BY created_at DESC LIMIT 10");
    $log_stmt->execute([$seller_id]);
    $logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="card" style="margin-top: 40px;">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-history"></i> Recent Activity</h3>
        <?php if (count($logs) > 0): ?>
            <table style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Affected</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></td>
                            <td><span style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $log['action_type']); ?></span></td>
                            <td><?php echo $log['affected_count']; ?> items</td>
                            <td><?php echo $log['action_value'] ? htmlspecialchars($log['action_value']) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#888; text-align:center;">No recent activity found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    // Initialize state on load
    document.addEventListener('DOMContentLoaded', function() {
        toggleInput();
    });

    function toggleAll(source) {
        const checkboxes = document.getElementsByClassName('row-checkbox');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    function toggleInput() {
        const select = document.getElementById('actionSelect');
        const input = document.getElementById('actionInput');
        if (!select || !input) return;
        
        const val = select.value;
        
        if (val === 'set_price' || val === 'price_percentage' || val === 'set_stock') {
            input.style.display = 'block';
            input.required = true;
            if (val === 'price_percentage') input.placeholder = "Percent (+/-)";
            else if (val === 'set_price') input.placeholder = "Price (₹)";
            else input.placeholder = "Qty";
        } else {
            input.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }

    function confirmAction() {
        const actionSelect = document.getElementById('actionSelect');
        const action = actionSelect ? actionSelect.value : '';
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert("Please select at least one product.");
            return false;
        }
        
        if (action === "") {
            alert("Please select an action from the dropdown.");
            if (actionSelect) actionSelect.focus();
            return false;
        }
        
        if (action === 'delete') {
            return confirm("Are you sure you want to PERMANENTLY DELETE " + checkboxes.length + " items? This cannot be undone.");
        }
        return true;
    }
</script>

</body>
</html>
