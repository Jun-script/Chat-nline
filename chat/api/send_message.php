<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = trim($_POST['message'] ?? '');

if (!$receiver_id || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Receiver ID and message text are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)"
    );
    
    if ($stmt->execute([$sender_id, $receiver_id, $message])) {
        $new_message_id = $pdo->lastInsertId();
        echo json_encode([
            'status' => 'success', 
            'message' => 'Message sent successfully.',
            'new_message' => [
                'message_id' => $new_message_id,
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s') // Approximate time
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send message.']);
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
