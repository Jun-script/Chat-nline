<?php
// api_handler.php
// This file handles all API requests. It is included by index.php when $_GET['action'] is set.

header('Content-Type: application/json');

// Ensure user is logged in for most API actions, except login and register
$public_actions = ['login', 'register'];
if (!isset($_SESSION['user_id']) && !in_array($_GET['action'], $public_actions)) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

$action = $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $response['message'] = 'Email and password are required.';
                echo json_encode($response);
                exit;
            }

            try {
                $stmt = $pdo->prepare("SELECT user_id, username, password, friendship_code, profile_image FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['friendship_code'] = $user['friendship_code'];
                    $_SESSION['profile_image'] = $user['profile_image'];
                    
                    // Update last_seen timestamp
                    $update_stmt = $pdo->prepare("UPDATE users SET last_seen = CURRENT_TIMESTAMP WHERE user_id = ?");
                    $update_stmt->execute([$user['user_id']]);

                    $response['status'] = 'success';
                    $response['message'] = 'Login successful!';
                    $response['user'] = [
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'friendship_code' => $user['friendship_code'],
                        'profile_image' => $user['profile_image']
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
        break;

    case 'register':
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
                } else {
                    $response['message'] = 'Registration failed. Please try again.';
                }
            } catch (PDOException $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'Invalid request method.';
        }
        break;

    case 'logout':
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();

        // Redirect to login page after logout
        header('Location: index.php?page=login');
        exit;
        break;

    case 'add_contact':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $friendship_code = trim($_POST['friendship_code'] ?? '');

            if (empty($friendship_code)) {
                $response['message'] = 'Friendship code is required.';
                echo json_encode($response);
                exit;
            }

            $current_user_id = $_SESSION['user_id'];

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
        break;

    case 'get_contacts':
        $current_user_id = $_SESSION['user_id'];

        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    u.user_id, 
                    u.username, 
                    u.email,
                    u.last_seen,
                    u.profile_image,
                    (SELECT message FROM messages 
                     WHERE (sender_id = u.user_id AND receiver_id = :current_user_id_1) 
                        OR (sender_id = :current_user_id_2 AND receiver_id = u.user_id)
                     ORDER BY created_at DESC LIMIT 1) AS last_message,
                    (SELECT created_at FROM messages 
                     WHERE (sender_id = u.user_id AND receiver_id = :current_user_id_3) 
                        OR (sender_id = :current_user_id_4 AND receiver_id = u.user_id)
                     ORDER BY created_at DESC LIMIT 1) AS last_message_time,
                    (SELECT COUNT(*) FROM messages 
                     WHERE sender_id = u.user_id AND receiver_id = :current_user_id_5 AND is_read = 0) AS unread_count
                FROM users u
                WHERE u.user_id IN (
                    SELECT c.friend_id FROM contacts c WHERE c.user_id = :current_user_id_6
                    UNION
                    SELECT m.sender_id FROM messages m WHERE m.receiver_id = :current_user_id_7
                    UNION
                    SELECT m.receiver_id FROM messages m WHERE m.sender_id = :current_user_id_8
                ) AND u.user_id != :current_user_id_9
                ORDER BY last_message_time DESC, username ASC
            ");
            $stmt->execute([
                'current_user_id_1' => $current_user_id,
                'current_user_id_2' => $current_user_id,
                'current_user_id_3' => $current_user_id,
                'current_user_id_4' => $current_user_id,
                'current_user_id_5' => $current_user_id,
                'current_user_id_6' => $current_user_id,
                'current_user_id_7' => $current_user_id,
                'current_user_id_8' => $current_user_id,
                'current_user_id_9' => $current_user_id
            ]);
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['status'] = 'success';
            $response['message'] = 'Contacts retrieved successfully.';
            $response['contacts'] = $contacts;

        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
        break;

    case 'get_all_contacts':
        $current_user_id = $_SESSION['user_id'];

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    user_id, 
                    username, 
                    email,
                    last_seen,
                    profile_image
                FROM users
                WHERE user_id != ?
                ORDER BY username ASC
            ");
            $stmt->execute([$current_user_id]);
            $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['status'] = 'success';
            $response['message'] = 'All users retrieved successfully.';
            $response['contacts'] = $all_users;

        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
        break;

    case 'get_messages':
        $current_user_id = $_SESSION['user_id'];
        $friend_id = $_GET['user_id'] ?? null;

        if (empty($friend_id)) {
            $response['message'] = 'Friend ID is required.';
            echo json_encode($response);
            exit;
        }

        try {
            // Get friend's info
            $stmt = $pdo->prepare("SELECT user_id, username, last_seen, profile_image FROM users WHERE user_id = ?");
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
                    is_delivered, 
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
        break;

    case 'send_message':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sender_id = $_SESSION['user_id'];
            $receiver_id = $_POST['receiver_id'] ?? null;
            $message_text = trim($_POST['message'] ?? '');

            if (empty($receiver_id) || empty($message_text)) {
                $response['message'] = 'Receiver ID and message cannot be empty.';
                echo json_encode($response);
                exit;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, is_delivered) VALUES (?, ?, ?, 1)");
                if ($stmt->execute([$sender_id, $receiver_id, $message_text])) {
                    $last_insert_id = $pdo->lastInsertId();
                    $stmt = $pdo->prepare("SELECT * FROM messages WHERE message_id = ?");
                    $stmt->execute([$last_insert_id]);
                    $new_message = $stmt->fetch(PDO::FETCH_ASSOC);

                    $response['status'] = 'success';
                    $response['message'] = 'Message sent successfully.';
                    $response['new_message'] = $new_message;
                    $response['current_user_id'] = $sender_id;
                } else {
                    $response['message'] = 'Failed to send message.';
                }
            } catch (PDOException $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'Invalid request method.';
        }
        break;

    case 'mark_messages_as_read':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_user_id = $_SESSION['user_id'];
            $sender_id = $_POST['sender_id'] ?? null;

            if (empty($sender_id)) {
                $response['message'] = 'Sender ID is required.';
                echo json_encode($response);
                exit;
            }

            try {
                $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
                $stmt->execute([$sender_id, $current_user_id]);

                $response['status'] = 'success';
                $response['message'] = 'Messages marked as read.';
            } catch (PDOException $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'Invalid request method.';
        }
        break;

    case 'upload_profile_picture':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_user_id = $_SESSION['user_id'];

            if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
                $response['message'] = 'No file uploaded or upload error.';
                echo json_encode($response);
                exit;
            }

            $file_tmp_path = $_FILES['profile_picture']['tmp_name'];
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            // Ensure the extension is 'jpg' as requested, or convert if necessary
            // For simplicity, we'll assume the client sends a JPEG blob.
            $target_dir = '../assets/img/user/'; // Relative to api_handler.php
            $target_filename = $current_user_id . '.jpg';
            $target_file_path = $target_dir . $target_filename;

            // Create directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($file_tmp_path, $target_file_path)) {
                try {
                    $profile_picture_url = 'assets/img/user/' . $target_filename; // URL relative to chat/index.php
                    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
                    $stmt->execute([$profile_picture_url, $current_user_id]);

                    // Update the session variable
                    $_SESSION['profile_image'] = $profile_picture_url;

                    $response['status'] = 'success';
                    $response['message'] = 'Profile picture uploaded and updated successfully.';
                    $response['profile_picture_url'] = $profile_picture_url;
                } catch (PDOException $e) {
                    $response['message'] = 'Database error: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Failed to move uploaded file.';
            }
        } else {
            $response['message'] = 'Invalid request method.';
        }
        break;

    default:
        $response['message'] = 'Invalid API action.';
        break;
}

echo json_encode($response);
?>