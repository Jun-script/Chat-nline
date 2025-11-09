<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters long.']);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This email address is already registered.']);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Generate a unique friendship code
    $friendship_code = bin2hex(random_bytes(8));

    // Insert the new user into the database
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password, friendship_code) VALUES (?, ?, ?, ?)"
    );
    
    if ($stmt->execute([$username, $email, $hashed_password, $friendship_code])) {
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! Redirecting to login...']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred. Please try again.']);
    }

} catch (PDOException $e) {
    // In a real application, you would log this error, not expose it to the user.
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again later.']);
}
?>