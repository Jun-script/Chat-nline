<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$friend_id = $_GET['user_id'] ?? null;

if (empty($friend_id)) {
    $response['message'] = 'Friend ID is required.';
    echo json_encode($response);
    exit;
}

try {
    // Get friend's info
    $stmt = $pdo->prepare("SELECT user_id, username, last_seen FROM users WHERE user_id = ?");
    $stmt->execute([$friend_id]);
    $friend_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$friend_info) {
        $response['message'] = 'Friend not found.';
        echo json_encode($response);
        exit;
    }

    // Get messages between current user and friend
    $stmt = $pdo->prepare("
        SELECT 
            message_id, 
            sender_id, 
            receiver_id, 
            message, 
            is_read, 
            created_at
        FROM messages
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    ");
    $stmt->execute([$current_user_id, $friend_id, $friend_id, $current_user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['status'] = 'success';
    $response['message'] = 'Messages retrieved successfully.';
    $response['messages'] = $messages;
    $response['friend_info'] = $friend_info;
    $response['current_user_id'] = $current_user_id;

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>