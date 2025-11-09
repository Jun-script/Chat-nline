<?php
// api_handler.php
// This file handles all API requests. It is included by index.php when $_GET['action'] is set.

// Ensure session is started early
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Ensure database handle $pdo is available (include config if needed)
if (!isset($pdo)) {
    $configPath = __DIR__ . '/../config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    } else {
        // fallback error response for dev
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database configuration not found.']);
        exit;
    }
}

// set JSON header after session/config
header('Content-Type: application/json');

// use a single $action variable (avoid direct access to $_GET['action'])
$action = $_GET['action'] ?? '';

// Ensure user is logged in for most API actions, except login and register
$public_actions = ['login', 'register'];
if (!isset($_SESSION['user_id']) && !in_array($action, $public_actions, true)) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

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
            $country = trim($_POST['country'] ?? null);
            $gender = trim($_POST['gender'] ?? null);
            $dob = trim($_POST['dob'] ?? null); // expect YYYY-MM-DD or validate

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
            $friendship_code = bin2hex(random_bytes(8));

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, friendship_code, profile_image, country, gender, dob) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $default_profile = 'assets/img/user/default.jpg';
                if ($stmt->execute([$username, $email, $hashed_password, $friendship_code, $default_profile, $country, $gender, $dob])) {
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
        // DEBUG: geçici olarak hataları göster (dev ortamında)
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        header('Content-Type: application/json; charset=utf-8');
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            if (!isset($_SESSION['user_id'])) throw new Exception('Unauthorized');

            if (empty($_FILES['profile_picture']) || !is_uploaded_file($_FILES['profile_picture']['tmp_name'])) {
                throw new Exception('No file uploaded');
            }

            $file = $_FILES['profile_picture'];
            $tmp = $file['tmp_name'];

            // Validate image (finfo if available, otherwise getimagesize)
            $isImage = false;
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmp);
                finfo_close($finfo);
                if (strpos($mime, 'image/') === 0) $isImage = true;
            }
            if (!$isImage) {
                $info = @getimagesize($tmp);
                if ($info !== false && isset($info['mime']) && strpos($info['mime'], 'image/') === 0) {
                    $isImage = true;
                }
            }
            if (!$isImage) throw new Exception('Uploaded file is not a valid image');

            $user_id = (int) $_SESSION['user_id'];
            $upload_dir = __DIR__ . '/../assets/img/user/';
            if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
            if (!is_writable($upload_dir)) {
                // attempt to set writable for dev
                @chmod($upload_dir, 0755);
                if (!is_writable($upload_dir)) throw new Exception('Upload directory not writable: ' . $upload_dir);
            }

            $filename = $user_id . '.jpg';
            $target = $upload_dir . $filename;

            // Option: convert to jpeg to ensure consistent extension
            $imgData = file_get_contents($tmp);
            $img = @imagecreatefromstring($imgData);
            if ($img === false) {
                // fallback to moving original file
                if (!move_uploaded_file($tmp, $target)) throw new Exception('Failed to move uploaded file');
            } else {
                // save as JPEG
                if (!imagejpeg($img, $target, 90)) throw new Exception('Failed to save JPEG image');
                imagedestroy($img);
            }

            @chmod($target, 0644);
            $profile_url = 'assets/img/user/' . $filename;

            // DB update (use $pdo and correct column name)
            if (!isset($pdo)) throw new Exception('Database handle $pdo not available');
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
            if (!$stmt->execute([$profile_url, $user_id])) {
                throw new Exception('Database update failed');
            }

            $_SESSION['profile_image'] = $profile_url;

            echo json_encode([
                'status' => 'success',
                'message' => 'Profile picture uploaded and updated successfully.',
                'profile_picture_url' => $profile_url,
                'user_id' => $user_id
            ]);
        } catch (Exception $e) {
            error_log('upload_profile_picture error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
        break;

    case 'get_profile':
        if (!isset($_SESSION['user_id'])) {
            $response['message'] = 'Not logged in';
            echo json_encode($response);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT username, email, country, gender, dob, created_at, profile_image, friendship_code FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $response['status'] = 'success';
                $response['user'] = $user;
            } else {
                $response['message'] = 'User not found';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
        
        echo json_encode($response);
        exit;
        break;

    case 'update_profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $current_user_id = $_SESSION['user_id'] ?? null;
            if (!$current_user_id) {
                echo json_encode(['status'=>'error','message'=>'Not authenticated']);
                exit;
            }

            $username = trim($_POST['username'] ?? '');
            $country = trim($_POST['country'] ?? null);
            $gender = trim($_POST['gender'] ?? null);
            $dob = trim($_POST['dob'] ?? null);
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($username)) {
                echo json_encode(['status'=>'error','message'=>'Username is required']);
                exit;
            }

            try {
                // Check username uniqueness
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND user_id != ?");
                $stmt->execute([$username, $current_user_id]);
                if ($stmt->fetchColumn() > 0) {
                    echo json_encode(['status'=>'error','message'=>'Username already taken']);
                    exit;
                }

                $pdo->beginTransaction();

                // If changing password, validate
                if (!empty($new_password)) {
                    if ($new_password !== $confirm_password) {
                        echo json_encode(['status'=>'error','message'=>'New passwords do not match']);
                        $pdo->rollBack();
                        exit;
                    }
                    // verify current password
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
                    $stmt->execute([$current_user_id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$row || !password_verify($current_password, $row['password'])) {
                        echo json_encode(['status'=>'error','message'=>'Current password is incorrect']);
                        $pdo->rollBack();
                        exit;
                    }
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $stmt->execute([$hashed, $current_user_id]);
                }

                // Update other fields
                $stmt = $pdo->prepare("UPDATE users SET username = ?, country = ?, gender = ?, dob = ? WHERE user_id = ?");
                $stmt->execute([$username, $country, $gender, $dob ?: null, $current_user_id]);

                $pdo->commit();

                // Refresh session username
                $_SESSION['username'] = $username;

                // Return updated profile
                $stmt = $pdo->prepare("SELECT user_id, username, email, country, gender, dob, profile_image FROM users WHERE user_id = ?");
                $stmt->execute([$current_user_id]);
                $updated = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode(['status'=>'success','message'=>'Profile updated','user'=>$updated]);

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                echo json_encode(['status'=>'error','message'=>'Database error: '.$e->getMessage()]);
            }
            exit;
        } else {
            echo json_encode(['status'=>'error','message'=>'Invalid request method']);
            exit;
        }
        break;

    default:
        $response['message'] = 'Invalid API action.';
        break;
}

echo json_encode($response);
?>