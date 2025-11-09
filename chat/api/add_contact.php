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

$user_id = $_SESSION['user_id'];
$friendship_code = trim($_POST['friendship_code'] ?? '');

if (empty($friendship_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Friendship code is required.']);
    exit;
}

try {
    // 1. Find the user with the provided friendship code
    $stmt = $pdo->prepare("SELECT user_id, username FROM users WHERE friendship_code = ?");
    $stmt->execute([$friendship_code]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$friend) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid friendship code.']);
        exit;
    }

    $friend_id = $friend['user_id'];

    // 2. Prevent adding self
    if ($user_id == $friend_id) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot add yourself as a contact.']);
        exit;
    }

    // 3. Check if already a contact
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE user_id = ? AND friend_id = ?");
    $stmt->execute([$user_id, $friend_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This user is already in your contacts.']);
        exit;
    }

    // 4. Add mutual contacts in a transaction
    $pdo->beginTransaction();

    // Add friend to current user's contacts
    $stmt = $pdo->prepare("INSERT INTO contacts (user_id, friend_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $friend_id]);

    // Add current user to friend's contacts (mutual contact)
    $stmt = $pdo->prepare("INSERT INTO contacts (user_id, friend_id) VALUES (?, ?)");
    $stmt->execute([$friend_id, $user_id]);

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => $friend['username'] . ' added to your contacts.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
