<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit;
}

try {
    // Find user by email
    $stmt = $pdo->prepare("SELECT user_id, username, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user and password
    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, start the session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        
        // Update last_seen timestamp
        $updateStmt = $pdo->prepare("UPDATE users SET last_seen = CURRENT_TIMESTAMP WHERE user_id = ?");
        $updateStmt->execute([$user['user_id']]);

        echo json_encode(['status' => 'success', 'message' => 'Login successful! Redirecting...']);
    } else {
        // Invalid credentials
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again later.']);
}
?>