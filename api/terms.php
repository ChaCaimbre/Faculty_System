<?php
require_once 'auth_helper.php';
require_once '../config.php';

$userId = requireUserId();
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch all terms for the user
    $stmt = $db->prepare("SELECT id, name, is_active FROM academic_terms WHERE user_id = ? OR user_id = 1 ORDER BY id DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $name = $data['name'] ?? '';
    
    if (!$name) {
        echo json_encode(['success' => false, 'message' => 'Name required']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO academic_terms (name, user_id) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $userId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $db->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $db->error]);
    }
}
