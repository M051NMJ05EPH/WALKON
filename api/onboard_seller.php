<?php
// api/onboard_seller.php - Multichannel Vendor Onboarding from Sellers Ecosystem
session_start();
include '../config.php';

header('Content-Type: application/json');

// Auth Check — allow admins, entrepreneurs, and store owners
$allowed_roles = ['admin', 'entrepreneur', 'store', 'store_owner'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to onboard new vendors.']);
    exit();
}

// Get JSON Data
$data = json_decode(file_get_contents('php://input'), true);

$name          = trim($data['name'] ?? '');
$business_name = trim($data['business_name'] ?? '');
$email         = trim($data['email'] ?? '');
$password      = $data['password'] ?? '';
$phone         = trim($data['phone'] ?? '');
$city          = trim($data['city'] ?? '');
$tier          = trim($data['type'] ?? 'multi'); // single | multi | enterprise

// Validation
if (empty($name) || empty($business_name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Required fields: Enterprise Name, Master Associate, Identity Email, and Secure Key.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Secure Key must be at least 6 characters.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Check for duplicate email in users table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A vendor with this email already exists in the ecosystem.']);
        $pdo->rollBack();
        exit();
    }

    // 2. Check for duplicate email in sellers table
    $stmt = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered as a seller.']);
        $pdo->rollBack();
        exit();
    }

    // 3. Create Seller Entry
    // is_verified = 0 by default (can be approved by admin)
    $stmt = $pdo->prepare("INSERT INTO sellers (name, business_name, email, password, phone, city, is_verified, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, 1, NOW())");
    $stmt->execute([$name, $business_name, $email, $password, $phone, $city]);
    $seller_id = $pdo->lastInsertId();

    // 4. Create User Account for Login
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $name_parts = explode(' ', $name);
    $first_name = $name_parts[0];
    $last_name  = count($name_parts) > 1 ? implode(' ', array_slice($name_parts, 1)) : '';

    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, is_active, seller_id, created_at) VALUES (?, ?, ?, ?, 'store_owner', 1, ?, NOW())");
    $stmt->execute([$first_name, $last_name, $email, $hashed, $seller_id]);

    $pdo->commit();

    echo json_encode([
        'success'   => true,
        'message'   => 'Vendor successfully onboarded to the ecosystem.',
        'seller_id' => $seller_id,
        'tier'      => $tier
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
