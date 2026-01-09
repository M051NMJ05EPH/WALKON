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
        // Prepare placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        
        // Merge IDs with Seller ID for security
        $params = $selected_ids;
        $params[] = $seller_id; 

        try {
            switch ($action) {
                case 'delete':
                    $sql = "DELETE FROM products WHERE id IN ($placeholders) AND seller_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $message = "Selected products deleted successfully.";
                    $msg_type = "success";
                    break;

                case 'status_active':
                    $sql = "UPDATE products SET status = 'published' WHERE id IN ($placeholders) AND seller_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $message = "Status updated to Published.";
                    $msg_type = "success";
                    break;

                case 'status_draft':
                    $sql = "UPDATE products SET status = 'draft' WHERE id IN ($placeholders) AND seller_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $message = "Status updated to Draft.";
                    $msg_type = "success";
                    break;

                case 'price_percentage':
                    // Increase/Decrease price by percentage
                    $percent = floatval($value);
                    $multiplier = 1 + ($percent / 100);
                    $sql = "UPDATE products SET price = price * ? WHERE id IN ($placeholders) AND seller_id = ?";
                    // We need to put the multiplier at the start of params array
                    array_unshift($params, $multiplier); 
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $message = "Prices updated by $percent%.";
                    $msg_type = "success";
                    break;
                
                case 'set_price':
                    $new_price = floatval($value);
                    $sql = "UPDATE products SET price = ? WHERE id IN ($placeholders) AND seller_id = ?";
                    array_unshift($params, $new_price);
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $message = "Prices set to $$new_price.";
                    $msg_type = "success";
                    break;

                case 'set_stock':
                    $new_qty = intval($value);
                    $sql = "UPDATE products SET quantity = ? WHERE id IN ($placeholders) AND seller_id = ?";
                    array_unshift($params, $new_qty);
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $message = "Stock quantity updated.";
                    $msg_type = "success";
                    break;
            }
        } catch (PDOException $e) {
            $message = "Error performing action: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}

// ---------------------------------------------------------
// FETCH PRODUCTS
// ---------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
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
                             // Robust image selection
                             $images_raw = $p['images'];
                             $img = 'https://via.placeholder.com/40';

                             if (!empty($images_raw)) {
                                 $decoded = json_decode($images_raw, true);
                                 $candidates = is_array($decoded) ? $decoded : [$images_raw];
                                 
                                 foreach ($candidates as $url) {
                                     $is_local = (strpos($url, 'uploads/') === 0);
                                     $is_http = (strpos($url, 'http') === 0);
                                     
                                     $path = parse_url($url, PHP_URL_PATH);
                                     $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                     $is_image_ext = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                                     
                                     if (($is_local && file_exists($url)) || ($is_http && $is_image_ext)) {
                                         $img = $url;
                                         break;
                                     }
                                 }
                             }
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
