<?php
session_start();
header("Content-Type: application/json");
require_once '../config.php';

$db = getDB();
$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $username = $db->real_escape_string($data['username']);
    $password = $data['password'];

    $result = $db->query("SELECT * FROM users WHERE username = '$username'");
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo json_encode(["success" => true, "user" => ["username" => $user['username']]]);
            exit;
        }
    }
    echo json_encode(["success" => false, "message" => "Invalid username or password"]);
}
elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(["success" => true]);
}
elseif ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode(["authenticated" => true, "username" => $_SESSION['username']]);
    }
    else {
        echo json_encode(["authenticated" => false]);
    }
}
elseif ($action === 'reset_password') {
    $username = $db->real_escape_string($data['username']);
    $newPassword = $data['new_password'];

    $result = $db->query("SELECT id FROM users WHERE username = '$username'");
    if ($result->num_rows === 1) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $db->query("UPDATE users SET password = '$hashedPassword' WHERE username = '$username'");
        if ($update) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Database update failed."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Account not found."]);
    }
}
elseif ($action === 'register') {
    $username = trim($db->real_escape_string($data['username']));
    $password = $data['password'];

    if (strlen($username) < 3) {
        echo json_encode(["success" => false, "message" => "Username must be at least 3 characters."]);
        exit;
    }
    if (strlen($password) < 6) {
        echo json_encode(["success" => false, "message" => "Password must be at least 6 characters."]);
        exit;
    }

    // Check if username already exists
    $check = $db->query("SELECT id FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Username already taken. Choose another."]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insert = $db->query("INSERT INTO users (username, password) VALUES ('$username', '$hashedPassword')");
    if ($insert) {
        echo json_encode(["success" => true, "message" => "Account created successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to create account. Please try again."]);
    }
}
?>
