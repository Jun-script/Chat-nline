<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response['message'] = 'Email and password are required.';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, username, password, friendship_code FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['friendship_code'] = $user['friendship_code'];
            
            // Update last_seen timestamp
            $update_stmt = $pdo->prepare("UPDATE users SET last_seen = CURRENT_TIMESTAMP WHERE user_id = ?");
            $update_stmt->execute([$user['user_id']]);

            $response['status'] = 'success';
            $response['message'] = 'Login successful!';
            $response['user'] = [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'friendship_code' => $user['friendship_code']
            ];
        } else {
            $response['message'] = 'Invalid email or password.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>