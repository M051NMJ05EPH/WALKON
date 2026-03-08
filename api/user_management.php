<?php
session_start();
header('Content-Type: application/json');

require_once '../config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

// Require admin or store owner
try {
    requireRole(['admin', 'store_owner']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$logger = new ActivityLogger($pdo);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        createUser();
        break;
    case 'update':
        updateUser();
        break;
    case 'activate':
        toggleUserStatus(true);
        break;
    case 'deactivate':
        toggleUserStatus(false);
        break;
    case 'list':
        listUsers();
        break;
    case 'activity':
        getUserActivity();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function createUser() {
    global $pdo, $logger;
    
    try {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'staff';
        
        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            throw new Exception('All fields are required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters');
        }
        
        // Role validation - store owners can't create admins or other store owners
        $allowed_roles = ['staff'];
        if (isAdmin()) {
            $allowed_roles[] = 'store_owner';
        }
        
        if (!in_array($role, $allowed_roles)) {
            throw new Exception('Invalid role');
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already exists');
        }
        
        // Determine seller_id
        $seller_id = $_POST['seller_id'] ?? null;
        if (!isAdmin()) {
            // Store owners can only create users for their own store
            $stmt_owner = $pdo->prepare("SELECT seller_id FROM users WHERE id = ?");
            $stmt_owner->execute([$_SESSION['user_id']]);
            $owner_seller_id = $stmt_owner->fetchColumn();
            $seller_id = $owner_seller_id;
        }
        
        // Create user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password, role, seller_id, is_verified, is_active, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, TRUE, TRUE, NOW())
        ");
        
        $stmt->execute([$first_name, $last_name, $email, $hashed_password, $role, $seller_id]);
        $new_user_id = $pdo->lastInsertId();
        
        // Log activity
        $logger->logUserCreated($_SESSION['user_id'], $new_user_id, $email, $role);
        
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $new_user_id
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function updateUser() {
    global $pdo, $logger;
    
    try {
        $user_id = $_POST['user_id'] ?? 0;
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? '';
        
        if (!$user_id || empty($first_name) || empty($last_name) || empty($email) || empty($role)) {
            throw new Exception('All fields are required');
        }
        
        // Role validation
        $allowed_roles = ['staff'];
        if (isAdmin()) {
            $allowed_roles[] = 'store_owner';
        }
        
        if (!in_array($role, $allowed_roles)) {
            throw new Exception('Invalid role');
        }
        
        // Check if email is taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            throw new Exception('Email already in use');
        }
        
        // Determine seller_id update
        $seller_id = $_POST['seller_id'] ?? null;
        if (!isAdmin()) {
            // Keep existing seller_id for non-admins
            $stmt_cur = $pdo->prepare("SELECT seller_id FROM users WHERE id = ?");
            $stmt_cur->execute([$user_id]);
            $seller_id = $stmt_cur->fetchColumn();
        }
        
        // Update user
        $stmt = $pdo->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, email = ?, role = ?, seller_id = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$first_name, $last_name, $email, $role, $seller_id, $user_id]);
        
        // Log activity
        $changes = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'role' => $role
        ];
        $logger->logUserUpdated($_SESSION['user_id'], $user_id, $changes);
        
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function toggleUserStatus($activate) {
    global $pdo, $logger;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'] ?? 0;
        
        if (!$user_id) {
            throw new Exception('User ID required');
        }
        
        // Don't allow deactivating yourself
        if ($user_id == $_SESSION['user_id']) {
            throw new Exception('You cannot deactivate your own account');
        }
        
        // Get user details
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Update status
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->execute([$activate, $user_id]);
        
        // Log activity
        if ($activate) {
            $logger->logUserActivated($_SESSION['user_id'], $user_id, $user['email']);
        } else {
            $logger->logUserDeactivated($_SESSION['user_id'], $user_id, $user['email']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => $activate ? 'User activated successfully' : 'User deactivated successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function listUsers() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT u.id, u.first_name, u.last_name, u.email, u.role, u.is_active, u.is_verified, u.last_login, u.created_at, u.seller_id, s.business_name
            FROM users u
            LEFT JOIN sellers s ON u.seller_id = s.id
            ORDER BY u.created_at DESC
        ");
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'users' => $users]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getUserActivity() {
    global $pdo, $logger;
    
    try {
        $user_id = $_GET['user_id'] ?? 0;
        
        if (!$user_id) {
            throw new Exception('User ID required');
        }
        
        $activities = $logger->getRecentActivities($user_id, 50);
        
        echo json_encode(['success' => true, 'activities' => $activities]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
