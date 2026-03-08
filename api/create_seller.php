<?php
// api/create_seller.php - Backend for Admin Vendor Onboarding
session_start();
include '../config.php';

header('Content-Type: application/json');

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Get Data
$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$business_name = trim($data['business_name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = $data['password'] ?? '';
$website_url = trim($data['website_url'] ?? '');
$city = trim($data['city'] ?? '');
$country = trim($data['country'] ?? '');
$is_verified = isset($data['is_verified']) && $data['is_verified'] ? 1 : 0;

// Basic Validation
if (empty($name) || empty($business_name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Check if user already exists in users or sellers
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A user with this email already exists.']);
        exit();
    }

    // 2. Create Seller Entry
    $stmt = $pdo->prepare("INSERT INTO sellers (name, business_name, email, password, phone, website_url, city, country, is_verified, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([$name, $business_name, $email, $password, $phone, $website_url, $city, $country, $is_verified]);
    $seller_id = $pdo->lastInsertId();

    // 3. Create User Account for Login
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Split name into first and last
    $name_parts = explode(' ', $name);
    $first_name = $name_parts[0];
    $last_name = isset($name_parts[1]) ? implode(' ', array_slice($name_parts, 1)) : '';

    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, is_active, seller_id) VALUES (?, ?, ?, ?, 'store_owner', 1, ?)");
    $stmt->execute([$first_name, $last_name, $email, $hashed_password, $seller_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Vendor onboarded successfully.']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>
