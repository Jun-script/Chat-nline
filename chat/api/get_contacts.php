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

try {
    $stmt = $pdo->prepare("
        SELECT 
            u.user_id, 
            u.username, 
            u.email,
            u.last_seen
        FROM contacts c
        JOIN users u ON c.friend_id = u.user_id
        WHERE c.user_id = ?
        ORDER BY u.username ASC
    ");
    $stmt->execute([$current_user_id]);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['status'] = 'success';
    $response['message'] = 'Contacts retrieved successfully.';
    $response['contacts'] = $contacts;

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>