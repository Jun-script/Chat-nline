<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_id = $_GET['user_id'] ?? null;

if (!$friend_id) {
    echo json_encode(['status' => 'error', 'message' => 'Friend ID is required.']);
    exit;
}

try {
    // Fetch messages between the logged-in user and the selected friend
    $stmt = $pdo->prepare(
        "SELECT message_id, sender_id, receiver_id, message, created_at 
         FROM messages
         WHERE (sender_id = :user_id AND receiver_id = :friend_id)
            OR (sender_id = :friend_id AND receiver_id = :user_id)
         ORDER BY created_at ASC"
    );
    $stmt->execute(['user_id' => $user_id, 'friend_id' => $friend_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark messages from the friend as read
    $updateStmt = $pdo->prepare(
        "UPDATE messages SET is_read = 1 
         WHERE sender_id = :friend_id AND receiver_id = :user_id AND is_read = 0"
    );
    $updateStmt->execute(['friend_id' => $friend_id, 'user_id' => $user_id]);
    
    // Also fetch friend's info for the header
    $friendStmt = $pdo->prepare("SELECT user_id, username FROM users WHERE user_id = ?");
    $friendStmt->execute([$friend_id]);
    $friend_info = $friendStmt->fetch(PDO::FETCH_ASSOC);


    echo json_encode([
        'status' => 'success', 
        'messages' => $messages,
        'friend_info' => $friend_info,
        'current_user_id' => $user_id
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
