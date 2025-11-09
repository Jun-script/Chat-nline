<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    // Check if username or email already exists
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'Username or email already exists.';
            echo json_encode($response);
            exit;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate a unique friendship code
    $friendship_code = bin2hex(random_bytes(8)); // 16 character hex string
    while (true) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE friendship_code = ?");
            $stmt->execute([$friendship_code]);
            if ($stmt->fetchColumn() === 0) {
                break; // Code is unique
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error during friendship code generation: ' . $e->getMessage();
            echo json_encode($response);
            exit;
        }
        $friendship_code = bin2hex(random_bytes(8)); // Generate new code if not unique
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, friendship_code) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password, $friendship_code])) {
            $response['status'] = 'success';
            $response['message'] = 'Registration successful!';
            // Optionally, log the user in immediately
            // $_SESSION['user_id'] = $pdo->lastInsertId();
            // $_SESSION['username'] = $username;
        } else {
            $response['message'] = 'Registration failed. Please try again.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>