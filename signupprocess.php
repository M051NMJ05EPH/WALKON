<?php
// signup-process.php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$password   = $_POST['password'] ?? '';
$confirm    = $_POST['confirm_password'] ?? '';

if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
    exit;
}

if ($password !== $confirm) {
    echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
    exit;
}

// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already registered']);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password]);

    // Auto login after signup
    $user_id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = "$first_name $last_name";
    $_SESSION['user_email'] = $email;

    echo json_encode(['success' => true, 'message' => 'Account created successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Registration failed. Try again.']);
}
?>