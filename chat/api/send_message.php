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

$sender_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'] ?? null;
    $message_text = trim($_POST['message'] ?? '');

    if (empty($receiver_id) || empty($message_text)) {
        $response['message'] = 'Receiver ID and message cannot be empty.';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$sender_id, $receiver_id, $message_text])) {
            $last_insert_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM messages WHERE message_id = ?");
            $stmt->execute([$last_insert_id]);
            $new_message = $stmt->fetch(PDO::FETCH_ASSOC);

            $response['status'] = 'success';
            $response['message'] = 'Message sent successfully.';
            $response['new_message'] = $new_message;
        } else {
            $response['message'] = 'Failed to send message.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>