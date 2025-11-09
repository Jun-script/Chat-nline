<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    exit;
}

if (isset($_GET['to'])) {
    $fromUserId = $_SESSION['id'];
    $toUserId = $_GET['to'];

    $sql = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "iiii", $fromUserId, $toUserId, $toUserId, $fromUserId);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $messages = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $messages[] = $row;
            }
            echo json_encode($messages);
        } else {
            http_response_code(500);
        }
        mysqli_stmt_close($stmt);
    } else {
        http_response_code(500);
    }
    mysqli_close($link);
} else {
    http_response_code(400);
}
?>
