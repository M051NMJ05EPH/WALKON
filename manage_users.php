<?php
session_start();
require_once 'config.php';
require_once 'includes/auth_check.php';
require_once 'includes/activity_logger.php';

// Require admin or store owner role
requireRole(['admin', 'store_owner']);

$logger = new ActivityLogger($pdo);
$current_user_name = getCurrentUserName();

// Fetch all users
try {
    $stmt = $pdo->query("
        SELECT u.id, u.first_name, u.last_name, u.email, u.role, u.is_active, u.is_verified, u.last_login, u.created_at, u.seller_id, s.business_name
        FROM users u 
        LEFT JOIN sellers s ON u.seller_id = s.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all sellers for dropdown (admins only)
    $sellers = [];
    if (isAdmin()) {
        $stmt_sellers = $pdo->query("SELECT id, business_name FROM sellers WHERE is_active = 1 ORDER BY business_name ASC");
        $sellers = $stmt_sellers->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $users = [];
    $error = "Failed to fetch data: " . $e->getMessage();
}

// Count users by role
$user_stats = [
    'total' => count($users),
    'active' => count(array_filter($users, fn($u) => $u['is_active'])),
    'inactive' => count(array_filter($users, fn($u) => !$u['is_active'])),
    'admin' => count(array_filter($users, fn($u) => $u['role'] === 'admin')),
    'store_owner' => count(array_filter($users, fn($u) => $u['role'] === 'store_owner')),
    'store_owner' => count(array_filter($users, fn($u) => $u['role'] === 'store_owner')),
    'staff' => count(array_filter($users, fn($u) => $u['role'] === 'staff')),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - WALKON Platform</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #10b981;
            --primary-light: #34d399;
            --primary-dark: #059669;
            --dark-bg: #0B0F19;
            --dark-card: #151B2B;
            --dark-border: #2A3241;
            --text-main: #F1F5F9;
            --text-muted: #94A3B8;
            --font-heading: 'Playfair Display', serif;
            --font-body: 'Inter', sans-serif;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        
        body { 
            font-family: var(--font-body); 
            background: var(--dark-bg); 
            color: var(--text-main); 
            line-height: 1.6; 
        }

        /* Navbar */
        .navbar {
            background: rgba(5, 7, 10, 0.95);
            backdrop-filter: blur(20px);
            position: fixed; width: 100%; top: 0; z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            height: 80px;
        }
        .nav-container {
            max-width: 1600px; margin: 0 auto; padding: 0 2rem; height: 100%;
            display: flex; justify-content: space-between; align-items: center;
        }
        
        .logo-box {
            display: flex; align-items: center; gap: 12px; text-decoration: none;
        }
        .logo-box img { height: 35px; width: auto; }
        .logo-box .brand-name {
            font-size: 1.5rem; font-weight: 700; color: white; letter-spacing: -0.5px;
        }
        .logo-box .brand-name span { color: var(--primary); }

        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a { 
            text-decoration: none; font-weight: 500; font-size: 0.9rem;
            color: #e2e8f0; transition: 0.3s;
        }
        .nav-links a:hover { color: var(--primary); }

        .role-badge {
            background: var(--primary);
            color: #000;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Container */
        .container {
            max-width: 1600px;
            margin: 120px auto 60px;
            padding: 0 2rem;
        }

        /* Header */
        .page-header {
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 1.05rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--dark-card);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--dark-border);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Actions Bar */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 20px;
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .search-box i {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .btn-primary {
            background: var(--primary);
            color: #000;
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Table */
        .table-container {
            background: var(--dark-card);
            border-radius: 16px;
            border: 1px solid var(--dark-border);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(16, 185, 129, 0.05);
        }

        th {
            padding: 18px 24px;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--dark-border);
        }

        td {
            padding: 18px 24px;
            border-bottom: 1px solid var(--dark-border);
            color: var(--text-main);
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: #000;
        }

        .user-details h4 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .user-details p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-admin {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .badge-store-owner {
            background: rgba(16, 185, 129, 0.2);
            color: var(--primary);
        }


        .badge-staff {
            background: rgba(168, 85, 247, 0.2);
            color: #a855f7;
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.2);
            color: var(--primary);
        }

        .badge-inactive {
            background: rgba(156, 163, 175, 0.2);
            color: #9ca3af;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--dark-border);
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-icon:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(16, 185, 129, 0.1);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 30px;
        }

        .modal-header h2 {
            font-size: 1.8rem;
            color: white;
            margin-bottom: 8px;
        }

        .modal-header p {
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--dark-border);
            border-radius: 10px;
            color: white;
            font-size: 0.95rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn-secondary {
            flex: 1;
            padding: 12px 24px;
            background: transparent;
            border: 1px solid var(--dark-border);
            color: var(--text-main);
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            table { font-size: 0.85rem; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo-box">
                <img src="assets/shoe_logo_green.png" alt="WalkOn">
                <div class="brand-name">Walk<span>on</span></div>
            </a>
            
            <div class="nav-links">
                <span class="role-badge">
                    <?= isAdmin() ? '👑 Admin' : '🟢 Store Owner' ?>
                </span>
                <a href="<?= isAdmin() ? 'dashboard.php' : 'store_owner_dashboard.php' ?>">Dashboard</a>
                <a href="manage_users.php" style="color: var(--primary);">Users</a>
                <a href="store_settings.php">Settings</a>
                <a href="logout.php">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>User Management</h1>
            <p>Manage staff accounts, roles, and permissions</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $user_stats['total'] ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $user_stats['active'] ?></div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $user_stats['store_owner'] ?></div>
                <div class="stat-label">Store Owners</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $user_stats['staff'] ?></div>
                <div class="stat-label">Staff</div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search users by name or email...">
                <i class="fas fa-search"></i>
            </div>
            <button class="btn-primary" onclick="openAddUserModal()">
                <i class="fas fa-user-plus"></i>
                Add New User
            </button>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Store</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                        $full_name = trim($user['first_name'] . ' ' . $user['last_name']) ?: 'No Name';
                        
                        $role_class = match($user['role']) {
                            'admin' => 'badge-admin',
                            'store_owner' => 'badge-store-owner',
                            'staff' => 'badge-staff',
                            default => 'badge-staff'
                        };
                        
                        $role_label = str_replace('_', ' ', ucwords($user['role'], '_'));
                    ?>
                    <tr data-user-id="<?= $user['id'] ?>" data-email="<?= htmlspecialchars($user['email']) ?>">
                        <td>
                            <div class="user-info">
                                <div class="user-avatar"><?= $initials ?></div>
                                <div class="user-details">
                                    <h4><?= htmlspecialchars($full_name) ?></h4>
                                    <p><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">
                                <?= $user['business_name'] ? htmlspecialchars($user['business_name']) : '<em style="color:#ef4444">No Store</em>' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $role_class ?>">
                                <?= $role_label ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $user['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <?= $user['last_login'] ? date('M d, y H:i', strtotime($user['last_login'])) : 'Never' ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-icon" onclick="editUser(<?= $user['id'] ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" onclick="toggleUserStatus(<?= $user['id'] ?>, <?= $user['is_active'] ? 'false' : 'true' ?>)" title="<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                    <i class="fas fa-power-off"></i>
                                </button>
                                <button class="btn-icon" onclick="viewActivity(<?= $user['id'] ?>)" title="View Activity">
                                    <i class="fas fa-history"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal" id="addUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <p>Create a new staff member account</p>
            </div>
            <form id="addUserForm" onsubmit="submitNewUser(event)">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required onchange="toggleStoreSelection(this.value)">
                        <?php if (isAdmin()): ?>
                            <option value="store_owner">Store Owner</option>
                        <?php endif; ?>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <?php if (isAdmin()): ?>
                <div class="form-group" id="storeSelectionGroup">
                    <label>Assign Store (Optional)</label>
                    <select name="seller_id">
                        <option value="">Select a Store...</option>
                        <?php foreach ($sellers as $seller): ?>
                            <option value="<?= $seller['id'] ?>"><?= htmlspecialchars($seller['business_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeAddUserModal()">Cancel</button>
                    <button type="submit" class="btn-primary" style="flex: 1;">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTable tbody tr');
            
            rows.forEach(row => {
                const email = row.dataset.email.toLowerCase();
                const nameEl = row.querySelector('.user-details h4');
                const name = nameEl ? nameEl.textContent.toLowerCase() : '';
                
                if (email.includes(searchTerm) || name.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Modal functions
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.add('active');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.remove('active');
            document.getElementById('addUserForm').reset();
        }

        // Submit new user
        async function submitNewUser(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('api/user_management.php?action=create', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('User created successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('System error. Please try again.');
            }
        }

        // Toggle user status
        async function toggleUserStatus(userId, activate) {
            const action = activate ? 'activate' : 'deactivate';
            if (!confirm(`Are you sure you want to ${action} this user?`)) return;
            
            try {
                const response = await fetch(`api/user_management.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('System error. Please try again.');
            }
        }

        // Edit user
        function editUser(userId) {
            alert('Edit functionality coming soon!');
        }

        // View activity
        function viewActivity(userId) {
            window.location.href = `user_activity.php?user_id=${userId}`;
        }

        // Toggle store selection visibility
        function toggleStoreSelection(role) {
            const group = document.getElementById('storeSelectionGroup');
            if (group) {
                // If it's a store owner being created by admin, they might need a seller record first
                // For staff/managers, it's definitely needed.
                group.style.display = 'block';
            }
        }
    </script>

</body>
</html>
