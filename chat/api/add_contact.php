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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $friendship_code = trim($_POST['friendship_code'] ?? '');

    if (empty($friendship_code)) {
        $response['message'] = 'Friendship code is required.';
        echo json_encode($response);
        exit;
    }

    try {
        // 1. Find the friend's user_id using the friendship_code
        $stmt = $pdo->prepare("SELECT user_id, username FROM users WHERE friendship_code = ?");
        $stmt->execute([$friendship_code]);
        $friend = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$friend) {
            $response['message'] = 'No user found with that friendship code.';
            echo json_encode($response);
            exit;
        }

        $friend_id = $friend['user_id'];

        // 2. Check if the friend is the current user
        if ($friend_id == $current_user_id) {
            $response['message'] = 'You cannot add yourself as a contact.';
            echo json_encode($response);
            exit;
        }

        // 3. Check if the contact already exists (current user added friend)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE user_id = ? AND friend_id = ?");
        $stmt->execute([$current_user_id, $friend_id]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'This user is already in your contacts.';
            echo json_encode($response);
            exit;
        }
        
        // 4. Check if the contact already exists (friend added current user)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE user_id = ? AND friend_id = ?");
        $stmt->execute([$friend_id, $current_user_id]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'This user has already added you as a contact.';
            echo json_encode($response);
            exit;
        }

        // 5. Add mutual contact
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO contacts (user_id, friend_id) VALUES (?, ?)");
        $stmt->execute([$current_user_id, $friend_id]);

        $stmt = $pdo->prepare("INSERT INTO contacts (user_id, friend_id) VALUES (?, ?)");
        $stmt->execute([$friend_id, $current_user_id]);

        $pdo->commit();

        $response['status'] = 'success';
        $response['message'] = $friend['username'] . ' has been added to your contacts.';

    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>