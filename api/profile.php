<?php
session_start();
header("Content-Type: application/json");
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$db = getDB();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT username, display_name, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo json_encode([
        "success" => true,
        "profile" => [
            "username" => $user['username'],
            "display_name" => $user['display_name'] ?: $user['username'],
            "profile_picture" => $user['profile_picture']
        ]
    ]);
} elseif ($method === 'POST') {
    $displayName = $_POST['display_name'] ?? null;
    $profilePicture = null;

    if (!empty($displayName)) {
        $displayName = $db->real_escape_string($displayName);
    } else {
        $displayName = null;
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileInfo = pathinfo($_FILES['profile_picture']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($extension, $allowedExtensions)) {
            $newFileName = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                $profilePicture = 'uploads/' . $newFileName;
            }
        }
    }

    if ($profilePicture) {
        $stmt = $db->prepare("UPDATE users SET display_name = ?, profile_picture = ? WHERE id = ?");
        $stmt->bind_param("ssi", $displayName, $profilePicture, $userId);
    } else {
        $stmt = $db->prepare("UPDATE users SET display_name = ? WHERE id = ?");
        $stmt->bind_param("si", $displayName, $userId);
    }
    
    if ($stmt->execute()) {
        // Fetch updated
        $stmt2 = $db->prepare("SELECT username, display_name, profile_picture FROM users WHERE id = ?");
        $stmt2->bind_param("i", $userId);
        $stmt2->execute();
        $user = $stmt2->get_result()->fetch_assoc();
        
        echo json_encode([
            "success" => true,
            "profile" => [
                "username" => $user['username'],
                "display_name" => $user['display_name'] ?: $user['username'],
                "profile_picture" => $user['profile_picture']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update profile"]);
    }
}
?>
