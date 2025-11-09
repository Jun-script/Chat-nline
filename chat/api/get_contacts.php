<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch contacts (friends) of the logged-in user
    // We join the contacts table with the users table to get friend's details
    $stmt = $pdo->prepare(
        "SELECT u.user_id, u.username, u.last_seen 
         FROM contacts c
         JOIN users u ON c.friend_id = u.user_id
         WHERE c.user_id = ?"
    );
    $stmt->execute([$user_id]);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'contacts' => $contacts]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
